<?php
namespace App\Ponpes\Controllers;

use App\Ponpes\Models\Ponpes;

class MapsController extends \Micro\Controller {

    public function findAction() {

        $format = $this->request->getQuery('format');

        $query = Ponpes::get()
            ->alias('ponpes')
            ->columns(array(
                'ponpes.id_ponpes',
                'MIN(ponpes.alamat) AS alamat',
                'MIN(ponpes.kelurahan) AS kelurahan',
                'MIN(ponpes.kode_pos) AS kode_pos',
                'MIN(ponpes.nama_ponpes) AS nama_ponpes',
                'MIN(ponpes.latitude) AS latitude',
                'MIN(ponpes.longitude) AS longitude',
                'MIN(propinsi.nama_propinsi) AS nama_propinsi',
                'MIN(kota.nama_kota) AS nama_kota',
                'MIN(kecamatan.nama_kecamatan) AS nama_kecamatan',
                'MIN(ponpes.img_gedung) AS img_gedung'
            ))
            ->join('App\MasterWilayah\Models\Propinsi', 'ponpes.kode_propinsi = propinsi.kode_propinsi', 'propinsi', 'left')
            ->join('App\MasterWilayah\Models\Kota', 'ponpes.id_kota = kota.id_kota', 'kota', 'left')
            ->join('App\MasterWilayah\Models\Kecamatan', 'ponpes.id_kecamatan = kecamatan.id_kecamatan', 'kecamatan', 'left')
            ->groupBy('ponpes.id_ponpes');

        if ( ! $this->role->can('manage_app@application')) {
            $auth = $this->auth->user();
            $query->join('App\Ponpes\Models\Author', 'ponpes.id_ponpes = author.id_ponpes', 'author');
            $query->andWhere('author.su_id = :user:', array('user' => $auth['su_id']));
        }

        $points = $query->execute()->filter(function($row){
            $latitude = $row->latitude;
            $longitude = $row->longitude;

            if (empty($latitude) && empty($longitude)) return FALSE;

            $address = array();

            if ( ! empty($row->alamat)) {
                $address[] = $row->alamat;
            }

            if ( ! empty($row->kelurahan)) {
                $address[] = $row->kelurahan;
            }

            if ( ! empty($row->nama_kecamatan)) {
                $address[] = $row->nama_kecamatan;
            }

            if ( ! empty($row->nama_kota)) {
                $address[] = $row->nama_kota;
            }

            if ( ! empty($row->nama_propinsi)) {
                $address[] = $row->nama_propinsi;
            }

            $description = implode(', ', $address);
            $image = ! empty($row->img_gedung) ? $row->img_gedung : Ponpes::IMG_DEFAULT;
            $image = $this->url->getSiteUrl('assets/thumb').'?s=public/resources/ponpes/'.$image;

            $point = array(
                'type' => 'Feature',
                'properties' => array(
                    'id' => $row->id_ponpes,
                    'title' => $row->nama_ponpes,
                    'description' => $description,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'link' => '/ponpes/'.$row->id_ponpes.'/home',
                    'image' => $image
                ),
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => [$longitude, $latitude]
                )
            );

            return $point;
        });

        // construct geoJSON like format
        $json = array(
            'type' => 'FeatureCollection',
            'features' => $points
        );

        if ($format == 'GeoJSON') {
            echo json_encode($json);
            exit();
        } else {
            return array(
                'success' => TRUE,
                'data' => $json
            );
        }

    }

}