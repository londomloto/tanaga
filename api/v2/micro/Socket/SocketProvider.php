<?php
namespace Micro\Socket;

class SocketProvider {

    protected $_url;
    protected $_room;
    protected $_config;

    public function __construct() {
        $this->_room = '/';

        $app = \Micro\App::getDefault();

        $this->_config = $app->config->socket;

        if ( ! $this->_config->offsetExists('enabled')) {
            $this->_config->offsetSet('enabled', TRUE);
        }
        
        if ( ! $this->_config->offsetExists('secure')) {
            $scheme = $app->url->getScheme();
            $this->_config->offsetSet('secure', $scheme == 'https' ? TRUE : FALSE);
        } else {
            $scheme = $this->_config->secure ? 'https' : 'http';
        }

        if ( ! $this->_config->offsetExists('host')) {
            if (preg_match('/worksaurus\.com/', $_SERVER['SERVER_NAME'])) {
                $host = $_SERVER['SERVER_NAME'];
            } else {
                $host = $_SERVER['SERVER_ADDR'];
            }
            $this->_config->offsetSet('host', $host);
        }

        if ( ! $this->_config->offsetExists('port')) {
            $this->_config->offsetSet('port', $this->_config->secure ? 8443 : 8080);
        }
        
        // $scheme = $this->_config->secure ? 'https' : 'http';

        // if ($scheme == 'https') {
        //     $this->_config->offsetSet('secure', TRUE);
        // }

        $url = $scheme.'://'.$this->_config->host.':'.$this->_config->port;
        $this->_url = $url;

        if ( ! $this->_config->offsetExists('room')) {
            // define default room
            $room = $app->url->getClientUrl();
            $room = '/'.preg_replace('#(https?://|/$)#', '', $room);
            $this->_config->offsetSet('room', $room);
        }else{
            $room = $this->_config->offsetGet('room');
        }

        
        $this->_room = $room;
    }

    public function __get($prop) {
        if ($this->_config->offsetExists($prop)) {
            return $this->_config->offsetGet($prop);
        } else {
            throw new \Phalcon\Exception('Undefined property ' . get_class() . '::'. $prop);
        }
    }

    public function to($room) {
        $this->_room = $room;
        return $this;
    }

    public function createSocket() {
        $server = $this->_url.'?room='.$this->_room;
        $options = array();

        // var_dump($server);

        if ($this->_config->secure) {
            $options = array(
                'context' => array(
                    'ssl' => array(
                        'verify_peer' => FALSE,
                        'verify_peer_name' => FALSE
                    )
                )
            );
        }

        $socket = new \ElephantIO\Client(
            new \ElephantIO\Engine\SocketIO\Version2X($server, $options) 
        );

        return $socket;
    }

    public function send($event, $data) {
        if ( ! $this->_config->enabled) {
            return;
        }

        $socket = $this->createSocket();

        $app = \Micro\App::getDefault();
        $data['__sender'] = $app->request->getHeader('X-Socket-Session');

        $socket->initialize();

        $socket->emit($event, $data);
        $socket->close();
        
        unset($socket);
    }

    public function emit($event, $data = array()) {
        $data['__event'] = $event;
        $this->send('emit', $data);
    }

    public function broadcast($event, $data = array()) {
        $data['__event'] = $event;
        $this->send('broadcast', $data);
    }
}