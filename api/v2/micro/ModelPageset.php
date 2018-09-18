<?php
namespace Micro;

use Micro\Paginator\Adapter\QueryBuilder as QueryBuilderPaginator;

class ModelPageset implements \JsonSerializable {

    public $success = TRUE;
    private $__columns;

    public function __construct(ModelQuery $query) {
        
        $limit = (int) $query->getLimit();
        $builder = $query->getBuilder();
        
        if (empty($limit)) {
            $this->data = $query->getQuery()->execute();
            $this->count = $this->total = count($this->data);
            $this->page = $this->pages = $this->count > 0 ? 1 : 0;
        } else {

            $start = (int) $query->getStart();
            $page = floor($start / $limit) + 1;
            
            $paginator = new QueryBuilderPaginator(array(
                'builder' => $builder,
                'limit' => $limit,
                'page' => $page
            ));

            $result = $paginator->getPaginate();

            $this->data = $result->items;
            $this->count = $result->items->count();
            $this->total = $result->total_items;
            $this->pages = $result->total_pages;
            $this->page = $result->current;
        }
    }

    public function setColumns($columns) {
        $this->__columns = $columns;
    }

    public function map($handler) {
        $data = array();

        foreach($this->data as $model) {
            $data[] = $handler($model);
        }

        $this->data = $data;
        return $this;
    }

    public function filter($handler) {
        $this->data = $this->data->filter(function($model) use ($handler){
            return $handler($model);
        });
        return $this;
    }

    public function getFirst() {
        return $this->data->getFirst();
    }

    public function getLast() {
        return $this->data->getLast();
    }

    public function toArray() {
        $props = get_object_vars($this);
        $array = array();

        foreach($props as $key => $val) {
            if ($key == '__columns') continue;

            if ($key == 'data') {
                if ($val instanceof \Phalcon\Mvc\Model\Resultset) {
                    $val = $val->filter(function($model){ return $model; });
                }

                $data = array();

                if (is_null($this->__columns)) {
                    foreach($val as $model) {
                        if ($model instanceof \Phalcon\Mvc\Model) {
                            $data[] = $model->toArray();
                        } else {
                            $data[] = $model;
                        }
                    }
                } else {
                    foreach($val as $model) {
                        $item = array();
                        foreach($model as $k => $v) {
                            $item[$k] = $v;
                        }
                        $data[] = $item;
                    }
                }

                $array['data'] = $data;
            } else {
                $array[$key] = $val;
            }
        }
        
        return $array;
    }

    /**
     * @Override
     */
    public function jsonSerialize() {
        return $this->toArray();
    }

}