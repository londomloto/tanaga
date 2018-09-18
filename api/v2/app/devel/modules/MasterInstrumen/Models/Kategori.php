<?php
namespace App\MasterInstrumen\Models;

class Kategori extends \Micro\Model {

    public function getSource() {
        return 'm_tbl_kategori_group';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        $data['label_kategori'] = $data['kode_kategori'].' - '.$data['nama_kategori'];
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