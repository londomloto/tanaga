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