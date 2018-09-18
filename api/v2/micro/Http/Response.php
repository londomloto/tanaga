<?php
namespace Micro\Http;

class Response extends \Phalcon\Http\Response {
    
    /*public function send() {

        $request = $this->getDI()->get('request');

        if ($request->getHeader('Origin')) {
            $this->setHeader('Access-Control-Allow-Origin', $request->getHeader('Origin'));
            $this->setHeader('Access-Control-Allow-Credentials', 'true');
            $this->setHeader('Access-Control-Max-Age', '86400');
        }

        if ($request->getMethod() == 'OPTIONS') {
            if ($request->getHeader('Access-Control-Request-Method')) {
                $this->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            }

            if ($request->getHeader('Access-Control-Request-Headers')) {
                $this->setHeader('Access-Control-Allow-Headers', $request->getHeader('Access-Control-Request-Headers'));
            }

            $this->sendHeaders();
            
            exit(0);
        }

        return parent::send();
    }*/

}