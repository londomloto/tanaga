<?php
namespace Micro\Routing;

class Route extends \Micro\Component {

    protected $_prefix;
    protected $_controller;
    protected $_middleware;
    protected $_router;

    public function __construct($options = array()) {
        $this->_prefix = '';
        $this->_controller = FALSE;
        $this->_middleware = FALSE;
    }

    public function getDI() {
        return \Phalcon\DI::getDefault();
    }
    
    public function setGroup($group) {
        $this->_prefix = $group->getPrefix();
        $this->_controller = $group->getHandler();
        $this->_middleware = $group->getMiddleware();
    }

    public function setController($controller) {
        $this->_controller = $controller;
        return $this;
    }

    public function setMiddleware($middleware) {
        $this->_middleware = $middleware;
        return $this;
    }

    public function getMiddleware() {
        return $this->_middleware;
    }

    public function get($path, $handler) {
        $route = $this->getApp()->get($this->_compilePath($path), $this->_compileHandler($handler));
        $route->_provider = $this;
        
        return $this;
    }

    public function post($path, $handler) {
        $route = $this->getApp()->post($this->_compilePath($path), $this->_compileHandler($handler));
        $route->_provider = $this;

        return $this;
    }

    public function put($path, $handler) {
        $route = $this->getApp()->put($this->_compilePath($path), $this->_compileHandler($handler));
        $route->_provider = $this;

        return $this;
    }

    public function delete($path, $handler) {
        $route = $this->getApp()->delete($this->_compilePath($path), $this->_compileHandler($handler));
        $route->_provider = $this;

        return $this;
    }

    public function options($path, $handler) {
        $route = $this->getApp()->options($this->_compilePath($path), $this->_compileHandler($handler));
        $route->_provider = $this;

        return $this;
    }

    private function _compilePath($path) {
        $path = $this->_prefix.$path;
        return $path;
    }

    private function _compileHandler($handler) {
        if ($this->_controller && is_string($handler)) {
            $ctl = $this->_controller;
            $injector = $this->getDI();
            
            if ( ! $injector->offsetExists($ctl)) {
                $injector->setShared($ctl, function() use ($ctl){
                    return new $ctl();
                });
            }

            $controller = $this->getDI()->getShared($ctl);
            $action = $handler.'Action';

            return array($controller, $action);
        }

        return $handler;
    }
}