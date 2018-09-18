<?php
namespace Micro\Routing;

class Router {

    private static $_resources = array();

    public static function map($verb, $path, $handler) {
        $verb = strtolower($verb);

        if ($handler instanceof \Closure) {
            $route = new Route();
            $route->$verb($path, $handler);

            return $route;
        } else {
            return self::mapResource($verb, $path, $handler);
        }
    }

    public static function mapResource($verb, $path, $handler) {
        $parts = explode('@', $handler);
        $paths = explode('/', $path);

        $path = '/' . (array_pop($paths));
        $prefix = implode('/', $paths);
        $resource = $parts[0];
        $action = $parts[1];

        $route = isset(self::$_resources[$resource])
            ? self::$_resources[$resource] 
            : new Group(array('prefix' => $prefix, 'handler' => $resource));

        $verb = strtolower($verb);
        $route->$verb($path, $action);

        return $route;
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

    public static function resource($path, $resource) {
        $group = new Group(array(
            'prefix' => $path,
            'handler' => $resource
        ));

        $group->get('', 'find');
        $group->get('/{id:[0-9]+}', 'findById');
        $group->post('', 'create');
        $group->put('/{id:[0-9]+}', 'update');
        $group->delete('/{id:[0-9]+}', 'delete');
        $group->options('', 'preflight');
        $group->options('/{id:[0-9]+}', 'preflight');

        return $group;
    }

    public static function controller($path, $controller) {
        return new Controller(array(
            'prefix' => $path,
            'handler' => $controller
        ));
    }
    
}