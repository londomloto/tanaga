<?php
namespace App\Ponpes\Models;

class AssetGambar extends \Micro\Model {
    
    public function getSource() {
        return 'x_tbl_asset_gambar';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        $data['gambar_url'] = '';
        $data['gambar_thumb'] = '';

        if ( ! empty($data['gambar'])) {
            $URL = \Micro\App::getDefault()->url;
            $data['gambar_url'] = $URL->getBaseUrl().'public/resources/asset/'.$data['gambar'];
            $data['gambar_thumb'] = $URL->getSiteUrl('assets/thumb').'?s=public/resources/asset/'.$data['gambar'];
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