<?php
namespace App\Ponpes\Models;

class Instrumen extends \Micro\Model {

    public function initialize() {
        $this->hasOne(
            'id_instrumen',
            'App\MasterInstrumen\Models\Instrumen',
            'id_instrumen',
            array(
                'alias' => 'Master'
            )
        );
    }

    public function getSource() {
        return 'x_tbl_instrumen_ponpes';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        if ($this->master) {
            $data['kode_init'] =  $this->master->kode_init;
            $data['deskripsi'] =  $this->master->deskripsi;
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