<?php
namespace Micro;

abstract class Component {

    protected $_providers = array();

    public function getApp() {
        return \Micro\App::getDefault();
    }

    public function __get($key) {
        if (isset($this->_providers[$key])) {
            return $this->_providers[$key];
        }

        $di = $this->getApp()->getDI();

        if ($di->has($key)) {
            $provider = $di->get($key, TRUE);
            $this->_providers[$key] = $provider;
            return $provider;
        } else {
            $class = get_called_class();
            throw new \Phalcon\Exception("Undefined property: {$class}::\${$key}");
        }
    }
}