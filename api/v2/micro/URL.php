<?php
namespace Micro;

class URL extends \Phalcon\Mvc\Url {

    protected $_baseurl;

    public function __construct() {
        // construct base uri
        $uri = substr(
            $_SERVER['SCRIPT_NAME'],
            0,
            strpos(
                $_SERVER['SCRIPT_NAME'],
                basename($_SERVER['SCRIPT_FILENAME'])
            )
        );

        $this->setBaseUri($uri);
    }

    public function getScheme() {
        return ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http';
    }

    public function getHost() {
        return $_SERVER['HTTP_HOST'];
    }

    public function getBaseUri() {
        $uri = parent::getBaseUri();
        $uri = dirname($uri).'/';
        return $uri;
    }

    public function getBaseUrl() {
        if (is_null($this->_baseurl)) {
            $base = $this->getScheme().'://'.$_SERVER['HTTP_HOST'].$this->getBaseUri();
            $this->_baseurl = $base;
        }
        return $this->_baseurl;
    }

    public function getSiteUrl($path) {
        return $this->getBaseUrl().$path;
    }

    public function getClientUrl() {
        $base = $this->getBaseUrl();
        $part = explode('/api/v2/app/', $base);

        return $part[0].'/'.$part[1];
    }
}