<?php
namespace App\Kanban\Models;

use App\Kanban\Models\KanbanStatus;

class KanbanPanel extends \Micro\Model {

    public function initialize() {
        $this->hasMany(
            'kp_id',
            'App\Kanban\Models\KanbanStatus',
            'kst_kp_id',
            array(
                'alias' => 'Statuses',
                'foreignKey' => array(
                    'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
                )
            )
        );
    }

    public function getSource() {
        return 'kanban_panels';
    }

    public function saveStatuses($items) {
        $create = array();
        $update = array();
        $delete = array();

        $exists = array_map(function($item){ return $item['kst_id']; }, $this->statuses->toArray());
        $sliced = array();

        foreach($items as $item) {
            if ( ! isset($item['kst_id'])) {
                $create[] = $item;
            } else {
                if (in_array($item['kst_id'], $exists)) {
                    $update[] = $item;
                    $sliced[] = $item['kst_id'];
                }
            }
        }

        for ($i = count($exists) - 1; $i >= 0; $i--) {
            if ( ! in_array($exists[$i], $sliced)) {
                $delete[] = $exists[$i];
            }
        }

        if (count($delete) > 0) {
            KanbanStatus::find('kst_id IN ('.implode(',', $delete).')')->delete();
        }

        if (count($update) > 0) {
            foreach($update as $item) {
                $status = KanbanStatus::findFirst($item['kst_id']);
                $status->save($item);
            }
        }

        if (count($create) > 0) {
            foreach($create as $item) {
                $status = new KanbanStatus();
                $item['kst_kp_id'] = $this->kp_id;
                $status->save($item);
            }
        }
    }

}