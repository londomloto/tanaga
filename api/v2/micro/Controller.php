<?php
namespace Micro;

use Phalcon\Mvc\Dispatcher;

abstract class Controller extends \Phalcon\Mvc\Controller {
    
    protected $_initialized;
    public $view;

    public function onConstruct() {
        if ( ! $this->_initialized) {
            $this->_initialized = TRUE;
            $this->initialize();
        }
    }

    public function getApp() {
        return \Micro\App::getDefault();
    }

    public function getModule() {
        static $module;

        if (is_null($module)) {
            $class = get_called_class();
            $parts = explode('\\', $class);
            $module = $parts[1];
        }
        
        return $module;
    }

    public function initialize() {
        // setup view
        $this->view = new \Phalcon\Mvc\View\Simple();
        $this->view->setViewsDir(APPPATH.'modules/'.$this->getModule().'/Views/');
    }

    public function preflightAction() {
        
        return array(
            'success' => TRUE
        );

    }

}