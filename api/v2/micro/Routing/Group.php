<?php
namespace Micro\Routing;

use Phalcon\Mvc\Micro\Collection;

class Group {

    protected $_collector;
    protected $_batch = FALSE;

    public function __construct($options = array()) {

        $this->_collector = new Collection();
        $this->_initialize($options);
    }

    public function getApp() {
        return \Micro\App::getDefault();
    }

    public function batch() {
        $this->_batch = TRUE;
    }

    public function mount() {
        $this->getApp()->mount($this->_collector);
    }

    public function map($verb, $path, $func) {
        $func = $func.'Action';
        $this->_collector->$verb($path, $func);

        if ($verb != 'options') {
            $this->_collector->options($path, $func);
        }

        if ( ! $this->_batch) {
            $this->getApp()->mount($this->_collector);
        }
    }

    protected function _initialize($options = array()) {

        if (isset($options['prefix'])) {
            $this->_collector->setPrefix($options['prefix']);
        }

        if (isset($options['handler'])) {
            $this->_collector->setHandler($options['handler'], TRUE);
        }

        $this->_collector->_middleware = NULL;

        if (isset($options['middleware'])) {
            $this->_collector->_middleware = $options['middleware'];
        }
    }
    
    public function get($path, $func) {
        $this->map('get', $path, $func);
        return $this;
    }

    public function post($path, $func) {
        $this->map('post', $path, $func);
        return $this;
    }

    public function put($path, $func) {
        $this->map('put', $path, $func);
        return $this;
    }

    public function delete($path, $func) {
        $this->map('delete', $path, $func);
        return $this;
    }

    public function options($path, $func) {
        $this->map('options', $path, $func);
        return $this;
    }

}