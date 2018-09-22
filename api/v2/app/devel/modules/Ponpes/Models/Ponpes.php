<?php
namespace App\Ponpes\Models;

class Ponpes extends \Micro\Model {

    const IMG_DEFAULT = 'default-5.jpg';

    public function initialize() {
        $this->hasMany(
            'id_ponpes',
            'App\Ponpes\Models\Author',
            'id_ponpes',
            array(
                'alias' => 'Authors',
                'foreignKey' => array(
                    'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasOne(
            'kode_kecamatan',
            'App\MasterWilayah\Models\Kecamatan',
            'kode_kecamatan',
            array(
                'alias' => 'Kecamatan'
            )
        );

        $this->hasOne(
            'kode_kota',
            'App\MasterWilayah\Models\Kota',
            'kode_kota',
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
        return 'm_tbl_pesantren';
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

        $data['img_gedung_url'] = $URL->getBaseUrl().'public/resources/ponpes/'.$data['img_gedung']; 
        $data['img_gedung_thumb'] = $URL->getSiteUrl('/assets/thumb').'?s=public/resources/ponpes/'.$data['img_gedung']; 

        return $data;
    }
}