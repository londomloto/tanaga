<?php
namespace App\MasterWilayah\Models;

class Kecamatan extends \Micro\Model {

    public function initialize() {
        $this->hasOne(
            'kode_kota',
            'App\MasterWilayah\Models\Kota',
            'kode_kota',
            array(
                'alias' => 'Kota'
            )
        );
    }

    public function getSource() {
        return 'm_tbl_kecamatan';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        $data['label_kecamatan'] = $data['kode_kecamatan'].' - '.$data['nama_kecamatan'];
        if ($this->kota) {
            $data['nama_kota'] = $this->kota->nama_kota;
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

    public static function findFirstByKode($kode) {
        return self::findFirst(array(
            'kode_kecamatan = :kode:',
            'bind' => array(
                'kode' => $kode
            )
        ));
    }

}