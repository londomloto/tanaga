<?php
namespace App\Bpmn\Models;

use Micro\Helpers\Text;

class Link extends \Micro\Model {

    public function initialize() {
        $this->hasOne(
            'source_id',
            'App\Bpmn\Models\Shape',
            'id',
            array(
                'alias' => 'Source'
            )
        );

        $this->hasOne(
            'target_id',
            'App\Bpmn\Models\Shape',
            'id',
            array(
                'alias' => 'Target'
            )
        );
    }

    public function getSource() {
        return 'bpm_links';
    }

    public function toArray($columns = NULL) {
        $array = parent::toArray($columns);
        $casts = array('label_distance', 'convex', 'smooth', 'smoothness');
        foreach($casts as $key) {
            $array[$key] = (float) $array[$key];
        }
        return $array;
    }
    
    public function conditions() {
        $params = $this->params;
        try {
            return json_decode($params);
        } catch(\Exception $e){}
        return array();
    }

    public function prevLinks() {
        return $this->source->incomingLinks;
    }

    public function nextLinks() {
        return $this->target->outgoingLinks;
    }

    public static function store(Array $options) {
        $payload = $options['payload'];

        if ( ! empty($payload['label'])) {
            $payload['name'] = Text::slug($payload['label']);
        }

        $data = array(
            'type' => $payload['type'],
            'name' => $payload['name'],
            'client_id' => $payload['guid'],
            'client_source' => $payload['source'],
            'client_target' => $payload['target'],
            'router_type' => $payload['routerType'],
            'diagram_id' => $options['diagramId'],
            'source_id' => $options['sourceId'],
            'target_id' => $options['targetId'],
            'command' => $payload['command'],
            'label' => $payload['label'],
            'label_distance' => $payload['labelDistance'],
            'convex' => (int)$payload['convex'],
            'smooth' => (int)$payload['smooth'],
            'smoothness' => (int)$payload['smoothness'],
            'stroke' => $payload['stroke'],
            // 'data_source' => $payload['dataSource'],
            'params' => json_encode($options['params'])
        );

        if ($options['action'] == 'create') {
            $link = new Link();
        } else {
            $link = self::findFirst($options['linkId']);
        }

        if ($link) {
            $link->save($data);
            return $link;
        }

        return FALSE;
    }
}