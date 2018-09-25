<?php
namespace App\Maps\Models;

class Layer extends \Micro\Model {

    public function getSource() {
        return 'trx_maps_layers';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        $data['tml_kml_url'] = NULL;
        
        if ( ! empty($data['tml_styles'])) {
            $styles = json_decode($data['tml_styles'], TRUE);
            if ($styles) {
                if (isset($styles['marker'])) {
                    $data['tml_marker_options'] = $styles['marker'];
                }
                if (isset($styles['polyline'])) {
                    $data['tml_polyline_options'] = $styles['polyline'];
                }
                if (isset($styles['polygon'])) {
                    $data['tml_polygon_options'] = $styles['polygon'];
                }
            } 
        }

        if ( ! empty($data['tml_kml'])) {
            if (substr($data['tml_kml'], 0, 4) != 'http') {
                $data['tml_kml_url'] = \Micro\App::getDefault()->url->getBaseUrl().'public/resources/maps/'.$data['tml_kml'];    
            } else {
                $data['tml_kml_url'] = $data['tml_kml'];
            }
        }

        return $data;
    }

}
