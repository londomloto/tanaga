<?php
namespace App\Bpmn\Models;

class FormUser extends \Micro\Model {

    public function initialize() {
        $this->belongsTo(
            'bfu_su_id',
            'App\Users\Models\User',
            'su_id',
            array(
                'alias' => 'User'
            )
        );
    }

    public function getSource() {
        return 'bpm_forms_users';
    }

    public function toArray($columns = NULL) {
        $user = $this->user;
        
        $data = parent::toArray($columns);
        $data['bfu_su_fullname'] = $user ? $user->su_fullname : NULL;

        return $data;
    }
}