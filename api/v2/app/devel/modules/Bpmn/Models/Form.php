<?php
namespace App\Bpmn\Models;

use App\Bpmn\Models\FormRole,
    App\Bpmn\Models\FormUser,
    Phalcon\Mvc\Model\Relation;

class Form extends \Micro\Model {

    public function initialize() {
        $this->hasMany(
            'bf_id',
            'App\Bpmn\Models\FormRole',
            'bfr_bf_id',
            array(
                'alias' => 'FormsRoles',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasMany(
            'bf_id',
            'App\Bpmn\Models\FormUser',
            'bfu_bf_id',
            array(
                'alias' => 'FormsUsers',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasManyToMany(
            'bf_id',
            'App\Bpmn\Models\FormRole',
            'bfr_bf_id',
            'bfr_sr_id',
            'App\Roles\Models\Role',
            'sr_id',
            array(
                'alias' => 'Roles'
            )
        );

        $this->hasManyToMany(
            'bf_id',
            'App\Bpmn\Models\FormUser',
            'bfu_bf_id',
            'bfu_su_id',
            'App\Users\Models\User',
            'su_id',
            array(
                'alias' => 'Users'
            )
        );        
    }

    public function getSource() {
        return 'bpm_forms';
    }

    public function saveRoles($items) {
        $create = array();
        $update = array();
        $delete = array();

        $exists = array_map(function($item){ return $item['bfr_id']; }, $this->FormsRoles->toArray());
        $sliced = array();

        foreach($items as $item) {
            if ( ! isset($item['bfr_id'])) {
                $create[] = $item;
            } else {
                if (in_array($item['bfr_id'], $exists)) {
                    $update[] = $item;
                    $sliced[] = $item['bfr_id'];
                }
            }
        }

        for ($i = count($exists) - 1; $i >= 0; $i--) {
            if ( ! in_array($exists[$i], $sliced)) {
                $delete[] = $exists[$i];
            }
        }

        if (count($delete) > 0) {
            FormRole::find('bfr_id IN ('.implode(',', $delete).')')->delete();
        }

        if (count($update) > 0) {
            foreach($update as $item) {
                $prof = FormRole::findFirst($item['bfr_id']);
                $prof->save($item);
            }
        }

        if (count($create) > 0) {
            foreach($create as $item) {
                $prof = new FormRole();
                $item['bfr_bf_id'] = $this->bf_id;
                $prof->save($item);
            }
        }
    }

    public function saveUsers($items) {
        $create = array();
        $update = array();
        $delete = array();

        $exists = array_map(function($item){ return $item['bfu_id']; }, $this->FormsUsers->toArray());
        $sliced = array();

        foreach($items as $item) {
            if ( ! isset($item['bfu_id'])) {
                $create[] = $item;
            } else {
                if (in_array($item['bfu_id'], $exists)) {
                    $update[] = $item;
                    $sliced[] = $item['bfu_id'];
                }
            }
        }

        for ($i = count($exists) - 1; $i >= 0; $i--) {
            if ( ! in_array($exists[$i], $sliced)) {
                $delete[] = $exists[$i];
            }
        }

        if (count($delete) > 0) {
            FormUser::find('bfu_id IN ('.implode(',', $delete).')')->delete();
        }

        if (count($update) > 0) {
            foreach($update as $item) {
                $prof = FormUser::findFirst($item['bfu_id']);
                $prof->save($item);
            }
        }

        if (count($create) > 0) {
            foreach($create as $item) {
                $prof = new FormUser();
                $item['bfu_bf_id'] = $this->bf_id;
                $prof->save($item);
            }
        }
    }
}