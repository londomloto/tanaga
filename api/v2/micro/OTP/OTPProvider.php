<?php
namespace Micro\OTP;

class OTPProvider extends \Micro\Component {

    public function __construct() {
        $this->_config = $this->getApp()->config->otp;
    }

    private function __fetchEmail() {
        $post = $this->getApp()->request->getJson();
        return isset($post['email']) ? $post['email'] : FALSE;
    }

    private function __fetchCode() {
        $post = $this->getApp()->request->getJson();
        return isset($post['code']) ? $post['code'] : FALSE;    
    }

    private function __fetchUser() {
        $email = $this->__fetchEmail();

        if ($email) {
            return call_user_func_array('App\Users\Models\User::findFirst', array(
                array(
                    'su_email = :email:',
                    'bind' => array(
                        'email' => $email
                    )
                )
            ));    
        } else {
            $user = $this->getApp()->auth->user(NULL, TRUE);
            return $user ? $user : FALSE;
        }
        
    }

    public function request() {
        $user = $this->__fetchUser();
        $life = $this->_config->offsetExists('lifetime') ? $this->_config->lifetime : 120;
        $code = NULL;

        if ($user) {
            $token = $user->su_otp_token;
            $valid = TRUE;
            
            if (empty($token)) {
                $valid = FALSE;
            } else {
                // verify token
                $decode = $this->getApp()->security->verifyToken($token);

                if ($decode['status'] == 'valid') {
                    $data = $decode['payload'];
                    $code = $data->code;

                    $startTime = strtotime($data->date);
                    $currentTime = time();
                    $deltaTime = $currentTime - $startTime;
                    $life -= $deltaTime;
                } else {
                    $valid = FALSE;
                }
            }

            if ( ! $valid) {

                $code = rand(10000, 99999);
                
                $token = $this->getApp()->security->createToken(array(
                    'code' => $code,
                    'date' => date('Y-m-d H:i:s')
                ), $life);

                // create and send
                $user->su_otp_token = $token;
                $user->save();
                
                $body = <<<MAIL
<h3 style="font-family: Arial;">Dear, user</h3>
<p style="font-family: Arial;">
    You have requested some service from application. We have generated a One-Time Password (OTP) for you. 
    This will verify that you have requested access. This One-Time Password is time sensitive.
</p>
<p style="font-family: Arial;font-size: 20px;">
    Your One-Time Password (OTP) is <span style="color: red">{$code}</span>
</p>

<p style="font-family: Arial; font-size: 12px;">
    These email contains sensitive information, please do not forward.<br>
    If you have any questions, feel free to contact us at <a href="mailto:support@worksaurus.com"><b>support@worksaurus.com</b></a><br>
    <br>
    Thank you.
</p>
MAIL;

                $options = array(
                    'from' => array('support@worksaurus.com' => 'Worksaurus Team'),
                    'to' => $user->su_email,
                    //'bcc' => 'roso@kct.co.id',
                    'subject' => 'Your OTP Code',
                    'body' => $body
                );

                set_time_limit(0);
                $this->getApp()->mailer->send($options);
            }

        }

        return array(
            'code' => $code,
            'life' => $life
        );
    }

    public function validate() {
        $code = $this->__fetchCode();
        $user = $this->__fetchUser();
        $success = FALSE;
        $message = NULL;

        if ($user) {
            $verify = $this->getApp()->security->verifyToken($user->su_otp_token);
            
            if ($verify['status'] == 'valid') {
                $data = $verify['payload'];
                $success = $code == $data->code;
            }
        }

        if ( ! $success) {
            throw new \Phalcon\Exception("Invalid OTP code");
        }
        
        return $success;
    }

}