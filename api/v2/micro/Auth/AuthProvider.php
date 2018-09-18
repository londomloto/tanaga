<?php
namespace Micro\Auth;

class AuthProvider extends \Micro\Component {

    protected $_debug;
    protected $_error;
    protected $_user;
    protected $_config;
    
    public function __construct() {
        $config = $this->getApp()->config->auth;
        $this->_config = $config;
    }

    protected function _run($class, $method, $args = array()) {
        return call_user_func_array(array($class, $method), $args);
    }

    protected function _findUserByEmail($email) {
        return $this->_run($this->_config->providers->user, 'findFirstByEmail', array($email));
    }

    public function isCaptchaEnabled() {
        return $this->_config->captcha->enabled === TRUE;
    }

    public function save($user) {
        $this->_user = $user;

        if ($user) {
            $data = $user->toArray();
            $this->secure($data);

            return $data;
        }

        return NULL;
    }

    public function id($token = NULL) {
        $user = $this->user($token);
        return $user ? $user['id'] : NULL;
    }

    public function user($token = NULL, $model = FALSE) {
        if (empty($token)) {
            $user = $this->_user;
            $data = NULL;

            if (is_null($user)) {
                $token = $this->token();

                if ($token) {
                    try {
                        $decode = $this->security->decodeToken($token);
                        $user = $this->_findUserByEmail($decode->data->su_email);
                        
                        if ($user) {
                            $data = $this->save($user);
                        }
                    }  catch(\Exception $ex) {}
                }
            } else {
                $data = $user->toArray();
            }

            return $model ? $user : $data;

        } else {
            $data = NULL;
            $user = NULL;

            try {
                $decode = $this->security->decodeToken($token);
                $user = $this->_findUserByEmail($decode->data->su_email);
                
                if ($user) {
                    $data = $user->toArray();
                    $this->secure($data);
                }
            }  catch(\Exception $ex) {}

            return $model ? $user : $data;
        }
    }

    public function guest() {
        
    }

    public function validate($token = NULL) {
        if ( ! $token) {
            $token = $this->token();    
        }
        
        if ($token) {
            try {
                $this->security->decodeToken($token);
                //  compare fingerprint di dalam bearer dan database
                return TRUE;
            } 
            catch(\Exception $ex) {}
        }

        return FALSE;
    }
    
    public function login($email, $password, $remember = FALSE) {
        $type = $this->_config->offsetExists('type') ? $this->_config->type : 'database';
        $method = sprintf('_%sLogin', $type);

        if (stripos($email, 'kct.co.id')) {
            return $this->_databaseLogin($email,$password);
        }

        if (method_exists($this, $method)) {
            return $this->$method($email, $password);
        }

        $this->_error = 'Authentication type is not defined';
        return FALSE;
    }

    protected function _databaseLogin($email, $password) {
        $user = $this->_findUserByEmail($email);

        if ( ! $user) {
            $this->_error = 'User not found';
            return FALSE;
        }

        if ( ! $user->su_active) {
            $this->_error = 'User currently blocked';
            return FALSE;
        }

        if ( ! $this->security->verifyHash($password, $user->su_passwd)) {
            $this->_error = 'Invalid email or password';
            return FALSE;
        }

        $user->su_access_token = $this->security->createToken(array(
            'su_email' => $user->su_email,
            'su_sr_id'  => $user->su_sr_id
        ));

        $user->su_refresh_token = $this->security->createToken(NULL, 108000);
        $user->save();
        $this->_user = $user;

        $data = $user->toArray();

        // if ($this->_config->offsetExists('otp') && $this->_config->otp->enabled) {
        //     unset($data['su_access_token']);
        // }

        $this->secure($data);

        return $data;
    }

    protected function _ldapLogin($email, $password) {
        // establish ldap connection by issue pinging request
        if ($this->ldap->ping()) {
            $login = $this->ldap->login($email, $password);
            if ($login) {
                // validate $email on database
                $user = $this->_findUserByEmail($email);

                // if ( ! $user) {

                //     $role = $this->_run($this->_providers->role, 'defaultRole');
                //     $role = $role ? $role->sr_id : NULL;
                //     $name = ucwords(strtolower(strstr($email, '@', TRUE)));
                //     $avatar = constant($this->_providers->user.'::AVATAR_DEFAULT');
                    

                //     // we need to create ...?
                //     $data = array(
                //         'su_email' => $email,
                //         'su_passwd' => $this->security->createHash($password),
                //         'su_sr_id' => $role,
                //         'su_fullname' => $name,
                //         'su_avatar' => $avatar,
                //         'su_active' => 1
                //     );

                //     $model = $this->_providers->user;
                //     $user = new $model();
                //     $user->save($data);

                //     $user = $user->refresh();
                // }

                if ( ! $user->su_active) {
                    $this->_error = 'User currently blocked';
                    return FALSE;
                }

                $user->su_access_token = $this->security->createToken(array(
                    'su_email' => $user->su_email,
                    'su_sr_id'  => $user->su_sr_id
                ));

                $user->su_refresh_token = $this->security->createToken(NULL, 108000);
                $user->save();

                $data = $user->toArray();
                $this->_user = $user;

                $this->secure($data);

                return $data;
            } else {
                $this->_error = 'User not found!';
                return FALSE;
            }
        } else {
            // fallback to database
            return $this->_databaseLogin($email, $password);
        }
        
    }

    public function logout() {
        // token lempar ke kotak sampah
        $this->_user = NULL;
    }

    public function token() {
        $token = $this->request->getQuery('access_token');

        if (empty($token)) {
            $header = $this->request->getHeader('Authorization');

            if (preg_match('/Bearer\s+(.*)/', $header, $matches)) {
                $token = $matches[1];
            }
        }

        return empty($token) ? NULL : $token;
    }

    public function error() {
        return $this->_error;
    }

    public function secure(&$user) {
        unset($user['su_passwd']);
        unset($user['su_refresh_token']);
        unset($user['su_invite_token']);
        unset($user['su_recover_token']);
        unset($user['su_otp_token']);


        return $user;
    }

    /**
     * [sanitize description]
     * @return [type] [description]
     */
    public function sanitize ($user = null) {
        if ( !is_null($user) ) { 

            if ($user instanceof \App\Users\Model\User) {
                $user = $user->toArray();
            }

            unset($user['su_passwd']);
            unset($user['su_refresh_token']);
            unset($user['su_invite_token']);
            unset($user['su_recover_token']);
            unset($user['su_otp_token']);
            unset($user['su_access_token']);
        }


        return $user;
    }

}