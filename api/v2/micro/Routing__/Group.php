<?php
namespace Micro\Routing;

class Group {

    protected $_prefix;
    protected $_handler;
    protected $_middleware;
    protected $_routes;

    public function __construct($options = array()) {
        $this->_prefix = isset($options['prefix']) ? $options['prefix'] : '';
        $this->_handler = isset($options['handler']) ? $options['handler'] : FALSE;
        $this->_middleware = isset($options['middleware']) ? $options['middleware'] : FALSE;
        $this->_routes = array();
    }
    
    public function getPrefix() {
        return $this->_prefix;
    }

    public function getHandler() {
        return $this->_handler;
    }

    public function getMiddleware() {
        return $this->_middleware;
    }

    public function setMiddleware($middleware) {
        $this->_middleware = $middleware;
        
        foreach($this->_routes as $route) {
            $route->setMiddleware($middleware);
        }

        return $this;
    }

    public function get($path, $handler) {
        $route = new Route();
        $route->setGroup($this);
        $route->get($path, $handler);

        $this->_routes[] = $route;

        return $this;
    }

    public function post($path, $handler) {
        $route = new Route();
        $route->setGroup($this);
        $route->post($path, $handler);

        $this->_routes[] = $route;

        return $this;
    }

    public function put($path, $handler) {
        $route = new Route();
        $route->setGroup($this);
        $route->put($path, $handler);

        $this->_routes[] = $route;

        return $this;
    }

    public function delete($path, $handler) {
        $route = new Route();
        $route->setGroup($this);
        $route->delete($path, $handler);

        $this->_routes[] = $route;

        return $this;
    }

    public function options($path, $handler) {
        $route = new Route();
        $route->setGroup($this);
        $route->options($path, $handler);

        $this->_routes[] = $route;
        
        return $this;
    }

}