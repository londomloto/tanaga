<?php
namespace Micro\Routing;

class Router {

    private static $__groups = array();

    public static function app() {
        return \Micro\App::getDefault();
    }

    public static function map($verb, $path, $handler) {
        $verb = strtolower($verb);

        if ($handler instanceof \Closure) {
            $app = self::app();
            $app->$verb($path, $handler);
            
            if ($verb != 'options') {
                $app->options($path, $handler);    
            }
        } else {
            $part = explode('@', $handler);
            $segments = explode('/', $path);
            $path = '/'.(array_pop($segments));
            $prefix = implode('/', $segments);
            $handler = $part[0];
            $action = $part[1];

            if ( ! isset(self::$__groups[$prefix])) {
                self::$__groups[$prefix] = new Group(array(
                    'prefix' => $prefix,
                    'handler' => $handler
                ));
            }

            $group = self::$__groups[$prefix];
            $group->$verb($path, $action);
        }
    }

    public static function get($path, $handler) {
        return self::map('GET', $path, $handler);
    }

    public static function post($path, $handler) {
        return self::map('POST', $path, $handler);
    }

    public static function put($path, $handler) {
        return self::map('PUT', $path, $handler);
    }

    public static function delete($path, $handler) {
        return self::map('DELETE', $path, $handler);
    }

    public static function options($path, $handler) {
        return self::map('OPTIONS', $path, $handler);
    }

    public static function group($options = array()) {
        return new Group($options);
    }

    public static function resource($path, $resource, $options = array()) {
        $options = array_merge($options, array(
            'prefix' => $path,
            'handler' => $resource
        ));

        $group = new Group($options);

        $group->batch();

        $group->get('', 'find');
        $group->get('/{id:[0-9]+}', 'findById');
        $group->post('', 'create');
        $group->put('/{id:[0-9]+}', 'update');
        $group->delete('/{id:[0-9]+}', 'delete');

        $group->mount();
        // $group->options('', 'preflight');
        // $group->options('/{id:[0-9]+}', 'preflight');

        return $group;
    }
    
}