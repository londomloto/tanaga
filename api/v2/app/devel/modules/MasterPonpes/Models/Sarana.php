<?php
namespace App\MasterPonpes\Models;

class Sarana extends \Micro\Model {

    public function initialize() {
        $this->hasOne(
            'id_jenis_sarana',
            'App\MasterPonpes\Models\JenisSarana',
            'id_jenis_sarana',
            array(
                'alias' => 'Jenis'
            )
        );
    }

    public function getSource() {
        return 'm_tbl_sarana';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);

        if ($this->jenis) {
            $data['jenis_sarana'] = $this->jenis->jenis_sarana;
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