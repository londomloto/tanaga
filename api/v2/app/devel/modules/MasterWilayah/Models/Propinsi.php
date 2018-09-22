<?php
namespace App\MasterWilayah\Models;

class Propinsi extends \Micro\Model {

    public function initialize() {
        $this->belongsTo(
            'kode_region',
            'App\MasterWilayah\Models\Region',
            'kode_region',
            array(
                'alias' => 'Region'
            )
        );
    }

    public function getSource() {
        return 'm_tbl_propinsi';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        if ($this->region) {
            $data['nama_region'] = $this->region->nama_region;
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