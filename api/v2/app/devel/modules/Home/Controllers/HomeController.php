<?php
namespace App\Home\Controllers;

use App\Users\Models\User;

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
}