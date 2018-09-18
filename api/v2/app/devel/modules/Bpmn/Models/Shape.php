<?php
namespace App\Bpmn\Models;

use Phalcon\Mvc\Model\Relation;

class Shape extends \Micro\Model {

    const SHAPE_TYPE_START = 'Graph.shape.activity.Start';
    const SHAPE_TYPE_STOP = 'Graph.shape.activity.Final';
    const SHAPE_TYPE_ACTION = 'Graph.shape.activity.Action';
    const SHAPE_TYPE_LANE = 'Graph.shape.activity.Lane';

    public function initialize() {
        $this->hasMany(
            'id',
            'App\Bpmn\Models\Link',
            'source_id',
            array(
                'alias' => 'OutgoingLinks'
            )
        );

        $this->hasMany(
            'id',
            'App\Bpmn\Models\Link',
            'target_id',
            array(
                'alias' => 'IncomingLinks'
            )
        );

        $this->belongsTo(
            'parent_id',
            'App\Bpmn\Models\Shape',
            'id',
            array(
                'alias' => 'Parent'
            )
        );

        $this->belongsTo(
            'diagram_id',
            'App\Bpmn\Models\Diagram',
            'id',
            array(
                'alias' => 'Diagram'
            )
        );

        $this->hasMany(
            'id',
            'App\Bpmn\Models\Form',
            'bf_activity',
            array(
                'alias' => 'Forms',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );
    }

    public function getSource() {
        return 'bpm_shapes';
    }

    public function toArray($columns = NULL) {
        $array = parent::toArray($columns);
        $casts = array('top', 'left', 'width', 'height', 'stroke_width', 'rotate');

        foreach($casts as $key) {
            $array[$key] = (float) $array[$key];
        }

        return $array;
    }

    public function isStart() {
        return $this->type == 'Graph.shape.activity.Start';
    }

    public function isStop() {
        return $this->type == 'Graph.shape.activity.Final';   
    }

    public static function store(Array $options) {
        $payload = $options['payload'];

        $data = array(
            'type' => $payload['type'],
            'mode' => $payload['mode'],
            'client_id' => $payload['guid'],
            'client_parent' => $payload['parent'],
            'client_pool' => $payload['pool'],
            'diagram_id' => $options['diagramId'],
            'parent_id' => $options['parent'],
            'width' => $payload['width'],
            'height' => $payload['height'],
            'left' => $payload['left'],
            'rotate' => $payload['rotate'],
            'top' => $payload['top'],
            'label' => $payload['label'],
            'fill' => $payload['fill'],
            'stroke' => $payload['stroke'],
            'stroke_width' => $payload['strokeWidth'],
            // 'data_source' => $payload['dataSource'],
            'params' => json_encode($options['params'])
        );
        
        if ($options['action'] == 'create') {
            $shape = new Shape();
        } else {
            $shape = Shape::findFirst($options['shapeId']);
        }

        if ($shape) {
            $shape->save($data);
            return $shape;
        }

        return FALSE;
    }
}