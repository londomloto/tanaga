<?php
namespace App\Ponpes\Models;

class Kitab extends \Micro\Model {

    public function initialize() {
        $this->hasOne(
            'id_kel_kitab',
            'App\MasterPonpes\Models\KelompokKitab',
            'id_kel_kitab',
            array(
                'alias' => 'Kelompok'
            )
        );
    }

    public function getSource() {
        return 'x_tbl_kitab_ponpes';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        if ($this->kelompok) {
            $data['nama_kel_kitab'] = $this->kelompok->nama_kel_kitab;
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