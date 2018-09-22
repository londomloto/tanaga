<?php
namespace App\Ponpes\Models;

class Organisasi extends \Micro\Model {

    public function initialize() {
        $this->hasOne(
            'id_tipe_org',
            'App\MasterPonpes\Models\JenisOrganisasi',
            'id_tipe_org',
            array(
                'alias' => 'Jenis'
            )
        );
    }

    public function getSource() {
        return 'x_tbl_organisasi_ponpes';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        if ($this->jenis) {
            $data['jenis_org'] =  $this->jenis->jenis_org;
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