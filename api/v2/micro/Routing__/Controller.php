<?php
namespace Micro\Routing;

class Controller extends Route {

    public function __construct($path, $controller) {
        $app = $this->getApp();
        
        $this->controller = new \Phalcon\Mvc\Micro\Collection();
        $this->controller->setPrefix($path);
        $this->controller->setHandler($controller, TRUE);
        
        $actions = (new \ReflectionClass($controller))->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach($actions as $action) {
            if (strpos($action->name, 'Action') !== FALSE) {
                $route = '/' . strtolower(str_replace('Action', '', $action->name));
                $this->controller->get($route, $action->name);
                $this->controller->post($route, $action->name);
            }
        }

        $app->mount($this->controller);
    }

}