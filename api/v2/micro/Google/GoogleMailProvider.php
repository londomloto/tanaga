<?php
namespace Micro\Google;

class GoogleMailProvider extends \Micro\Component {
    
    private $__client;
    private $__config;
    private $__errors;

    public function __construct() {
        $this->__config = $this->getApp()->config->google->mail;
        $this->__errors = NULL;

        $this->__client = new \Google_Client();
        
        $this->__client->setApplicationName($this->__config->name);

        $this->__client->setAuthConfig($this->__config->clientSecret);
        $this->__client->setAccessType('offline');
        $this->__client->setIncludeGrantedScopes(true);
        $this->__client->setApprovalPrompt('force');

        $scopes = implode(' ', $this->__config->scopes->toArray());

        $this->__client->addScope($scopes);
        $this->__client->setRedirectUri($this->getCallbackUrl());
    }

    public function getClient() {
        return $this->__client;
    }

    public function getCallbackUrl() {
        $base = $this->getApp()->url->getBaseUrl();
        $part = explode('/api/v2/app/', $base);
        return $part[0].'/auth/';
    }

    public function getAuthUrl($data = NULL) {
        if ( ! is_null($data)) {
            $this->__client->setState(json_encode($data));
        }
        return filter_var($this->__client->createAuthUrl(), FILTER_SANITIZE_URL);
    }

    public function getToken($code = NULL) {
        $token = array();

        if (is_null($code)) {
            $token = $this->__client->getAccessToken();
        } else {
            $token = $this->__client->fetchAccessTokenWithAuthCode($code);    
        }

        return isset($token['access_token']) ? $token : NULL;
    }

    public function getProfile($token) {
        try {
            $this->__client->setAccessToken($token);
            $service = new \Google_Service_Gmail($this->__client);
            $account = $service->users->getProfile('me');    
        } catch(\Google_Service_Exception $ex) {
            $account = NULL;
        }

        return $account;
    }

    public function syncLabels($account) {
        
        $this->__client->setAccessToken($account->getAccessToken());
        
        $success = TRUE;

        try {
            
            $service = new \Google_Service_Gmail($this->__client);
            $user = $account->getAccountId();

            $labels = $service->users_labels->listUsersLabels($user)->getLabels();
            $labels = array_map(function($e){ return $e->id; }, $labels);

            $this->__client->setUseBatch(TRUE);
            $batch  = $service->createBatch();

            foreach($labels as $id) {
                $request = $service->users_labels->get($user, $id);
                $batch->add($request);
            }

            $result = $batch->execute();
            $filter = array_flip($account->getFilterLabels());

            $labels = array();

            foreach($result as $label) {
                $item = $label->toSimpleObject();

                // pretty string
                $text = trim(preg_replace('/([^a-z]+)/i', ' ', $label->name));
                $text = ucwords(strtolower($text));

                $item->text = $text;
                $item->checked = isset($filter[$item->id]) ? 1 : 0;
                
                // set order
                $item->order = 1;
                $item->group = 'LABEL_SHOW';

                if (preg_match('/CATEGORY_/i', $label->name)) {
                    $item->order = 3;
                    $item->group = 'LABEL_HIDE';
                } else if (preg_match('/\[Imap\]/i', $label->name)) {
                    $item->order = 4;
                    $item->group = 'LABEL_HIDE';
                } else if ($label->getLabelListVisibility() == 'labelHide') {
                    $item->order = 2;
                    $item->group = 'LABEL_HIDE';
                }

                $labels[] = $item;
            }

            usort($labels, function($a, $b){
                return ($a->order - $b->order);
            });

            $this->__client->setUseBatch(FALSE);

            $success = $account->syncLabels($labels);
            
        } catch(\Exception $ex){
            $this->__client->setUseBatch(FALSE);
            $success = FALSE;
        }

        return $success;
    }

    public function read($account, $message) {
        $this->__client->setAccessToken($account->getAccessToken());
        $service = new \Google_Service_Gmail($this->__client);

        $success = FALSE;
        $updater = new \Google_Service_Gmail_ModifyMessageRequest();
        $updater->setRemoveLabelIds(array('UNREAD'));

        try {
            $result = $service->users_messages->modify(
                $account->getAccountId(), 
                $message->getMessageId(),
                $updater
            );

            $message->setLabels($result->labelIds);

            $success = TRUE;
        } catch(\Exception $ex){
            //var_dump($ex->getMessage());
        }

        return $success;
    }

    public function syncThreads($account, $params = array(), $partial = FALSE) {
        set_time_limit(0);

        $userId = $account->getAccountId();

        $this->__client->setAccessToken($account->getAccessToken());
        $service = new \Google_Service_Gmail($this->__client);

        $params = array_merge($params, array(
            // 'maxResults' => 1
        ));

        if ( ! isset($params['labelIds'])) {
            if (count($labels = $account->getFilterLabels()) > 0) {
                $params['labelIds'] = $labels;
            }    
        }

        // fetch threads
        $page = NULL;
        $success = TRUE;
        
        $batchClient = clone $this->__client;
        $batchClient->setUseBatch(TRUE);

        $conn = $account->getWriteConnection();
        $conn->begin();

        do {
            try {
                if ($page) {
                    $params['pageToken'] = $page;
                }

                $response = $service->users_threads->listUsersThreads($userId, $params);

                if (($threads = $response->getThreads())) {

                    $page = $response->getNextPageToken();
                    $sync = array();

                    $batchService = new \Google_Service_Gmail($batchClient);
                    $batchRequest = $batchService->createBatch();

                    foreach($threads as $thread) {
                        $sync[$thread->id] = $thread->toSimpleObject();
                        $request = $batchService->users_threads->get($userId, $thread->id, array('format' => 'full'));
                        $batchRequest->add($request);
                    }

                    $result = $batchRequest->execute();

                    unset($batchService, $batchRequest);

                    foreach($result as $thread) {

                        $sync[$thread->id]->subject = NULL;
                        $sync[$thread->id]->messages = array();
                        $sync[$thread->id]->sender = NULL;
                        $sync[$thread->id]->date = NULL;
                        $sync[$thread->id]->labels = NULL;

                        $messages = $thread->getMessages();
                        $total = count($messages);
                        $sender = array();
                        $subject = NULL;
                        $labels = array();

                        foreach($messages as $index => $message) {

                            $item = new \stdClass();

                            $item->id = $message->id;
                            $item->historyId = $message->historyId;
                            $item->threadId = $message->threadId;
                            $item->labelIds = $message->labelIds;
                            $item->snippet = $message->snippet;
                            $item->date = NULL;
                            $item->subject = NULL;
                            $item->from = array();
                            $item->to = array();
                            $item->headers = array();

                            $labels = array_values(array_unique(array_merge($labels, $message->labelIds)));


                            foreach($message->payload->headers as $header) {
                                $item->headers[] = $header->toSimpleObject();

                                $name = strtolower($header->name);

                                if ($name == 'date') {
                                    $date = self::__formatDate($header->value, 'Y-m-d H:i:s');
                                    $item->date = $date;
                                    $sync[$thread->id]->date = $item->date;
                                }

                                if ($name == 'subject') {
                                    $item->subject = $header->value;
                                    $subject = $header->value;
                                }

                                if ($name == 'from' || $name == 'to') {
                                    $value = str_replace('"', '', $header->value);
                                    $item->{$name}[] = $value;

                                    if ($name == 'from') {
                                        if ( ! in_array($value, $sender)) {
                                            $sender[] = $value;
                                        }
                                    }
                                }
                            }

                            $item->from = json_encode($item->from);
                            $item->to = json_encode($item->to);

                            $data = self::__fetchData($message);

                            $item->body = $data['data'];
                            $item->mime = $data['mime'];

                            $sync[$thread->id]->messages[] = $item;
                        }

                        $sync[$thread->id]->sender = json_encode($sender);
                        $sync[$thread->id]->items = $total;
                        $sync[$thread->id]->subject = $subject;
                        $sync[$thread->id]->labels = json_encode($labels);

                        $success = $success && $account->syncThread($sync[$thread->id]);

                    }

                    // batch request
                }
            } catch(\Exception $ex) {
                $this->__errors = $ex->getMessage();
                $page = NULL;
                $success = FALSE;
            }
        } while($page);

        unset($batchClient);

        if ($success) {
            $conn->commit();
        } else {
            $conn->rollback();
        }

        return $success;
    }

    public function validateToken($account) {
        $token = $account->getAccessToken();
        $valid = TRUE;
            
        $this->__client->setAccessToken($token);
        
        if ($this->__client->isAccessTokenExpired()) {
            // try to refresh
            $refreshToken = is_array($token) && isset($token['refresh_token']) 
                ? $token['refresh_token'] 
                : NULL;    
            
            try {

                $this->__client->fetchAccessTokenWithRefreshToken($this->__client->getRefreshToken());

                // new token
                $token = $this->__client->getAccessToken();

                if (is_array($token)) {
                    if ( ! isset($token['refresh_token'])) {
                        $token['refresh_token'] = $refreshToken;
                    }
                    $valid = TRUE;
                } else {
                    $valid = FALSE;
                }

                // save back to account
                $account->setAccessToken($token);

            } catch(\Exception $ex) {
                $valid = FALSE;
            }
            
        }

        return $valid;
    }

    private static function __formatDate($date, $format = 'Y-m-d H:i:s') {
        static $zone;

        if (is_null($zone)) {
            $zone = new \DateTimeZone(\Micro\App::getDefault()->config->app->timezone);
        }
        
        $date = new \DateTime($date);
        return $date->setTimezone($zone)->format($format);
    }

    private static function __fetchAttachments($message) {

    }

    private static function __fetchData($message) {
        $payload = $message->getPayload();
        $parts = $payload->getParts();
        $body = $payload->getBody();

        $data = FALSE;
        $mime = NULL;

        if ($data === FALSE) {
            foreach($parts as $part) {
                
                if ($data === FALSE) {
                    $ps1 = $part->parts;
                    foreach($ps1 as $p1) {
                        $ps2 = $p1->parts;
                        if (count($ps2) > 0) {
                            foreach($ps2 as $p2) {
                                if ($p2->mimeType == 'text/html' && $p2->body->data) {
                                    $data = self::__decodeData($p2->body->data);
                                    $mime = 'text/html';
                                    break;
                                }
                            }
                        } else if ($p1->mimeType == 'text/html' && $p1->body->data) {
                            $data = self::__decodeData($p1->body->data);
                            $mime = 'text/html';
                            break;
                        }
                    }
                }

                if ($data !== FALSE) {
                    break;
                }
            }    
        }

        // still not found?
        if ($data === FALSE) {
            foreach($parts as $part) {
                if ($part->mimeType == 'text/html' && $part->body->data) {
                    $data = self::__decodeData($part->body->data);
                    $mime = 'text/html';
                    break;
                }
            }
        }

        // still not found?
        if ($data === FALSE) {
            $data = self::__decodeData($body->data);    
            $mime = 'text/plain';
        }

        // still not found? last try!
        if ($data === FALSE) {
            foreach($parts as $part) {
                if ($part->body->data) {
                    $data = self::__decodeData($part->body->data);
                    $mime = 'text/plain';
                    break;
                }
            }
        }

        // still not found?
        if ($data === FALSE) {
            $data = '(No message)';
            $mime = 'text/plain';
        }

        return array(
            'data' => $data,
            'mime' => $mime
        );
    }

    private static function __decodeData($encoded) {
        $text = $encoded;
        $textSanitized = strtr($text, '-_', '+/');

        $decoded = base64_decode($textSanitized);

        if ( ! $decoded) {
            $decoded = FALSE;
        }

        return $decoded;
    }

    public function getErrors() {
        return $this->__errors;
    }
}