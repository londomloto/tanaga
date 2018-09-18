<?php
namespace Micro;

class ModelDataset extends \Phalcon\Mvc\Model\Resultset\Simple {

    private $__columns;

    public function map($handler) {
        return $this->filter($handler);
    }

    public function setColumns($columns) {
        $this->__columns = $columns;
    }

    public function jsonSerialize() {
        if ( ! is_null($this->__columns)) {
            return $this->filter(function($model){
                $data = array();
                foreach($model as $k => $v) {
                    $data[$k] = $v;
                }
                return $data;
            });
        } else {
            return $this->filter(function($model){
                return $model->toArray();
            });    
        }
    }
}