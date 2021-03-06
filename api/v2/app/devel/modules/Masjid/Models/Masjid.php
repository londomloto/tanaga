<?php
namespace App\Masjid\Models;

class Masjid extends \Micro\Model {

    const IMG_DEFAULT = 'default-2.jpg';

    public function initialize() {
        $this->hasMany(
            'id_rumah_ibadah',
            'App\Masjid\Models\Author',
            'id_rumah_ibadah',
            array(
                'alias' => 'Authors',
                'foreignKey' => array(
                    'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasOne(
            'id_kecamatan',
            'App\MasterWilayah\Models\Kecamatan',
            'id_kecamatan',
            array(
                'alias' => 'Kecamatan'
            )
        );

        $this->hasOne(
            'id_kota',
            'App\MasterWilayah\Models\Kota',
            'id_kota',
            array(
                'alias' => 'Kota'
            )
        );

        $this->hasOne(
            'kode_propinsi',
            'App\MasterWilayah\Models\Propinsi',
            'kode_propinsi',
            array(
                'alias' => 'Propinsi'
            )
        );
    }

    public function getSource() {
        return 'm_tbl_rumah_ibadah';
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

    public function beforeSave() {
        $nulls = array(
            'id_kota',
            'id_kecamatan'
        );

        foreach($nulls as $k) {
            if (isset($this->$k) && $this->$k == '') {
                $this->$k = NULL;
            }
        }
    }

    public function getComputedAddress() {
        $items = array();
        if ( ! empty($this->alamat)) {
            $items[] = $this->alamat;
        }

        if ( ! empty($this->kelurahan)) {
            $items[] = $this->kelurahan;
        }

        if ($this->kecamatan) {
            $items[] = $this->kecamatan->nama_kecamatan;
        }

        if ($this->kota) {
            $items[] = $this->kota->nama_kota;
        }

        if ($this->propinsi) {
            $items[] = $this->propinsi->nama_propinsi;
        }

        if (count($items) > 0) {
            return implode(', ', $items);
        } else {
            return 'Tidak ada alamat';
        }
    }
    
    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        $data['text_alamat'] = $this->getComputedAddress();

        if (empty($data['img_gedung'])) {
            $data['img_gedung'] = self::IMG_DEFAULT;
        }

        $URL = \Micro\App::getDefault()->url;

        $data['img_gedung_url'] = $URL->getBaseUrl().'public/resources/masjid/'.$data['img_gedung']; 
        $data['img_gedung_thumb'] = $URL->getSiteUrl('/assets/thumb').'?s=public/resources/masjid/'.$data['img_gedung']; 

        return $data;
    }
}