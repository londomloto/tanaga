<?php
namespace App\Home\Controllers;

use App\Users\Models\User;
use App\Ponpes\Models\Ponpes;

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
                'img_gedung'
            ))
            ->limit(10)
            ->offset(0)
            ->orderBy('rand()')
            ->execute();

        $images = array();
        $base = $this->url->getBaseUrl();

        foreach($result as $item) {
            if ( ! empty($item->img_gedung)) {
                $images[] = array(
                    'image' => $base.'public/resources/ponpes/'.$item->img_gedung
                );
            }
        }

        return array(
            'success' => TRUE,
            'data' => $images
        );
    }

}