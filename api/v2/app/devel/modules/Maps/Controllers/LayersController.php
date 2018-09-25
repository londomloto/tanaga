<?php
namespace App\Maps\Controllers;

use App\Maps\Models\Layer;

class LayersController extends \Micro\Controller {

    public function findAction() {
        
        // find kml layers
        $layers = Layer::get()
            ->andWhere("tml_type IN ('data')")
            ->andWhere('tml_hide = 0')
            ->orderBy('tml_group ASC')
            ->execute()
            ->filter(function($layer){
                return $layer->toArray();
            });

        return array(
            'success' => TRUE,
            'data' => $layers
        );
    }

    public function renderKmlAction() {
        $kml = $this->request->getQuery('kml');
        $src = APPPATH.'public/resources/maps/'.$kml;
        $this->file->render($src);
    }

    public function parseKmlAction() {
        require_once($this->config->sys->path.'/../vendor/libkml/libkml.php');


        $kml = $this->request->getQuery('kml');
        $kml = file_get_contents(APPPATH.'public/resources/maps/'.$kml);
        $kml = \KML::createFromText($kml);

        $placemarks = $kml->getAllFeatures();

        foreach($placemarks as $p) {
            $g = $p->getGeometry();
            print_r($g);
            print_r($p->getName());
            // print_r($g);
        }

        /*$fs = $kml->getFeature()->getFeatures();

        $data = array(
            'points' => array(),
            'polylines' => array(),
            'polygons' => array()
        );
        
        foreach($fs as $f) {

            $geom = $f->getGeometry();
            $coor = $geom->getCoordinates();

            if ($geom instanceof \libKML\Point) {
                $data['points'][] = array(
                    'name' => $f->getName(),
                    'prop' => json_decode($f->getDescription()),
                    'lat' => floatval($coor->getLatitude()),
                    'lng' => floatval($coor->getLongitude())
                );
            } else if ($geom instanceof \libKML\LineString) {
                $path = array();
                foreach($coor as $c) {
                    $path[] = array(
                        'lat' => floatval($c->getLatitude()),
                        'lng' => floatval($c->getLongitude())
                    );
                }
                $data['polylines'][] = array(
                    'path' => $path
                );
            }
        }
        return array(
            'success' => TRUE,
            'data' => $data
        );*/
    }

}