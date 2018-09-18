<?php
namespace App\Bpmn\Controllers;

class BpmnController extends \Micro\Controller {
    public function workersAction() {
        $data = array();

        foreach($this->bpmn->workers() as $worker) {
            $data[] = array(
                'name' => $worker->name(),
                'text' => $worker->text(),
                'desc' => $worker->description()
            );
        }
        
        return array(
            'success' => TRUE,
            'data' => $data
        );
    }

    public function activitiesAction($worker) {
        return $this->bpmn->worker($worker)->activities();
    }

    public function statusesAction($worker) {
        return $this->bpmn->worker($worker)->statuses();
    }

    public function startAction($worker) {
        $post = $this->request->getJson();

        if  ( ! isset($post['payload'])) {
            throw new \Phalcon\Exception("Invalid request parameters");
        }
        
        return $this->bpmn->worker($worker)->start($post['payload']);
    }

    public function stopAction($worker) {
        return $this->bpmn->worker($worker)->stop();
    }

    public function prevAction($worker) {

    }

    public function nextAction($worker) {
        $post = $this->request->getJson();

        if  ( ! isset($post['current']) || ! isset($post['payload'])) {
            throw new \Phalcon\Exception("Invalid request parameters");
        }

        return $this->bpmn->worker($worker)->next($post['current'], $post['payload']);
    }
    
}
