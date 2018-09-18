<?php
namespace App\Kanban\Models;

class KanbanStatus extends \Micro\Model {

    public function initialize() {
        $this->belongsTo(
            'kst_status',
            'App\Bpmn\Models\Link',
            'id',
            array(
                'alias' => 'Link'
            )
        );

        $this->belongsTo(
            'kst_diagrams_id',
            'App\Bpmn\Models\Diagram',
            'id',
            array(
                'alias' => 'Diagram'
            )
        );

        $this->hasOne(
            'kst_status',
            'App\Kanban\Models\KanbanForm',
            'kf_status',
            array(
                'alias' => 'Form'
            )
        );

        $this->hasOne(
            'kst_kp_id',
            'App\Kanban\Models\KanbanPanel',
            'kp_id',
            array(
                'alias' => 'Panel'
            )
        );
    }

    public function getSource() {
        return 'kanban_statuses';
    }

    public function toArray($columns = NULL) {
        $array = parent::toArray($columns);
        $link = $this->link;
        $form = $this->form;
        $diagram = $this->diagram;

        $array['kst_label'] = $link->label;
        $array['kst_name'] = $link->name;
        $array['kst_color'] = $link->stroke;
        $array['kst_diagrams_name'] = $diagram->name;
        $array['kst_type'] = 'common';
        $array['kst_kf_id'] = '';

        if ($form) {
            $array['kst_kf_id'] = $form->kf_id;
        }

        $bpmn = \Phalcon\DI::getDefault()->get('bpmn');

        $worker = $bpmn->worker($diagram->slug);

        if ($worker) {
            $start = $worker->start();
            if ($start['data'] && $start['data']['id'] == $this->kst_status) {
                $array['kst_type'] = 'start';
            }
        }

        return $array;
    }

}