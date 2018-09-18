<?php
namespace Micro\Google;

require_once('../../../vendor/autoload.php');

class GoogleClient extends \Micro\Component {

    private $__config;
    private $__connected;
    private $__conn;
    private $__client;

    public function __construct() {

        // default config
        // $config = array_merge(array(
        //     'version' => 3,
        //     'referals' => 0,
        //     'secure' => FALSE,
        //     'port' => 389,
        //     'base' => NULL
        // ), $this->getApp()->config->google->toArray());

        // $this->__config = new \Phalcon\Config($config);

        
        define('SCOPES', implode(' ', array(
          \Google_Service_Gmail::GMAIL_READONLY)
        ));
        // define('CLIENT_SECRET_PATH', PUBPATH . 'client_id.json');
        $gclient = json_decode($this->getApp()->config->google->client); 
        $this->__client = new \Google_Client();
        $this->__client->setApplicationName('WS SALES');
        $this->__client->setScopes(SCOPES);
        $this->__client->setClientId($gclient->web->client_id);
        $this->__client->setClientSecret($gclient->web->client_secret);
        $this->__client->setAccessType('offline');
        $this->__client->setApprovalPrompt('force');
    }

    function getClient(){
        return $this->__client;
    }


    // public function query($node = NULL) {
    //     return new LdapQuery($this->__conn, $this->__config->base, $node);
    // }

}