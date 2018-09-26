<?php
namespace App\Masjid\Controllers;

use App\Masjid\Models\Masjid;

class MapsController extends \Micro\Controller {

    public function findAction() {

        $format = $this->request->getQuery('format');

        $query = Masjid::get()
            ->alias('masjid')
            ->columns(array(
                'masjid.id_rumah_ibadah',
                'MIN(masjid.alamat) AS alamat',
                'MIN(masjid.kelurahan) AS kelurahan',
                'MIN(masjid.kode_pos) AS kode_pos',
                'MIN(masjid.nama_rumah_ibadah) AS nama_rumah_ibadah',
                'MIN(masjid.latitude) AS latitude',
                'MIN(masjid.longitude) AS longitude',
                'MIN(propinsi.nama_propinsi) AS nama_propinsi',
                'MIN(kota.nama_kota) AS nama_kota',
                'MIN(kecamatan.nama_kecamatan) AS nama_kecamatan',
                'MIN(masjid.img_gedung) AS img_gedung'
            ))
            ->join('App\MasterWilayah\Models\Propinsi', 'masjid.kode_propinsi = propinsi.kode_propinsi', 'propinsi', 'left')
            ->join('App\MasterWilayah\Models\Kota', 'masjid.id_kota = kota.id_kota', 'kota', 'left')
            ->join('App\MasterWilayah\Models\Kecamatan', 'masjid.id_kecamatan = kecamatan.id_kecamatan', 'kecamatan', 'left')
            ->groupBy('masjid.id_rumah_ibadah');

        if ( ! $this->role->can('manage_app@application')) {
            $auth = $this->auth->user();
            $query->join('App\Masjid\Models\Author', 'masjid.id_rumah_ibadah = author.id_rumah_ibadah', 'author');
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
            $image = ! empty($row->img_gedung) ? $row->img_gedung : Masjid::IMG_DEFAULT;
            $image = $this->url->getSiteUrl('assets/thumb').'?s=public/resources/masjid/'.$image;

            $point = array(
                'type' => 'Feature',
                'properties' => array(
                    'id' => $row->id_rumah_ibadah,
                    'title' => $row->nama_rumah_ibadah,
                    'description' => $description,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'link' => '/masjid/'.$row->id_rumah_ibadah.'/home',
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