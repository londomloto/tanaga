<?php
namespace App\Bpmn\Models;

class FormRole extends \Micro\Model {

    public function initialize() {
        $this->belongsTo(
            'bfr_sr_id',
            'App\Roles\Models\Role',
            'sr_id',
            array(
                'alias' => 'Role'
            )
        );
    }

    public function getSource() {
        return 'bpm_forms_roles';
    }

    public function toArray($columns = NULL) {
        $role = $this->role;
        
        $data = parent::toArray($columns);
        $data['bfr_sr_name'] = $role ? $role->sr_name : NULL;

        return $data;
    }

}