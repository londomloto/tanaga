<?php
namespace Micro;

use Phalcon\Mvc\Model\Query\Builder as QueryBuilder,
    Phalcon\Db\Column;

class ModelQuery {

    private $__di;
    private $__builder;
    private $__driver;
    private $__model;
    private $__alias;
    private $__limit;
    private $__start;
    private $__fields;

    public function __construct(\Phalcon\Mvc\Model $model, \Phalcon\DiInterface $di = NULL) {
        $class = get_class($model);

        $this->__builder = new QueryBuilder(array(
            'models' => array($class)
        ));

        $this->__di = $di;
        $this->__builder->setDI($di);
        $this->__model = $model;
        $this->__fields = new \stdClass();
        $this->__driver = $model->getReadConnection()->getType();

        // populate valid fields
        foreach($model->getModelsMetaData()->getDataTypes($model) as $name => $type) {
            $this->__fields->{$name} = array(
                'model' => $class,
                'field' => $name,
                'type' => $type
            );
        }   
    }

    public final function __call($method, $args) {
        if (method_exists($this->__builder, $method)) {
            call_user_func_array(array($this->__builder, $method), $args);
            return $this;
        } else {
            throw new \Phalcon\Exception("Call to undefined method Micro\Paginator::$method");
        }
    }

    public function getBuilder() {
        return $this->__builder;
    }

    public function getFields() {
        return $this->__fields;
    }

    public function alias($alias) {
        $class = get_class($this->__model);

        $this->__builder->from(NULL);
        $this->__builder->addFrom($class, $alias);

        $this->__alias = $alias;

        // update valid fields
        foreach($this->__fields as $key => &$val) {
            if ($val['model'] == $class) {
                $val['field'] = $alias.'.'.$key;
            }
        }

        return $this;
    }

    public function limit($limit, $start = NULL) {
        $this->__limit = $limit;
        $this->__start = $start;

        return $this;
    }

    public function getLimit() {
        return $this->__limit;
    }

    public function getStart() {
        return $this->__start;
    }

    public function join($model, $conditions = NULL, $alias = NULL, $type = 'left') {
        $this->__builder->join($model, $conditions, $alias, $type);

        $instance = new $model();

        foreach($instance->getModelsMetaData()->getDataTypes($instance) as $name => $type) {
            if ( ! isset($this->__fields->{$name})) {
                $this->__fields->{$name} = array(
                    'model' => $model,
                    'field' => ( ! empty($alias) ? $alias.'.' : '').$name,
                    'type'  => $type
                );
            }
        }

        return $this;
    }

    public function sortable() {
        $request = $this->__di->getRequest();
        $sort = $request->getQuery('sort');

        if ( ! empty($sort)) {
            $sort = json_decode($sort);
            foreach($sort as $item) {
                $name = $item->property;
                $sort = $item->direction;
                $maps = isset($this->__fields->{$name}) ? $this->__fields->{$name} : FALSE;

                if ($maps) {
                    $this->__builder->orderBy($maps['field'] . ' ' . $sort);
                }
            }
        }
        return $this;
    }

    public function filterable($options = array()) {
        $request = $this->__di->getRequest();

        $fields = $request->getQuery('fields');
        $query = $request->getQuery('query');

        // query
        if ( ! empty($query) && ! empty($fields)) {
            $fields = json_decode($fields);
            $where = array();

            if ($this->__driver == 'pgsql') {
                foreach($fields as $field) {
                    $attr = isset($this->__fields->{$field}) ? $this->__fields->{$field} : FALSE;
                    if ($attr) {
                        $where[] = 'UPPER(CAST('.$attr['field'].' AS VARCHAR)) LIKE :q:';
                    }
                }
            } else {
                foreach($fields as $field) {
                    $attr = isset($this->__fields->{$field}) ? $this->__fields->{$field} : FALSE;
                    if ($attr) {
                        $where[] = 'UPPER('.$attr['field'].') LIKE :q:';
                    }
                }
            }

            if ( ! empty($where)) {
                $this->__builder->andWhere('(' . implode(' OR ', $where) . ')', array('q' => '%'.strtoupper($query).'%'));    
            }
            
        }

        // params
        $params = $request->getQuery('params');

        if ( ! empty($params)) {
            $params = is_string($params) ? json_decode($params) : $params;

            foreach($params as $key => $val) {
                $attr = isset($this->__fields->{$key}) ? $this->__fields->{$key} : FALSE;
                if ($attr) {
                    $bind = array();
                    $oper = '=';
                    $pkey = 'param_'.$key;
                    
                    if (is_array($val)) {
                        $oper = $val[0]; 

                        switch(strtolower($oper)) {
                            case '=':
                            case '<>':
                            case '<':
                            case '<=':
                            case '>':
                            case '>=':
                                $bind[$pkey] = $val[1];
                                $this->__builder->andWhere($attr['field']." $oper :{$pkey}:", $bind);
                                break;
                            case 'in':
                                $this->__builder->inWhere($attr['field'], $val[1]);
                                break;
                            case 'not in':
                                $this->__builder->notInWhere($attr['field'], $val[1]);
                                break;
                            case 'like':
                                $bind[$pkey] = $val[1];
                                $this->__builder->andWhere($attr['field']." LIKE :{$pkey}:", $bind);
                                break;
                        }
                    } else {
                        $bind[$pkey] = $val;
                        $this->__builder->andWhere($attr['field']." $oper :{$pkey}:", $bind);
                    }
                }
            }
        }

        //print_r($this->__builder->getQuery()->getSql());

        return $this;
    }

    public function execute() {
        if ( ! is_null($this->__limit) && ! is_null($this->__start)) {
            $this->__builder->limit($this->__limit, $this->__start);
        }
        
        $query = $this->__builder->getQuery()->getSql();
        // print_r($query);
        $columns = $this->__builder->getColumns();

        $manager = \Phalcon\DI::getDefault()->get('modelsManager');
        $database = $manager->getReadConnection($this->__model);
        $result = $database->query($query['sql'], $query['bind'], $query['bindTypes']);
        
        $dataset = new ModelDataset(NULL, $this->__model, $result);
        $dataset->setColumns($columns);

        return $dataset;
    }

    public function paginate($fetchRequest = TRUE) {
        if ($fetchRequest && $this->__di) {
            $request = $this->__di->getRequest();

            $limit = $request->getQuery('limit');
            $start = $request->getQuery('start');

            if ($limit != '') {
                $this->__limit = $limit;
                $this->__start = $start;
            }
        }
        
        //print_r($this->__builder->getQuery()->getSql());

        $columns = $this->__builder->getColumns();
        $pageset = new ModelPageset($this);
        $pageset->setColumns($columns);

        return $pageset;
    }
}