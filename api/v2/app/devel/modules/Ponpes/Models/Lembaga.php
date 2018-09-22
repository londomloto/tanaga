<?php
namespace App\Ponpes\Models;

class Lembaga extends \Micro\Model {

    public function initialize() {
        $this->hasOne(
            'id_level',
            'App\MasterPonpes\Models\Lembaga',
            'id_level',
            array(
                'alias' => 'Level'
            )
        );
    }

    public function getSource() {
        return 'x_tbl_lembaga_ponpes';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        if ($this->level) {
            $data['level_pendidikan'] =  $this->level->level_pendidikan;
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