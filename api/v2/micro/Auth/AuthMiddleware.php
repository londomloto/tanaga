<?php
namespace Micro\Auth;

class AuthMiddleware extends \Micro\Component {
    
    public function authenticate() {
        $token = $this->request->getQuery('access_token');

        if (is_null($token)) {
            $header = $this->request->getHeader('Authorization');

            if ( ! $header) {
                throw new \Phalcon\Exception("It's look like you don't have privilege", 401);
            }

            if (preg_match('/Bearer\s+(.*)/', $header, $matches)) {
                $token = $matches[1];
            }
        }

        if (is_null($token)) {
            throw new \Phalcon\Exception("It's look like you don't have privilege", 401);
        }

        $verify = $this->security->verifyToken($token);

        if ( ! $verify['success']) {
            if ($verify['status'] == 'expired') {
                $message = "It's look like your access token has been expired";
            } else if($verify['status'] == 'pending') {
                $message = "It's look like Your access token not yet activated";
            } else {
                $message = "It's look like your access token not valid";
            }
            throw new \Phalcon\Exception($message, 401);
        }

        return TRUE;
    }

}