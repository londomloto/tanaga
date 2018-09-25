<?php
namespace App\Users\Controllers;

use App\Config\Models\Config,
    App\Users\Models\User,
    App\Roles\Models\Role;

class UsersController extends \Micro\Controller {

    public function testAction() {
        var_dump($this->security->encrypt(array('su_email' => 'roso.sasongko@gmail.com'), TRUE));
    }

    public function findAction() {
        return User::get()->filterable()->sortable()->paginate();
    }

    public function findByIdAction($id) {
        return User::get($id);
    }
    
    public function createAction() {
        $post = $this->request->getJson();
        $user = new User();

        if (isset($post['su_passwd']) && ! empty($post['su_passwd'])) {
            $post['su_passwd'] = $this->security->createHash($post['su_passwd']);
        }

        // define fullname
        if ( ! isset($post['su_fullname']) || empty($post['su_fullname'])) {
            $name = substr($post['su_email'], 0, strpos($post['su_email'], '@'));
            $name = ucwords(str_replace('.', ' ', $name));    
            $post['su_fullname'] = $name;
        }

        if ($user->save($post)) {
            if (isset($post['su_kanban'])) {
                $user->saveKanban($post['su_kanban']);
            }

            return User::get($user->su_id);
        }else{
            $messages = $user->getMessages();
            $msg = [];
            foreach ($messages as $message) {
                array_push($msg, $message->getMessage());
            }
            return ["success"=>FALSE, "message"=> implode(',', $msg)];
        }

        return User::none();
    }

    public function updateAction($id) {
        $query = User::get($id);

        if ($query->data) {
            $post = $this->request->getJson();

            if (isset($post['su_passwd']) && ! empty($post['su_passwd'])) {
                $post['su_passwd'] = $this->security->createHash($post['su_passwd']);
            }

            if ($query->data->save($post)) {
                if (isset($post['su_kanban'])) {
                    $query->data->saveKanban($post['su_kanban']);
                }
            }
        }

        return $query;
    }

    public function deleteAction($id) {
        $query = User::get($id);

        if ($query->data) {
            return array(
                'success' => $query->data->delete()
            );
        }
        
        return array('success' => FALSE);
    }

    public function uploadAction($id) {
        $query = User::get($id);

        if ($query->data) {
            if ($this->request->hasFiles()) {
                foreach($this->request->getFiles() as $file) {
                    $type = $file->getExtension();
                    
                    $name = str_replace(array('@', '.'), '_', $query->data->su_email).'.'.$type;
                    $path = APPPATH.'public/resources/avatars/'.$name;

                    if (@$file->moveTo($path)) {
                        $query->data->save(array(
                            'su_avatar' => $name
                        ));
                    }
                }
            }
        }

        return $query;
    }

    public function inviteAction() {
        $post = $this->request->getJson();
        $auth = $this->auth->user();

        $userLeft = Config::userLimit();

        if($userLeft <= 0) return ["success"=>FALSE, "data"=> NULL, "message"=> "User limit exceeded."];

        $email = isset($post['email']) ? $post['email'] : NULL;
        $role = isset($post['role']) ? $post['role'] : NULL;
        
        $result = array(
            'success' => FALSE
        );

        if ( ! empty($email)) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $found = User::findFirst(array(
                    'su_email = :email:',
                    'bind' => array('email' => $email)
                ));

                if ( ! $found) {
                    $user = new User();

                    if (empty($role)) {
                        $role = Role::defaultRole();
                        if ($role) {
                            $role = $role->sr_id;
                        }
                    }

                    $user->su_email = $email;

                    // define fullname
                    $name = substr($email, 0, strpos($email, '@'));
                    $name = ucwords(str_replace('.', ' ', $name));
                    $user->su_fullname = $name;
                    $user->su_active = 0;
                    $user->su_created_date = date('Y-m-d H:i:s');
                    $user->su_created_by = 'system';
                    $user->su_invite_token = $this->__createInvitationToken($email);

                    $user->su_sr_id = $role;

                    if($user->save()){
                        
                        $user = $user->refresh();

                        $this->__sendInvitationEmail($user->toArray());

                        if (isset($post['project']) && ! empty($post['project'])) {
                            $pu = new \App\Projects\Models\ProjectUser();
                            $pu->spu_su_id = $user->su_id;
                            $pu->spu_sp_id = $post['project'];
                            $pu->save();
                        }

                        $result['success'] = TRUE;
                        $result['data'] = User::get($user->su_id)->data;
                    }
                    $result['message'] = 'Failed to invite';
                } else {
                    $result['message'] = 'Email address already registered';
                }
            } else {
                $result['message'] = 'Invalid email address';
            }
        } else {
            $result['message'] = 'Invalid email address';
        }

        return $result;
    }

    public function reinviteAction() {
        $post = $this->request->getJson();

        $user = User::findFirst(array(
            'su_email = :email:', 
            'bind' => array('email' => $post['email'])
        ));

        if ($user && $user->su_active == 0) {
            if (empty($user->su_invite_token)) {
                $user->su_invite_token = $this->__createInvitationToken($post['email']);
                $user->save();
            }
            
            $this->__sendInvitationEmail($user->toArray());
        }

        return array(
            'success' => TRUE
        );
    }

    public function validateActivationAction() {
        $result = array(
            'success' => FALSE
        );

        $post = $this->request->getJson();
        $code = $post['token'];

        if ( ! empty($code)) {
            $data = $this->security->decrypt($code, TRUE);

            if (isset($data->su_email)) {
                $user = User::findFirst(array(
                    'su_email = :email: AND su_invite_token = :code:',
                    'bind' => array(
                        'email' => $data->su_email,
                        'code' => $code
                    )
                ));

                if ($user) {
                    if ($user->su_active == 0) {
                        $result['success'] = TRUE;
                        $result['data'] = $user->toArray();
                    } else {
                        $result['message'] = 'Already activated';
                    }
                } else {
                    $result['message'] = 'Invalid activation code';
                }
            } else {
                $result['message'] = 'Invalid activation code';    
            }
        } else {
            $result['message'] = 'Invalid activation code';
        }

        return $result;
    }

    public function activateAction() {
        $post = $this->request->getJson();
        $user = User::findFirst($post['su_id']);

        $post['su_active'] = 1;
        $post['su_invite_token'] = NULL;

        if (isset($post['password'])) {
            $post['su_passwd'] = $this->security->createHash($post['password']);
        }

        if ($user) {
            $user->save($post);
        }

        $redir = $this->url->getScheme().'://'.$this->url->getHost().'/'.$this->config->app->name;

        return array(
            'success' => TRUE,
            'data' => array(
                'redir' => $redir
            )
        );
    }

    public function enableAction($id) {
        $user = User::findFirst($id);
        $done = FALSE;
        $data = NULL;
        
        $post = $this->request->getJson();
        $post['su_active'] = 1;
        $post['su_invite_token'] = NULL;

        if ($user) {
            $done = $user->save($post);
            $user = $user->refresh();
            $data = $this->auth->sanitize($user->toArray());

            if ($done) {
                $this->mailer->send(array(
                    'from' => array(Config::value('app_support_email') => Config::value('app_support')),
                    'to' => $user->su_email,
                    'subject' => 'Aktivasi user',
                    'body' => $this->view->render('activation', array(
                        'name' => $user->su_fullname,
                        'link' => $post['link']
                    ))
                ));
            }
        }

        return array(
            'success' => $done,
            'data' => $data
        );
    }

    private function __createInvitationToken($email) {
        return $this->security->encrypt(array(
            'su_email' => $email
        ), TRUE);
    }

    private function __sendInvitationEmail($data) {
        $href = sprintf(
            '%s://%s/%s/invitation?code=%s',
            $this->url->getScheme(),
            $this->url->getHost(),
            $this->config->app->name,
            urlencode($data['su_invite_token'])
        ); 
        
        $body = $this->view->render('invitation', array(
            'href' => $href
        ));

        $options = array(
            'from' => array('tanagadevel@gmail.com' => 'Administrator'),
            'to' => $data['su_email'],
            'bcc' => 'roso.sasongko@gmail.com',
            'subject' => 'Undangan bergabung dengan aplikasi',
            'body' => $body
        );

        return $this->mailer->send($options);
    }
}