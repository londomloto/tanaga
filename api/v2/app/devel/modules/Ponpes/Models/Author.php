<?php
namespace App\Ponpes\Models;

class Author extends \Micro\Model {
    
    public function initialize() {
        $this->belongsTo(
            'su_id',
            'App\Users\Models\User',
            'su_id',
            array(
                'alias' => 'User'
            )
        );

        $this->belongsTo(
            'id_ponpes',
            'App\Ponpes\Models\Pones',
            'id_ponpes',
            array(
                'alias' => 'Ponpes'
            )
        );
    }

    public function getSource() {
        return 'm_tbl_pesantren_author';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        if ($this->user) {
            $data['su_fullname'] = $this->user->su_fullname;
            $data['su_org_type'] = $this->user->su_org_type;
            $data['su_org_name'] = $this->user->su_org_name;
        }
        return $data;
    }

    public function getEditorName() {
        $user = \Micro\App::getDefault()->auth->user();
        $part = explode(' ', $user['su_fullname']);
        $name = $part[0];

        return $name; 
    }

    public function beforeCreate() {
        $this->create_date = date('Y-m-d H:i:s');
        $this->create_user = $this->getEditorName();
    }

    public function beforeUpdate() {
        $this->last_edit_date = date('Y-m-d H:i:s');
        $this->last_edit_user = $this->getEditorName();
    }

}