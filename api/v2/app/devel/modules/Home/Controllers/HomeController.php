<?php
namespace App\Home\Controllers;

use App\Users\Models\User;
use App\Ponpes\Models\Ponpes;
use App\Masjid\Models\Masjid;

class HomeController extends \Micro\Controller {

    public function findAction() {
        $this->role->validate('update@home');
    }

    public function downloadAction() {
        $post = $this->request->getPost();
        $avatar = PUBPATH.'resources/avatars/10.jpg';

        if ($this->file->exists($avatar)) {
            $this->file->download($avatar);
        }
    }

    public function backgroundsAction() {
        $result = Ponpes::get()
            ->columns(array(
                'id_ponpes',
                'img_gedung'
            ))
            ->where("img_gedung != ''")
            ->limit(5)
            ->offset(0)
            ->orderBy('rand()')
            ->execute();

        $images = array();
        $base = $this->url->getBaseUrl();

        foreach($result as $item) {
            if ($item->img_gedung != Ponpes::IMG_DEFAULT) {
                $file = APPPATH.'public/resources/ponpes/'.$item->img_gedung;
                if (file_exists($file)) {
                    $images['P'.$item->id_ponpes] = array(
                        'image' => $base.'public/resources/ponpes/'.$item->img_gedung
                    );    
                }
            }
        }

        $result = Masjid::get()
            ->columns(array(
                'id_rumah_ibadah',
                'img_gedung'
            ))
            ->where("img_gedung != ''")
            ->limit(5)
            ->offset(0)
            ->orderBy('rand()')
            ->execute();

        foreach($result as $item) {
            if ($item->img_gedung != Masjid::IMG_DEFAULT) {
                $file = APPPATH.'public/resources/masjid/'.$item->img_gedung;
                if (file_exists($file)) {
                    $images['M'.$item->id_rumah_ibadah] = array(
                        'image' => $base.'public/resources/masjid/'.$item->img_gedung
                    );    
                }
            }
        }

        $keys = array_keys($images);
        shuffle($keys);
        $random = array();

        foreach($keys as $k) {
            $random[$k] = $images[$k];
        }

        $images = array_values($random);

        return array(
            'success' => TRUE,
            'data' => $images
        );
    }

}