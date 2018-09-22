<?php
namespace App\Ponpes\Models;

class Sdm extends \Micro\Model {

    public function initialize() {
        $this->hasOne(
            'id_jabatan',
            'App\MasterPonpes\Models\Jabatan',
            'id_jabatan',
            array(
                'alias' => 'Jabatan'
            )
        );
    }

    public function getSource() {
        return 'x_tbl_sdm_ponpes';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        if ($this->jabatan) {
            $data['nama_jabatan'] =  $this->jabatan->nama_jabatan;
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