<?php
namespace Micro\Bpmn;

class BpmnProvider extends \Micro\Component {

    protected $_providers;
    protected static $_workers = array();

    public function __construct() {
        $app = $this->getApp();

        if ($app->config->offsetExists('bpmn')) {
            $this->_providers = $app->config->bpmn->providers;
        } else {
            throw new \Phalcon\Exception("BPMN configuration not found");
        }
    }
    
    public function workers() {
        $query = call_user_func_array($this->_providers->diagram.'::get', array());
        $query->orderBy('name ASC');
        $workers = array();

        foreach($query->execute() as $diagram) {
            $workers[] = $this->worker($diagram->slug);
        }

        return $workers;
    }

    public function worker($name) {
        $worker = isset(self::$_workers[$name]) ? self::$_workers[$name] : NULL;
        if (is_null($worker)) {
            $worker = new BpmnWorker($name, $this->_providers->diagram);
            self::$_workers[$name] = $worker;
        }
        return $worker;
    }

}