<?php
namespace App\Ponpes\Models;

class AssetPotensi extends \Micro\Model {
    public function initialize() {
        $this->hasOne(
            'kode_group',
            'App\MasterInstrumen\Models\Group',
            'kode_group',
            array(
                'alias' => 'Group'
            )
        );

        $this->hasOne(
            'kode_init',
            'App\MasterInstrumen\Models\Instrumen',
            'kode_init',
            array(
                'alias' => 'Potensi'
            )
        );

        $this->hasMany(
            'id_asset_potensi',
            'App\Ponpes\Models\AssetGambar',
            'id_asset_potensi',
            array(
                'alias' => 'Images',
                'foreignKey' => array(
                    'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
                )
            )
        );
    }

    public function getSource() {
        return 'x_tbl_asset_potensi_ponpes';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        $data['label_dimensi'] = $data['besar_asset'].' '.$data['sat_unit_asset'];

        if ($this->group) {
            $data['label_group'] = $this->group->deskripsi;
        }

        if ($this->potensi) {
            $data['label_potensi'] = $this->potensi->deskripsi;
        }

        $data['has_images'] = $this->images->count() > 0;

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

    public function saveImages($items) {
        if (count($items) === 0) {
            $this->images->delete();
            return;
        }

        $create = array();
        $update = array();
        $delete = array();

        $exists = array_map(
            function($item) {
                return $item['id_gambar'];
            },
            $this->images->toArray()
        );

        $sliced = array();

        foreach($items as $item) {
            if ( ! isset($item['id_gambar']) || empty($item['id_gambar'])) {
                $create[] = $item;
            } else {
                if (in_array($item['id_gambar'], $exists)) {
                    $update[] = $item;
                    $sliced[] = $item['id_gambar'];
                }
            }
        }

        for ($i = count($exists) - 1; $i >= 0; $i--) {
            if ( ! in_array($exists[$i], $sliced)) {
                $delete[] = $exists[$i];
            }
        }

        if (count($delete) > 0) {
            AssetGambar::find('id_gambar IN ('.implode(',', $delete).')')->delete();
        }

        if (count($create) > 0) {
            foreach($create as $item) {
                $k = new AssetGambar();
                $item['id_asset_potensi'] = $this->id_asset_potensi;
                $k->save($item);
            }
        }
    }
}