<?php
namespace App\Ponpes\Models;

class Ponpes extends \Micro\Model {

    public function getSource() {
        return 'm_tbl_pesantren';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);

        if (empty($data['img_gedung'])) {
            $data['img_gedung'] = 'default.png';
        }

        $URL = \Micro\App::getDefault()->url;

        $data['img_gedung_url'] = $URL->getBaseUrl().'public/resources/ponpes/'.$data['img_gedung']; 
        $data['img_gedung_thumb'] = $URL->getSiteUrl('/assets/thumb').'?s=public/resources/ponpes/'.$data['img_gedung']; 

        return $data;
    }
}