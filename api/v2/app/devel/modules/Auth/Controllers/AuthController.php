<?php
namespace App\Auth\Controllers;

class AuthController extends \Micro\Controller {

    public function testAction() {

    }

    public function loginAction() {
        $post = $this->request->getJson();
        
        if ( ! isset($post['email']) || ! isset($post['password'])) {
            throw new \Phalcon\Exception('Invalid parameters');
        }

        if ($this->auth->isCaptchaEnabled()) {
            if ( ! isset($post['captcha'])) {
                throw new \Phalcon\Exception('Invalid parameters');
            }

            // validate captcha first
            if ( $post['captcha'] !== FALSE && ! $this->security->verifyCaptcha($post['captcha'])) {
                throw new \Phalcon\Exception('Invalid security code');
            }
        }
        
        $user = $this->auth->login($post['email'], $post['password']);

        if ( ! $user) {
            throw new \Phalcon\Exception($this->auth->error());
        }

        $client = isset($post['client']) ? $post['client'] : 'mobile';
        $permission = 'login_'.$client.'@auth';

        if ($this->role->has($permission) && ! $this->role->can($permission)) {
            $this->auth->logout();
            return array(
                'success' => FALSE,
                'message' => 'You are not allowed to login through this device'
            );
        }

        return array(
            'success' => TRUE,
            'data' => $user
        );
    }

    public function accessAction() {
        $post = $this->request->getJson();
        $validation = $this->security->verifyToken($post['token']);
        
        $result = array(
            'success' => FALSE
        );

        if ($validation['success']) {
            $user = \App\Users\Models\User::findFirstByEmail($validation['payload']->su_email);

            if ($user) {
                $user->su_access_token = $post['token'];
                $user->save();

                $data = $this->auth->save($user);

                $client = isset($post['client']) ? $post['client'] : 'mobile';
                $permission = 'login_'.$client.'@auth';

                if ($this->role->has($permission) && ! $this->role->can($permission)) {
                    $this->auth->logout();
                    $result['message'] = 'You are not allowed to access through this device';
                } else {
                    $result['success'] = TRUE;
                    $result['data'] = $data;
                }
            }
        }

        return $result;
    }

    public function validateAction() {
        return array(
            'success' => TRUE,
            'data'=> $this->auth->validate()
        );
    }

    public function validatePasswordAction() {
        $user = $this->auth->user();
        $pass = $this->request->getQuery('pass');

        $data = array(
            'valid' => FALSE
        );

        if ($user) {
            $find = \App\Users\Models\User::findFirst($user['su_id']);
            if ($find) {
                $data['valid'] = $this->security->verifyHash($pass, $find->su_passwd);
            }
        }

        return array(
            'success' => TRUE,
            'data' => $data
        );
    }

    public function captchaAction() {
        $type = $this->request->getQuery('type');

        if ($type == 'image') {
            $this->security->createCaptcha('image');
            exit();
        } else {
            return array(
                'success' => TRUE,
                'data' => $this->security->createCaptcha('code')
            );
        }
    }

    public function validateResetAction() {
        $post = $this->request->getJson();
        $code = isset($post['token']) ? $post['token'] : NULL;

        $result = array(
            'success' => FALSE
        );

        if ( ! empty($code)) {
            $verify = $this->security->verifyToken($code);
            if ($verify['success']) {
                $user = \App\Users\Models\User::findFirst(array(
                    'su_email = :email: AND su_recover_token = :code:',
                    'bind' => array(
                        'email' => $verify['payload']->su_email,
                        'code' => $code
                    )
                ));

                if ($user) {
                    $result['success'] = TRUE;
                    $data = $user->toArray();
                    $result['data'] = $this->auth->secure($data);
                } else {
                    $result['message'] = 'Invalid recovery code';
                }
            } else {
                $result['message'] = $verify['message'];
            }
        } else {
            $result['message'] = 'Invalid recovery code';
        }

        return $result;
    }

    public function resetAction() {
        $post = $this->request->getJson();
        $user = \App\Users\Models\User::findFirst(array('su_email = :email:', 'bind' => array('email' => $post['email'])));

        if ($user) {
            $user->su_recover_token = NULL;
            $user->su_passwd = $this->security->createHash($post['password']);
            $user->save();

            return array(
                'success' => TRUE
            );
        }

        return array(
            'success' => FALSE
        );
    }

    public function recoverAction() {
        $post = $this->request->getJson();
        $user = \App\Users\Models\User::findFirst(array('su_email = :email:', 'bind' => array('email' => $post['email'])));

        if ($user) {

            $code = $this->security->createToken(array('su_email' => $user->su_email));

            $user->su_recover_token = $code;
            $user->save();

            $data = $user->toArray();

            $body = $this->view->render('password_recovery', array(
                'user' => $data,
                'href' => $this->url->getScheme().'://'.$this->url->getHost().'/'.$this->config->app->name.'/recover?code='.$code
            ));

            // send recover email
            $options = array(
                'from' => array('no-reply@worksaurus.com' => 'Worksaurus Admin'),
                'to' => $data['su_email'],
                'bcc' => 'roso@kct.co.id',
                'subject' => 'WS Team Password Recovery Instruction',
                'body' => $body
            );

            $this->mailer->send($options);
        }

        return array('success' => TRUE);
    }

    public function tesEmailAction(){
        $options = array(
            'from' => array('no-reply@worksaurus.com'=>'Worksaurus'),
            'to'=> 'vidi@kct.co.id',
            'subject' => 'Welcome to Worksaurus - User account invitation',
            'body' => "<b>tes email</b>"
        );

        return $this->mailer->send($options);
    }

}