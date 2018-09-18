<?php
namespace App\Assets\Controllers;

class AssetsController extends \Micro\Controller {
    /**
     * GET
     */
    public function thumbAction() {
        $q = $this->request->getQuery();

        $s = $q['s'];
        $w = $q['w'];
        $h = $q['h'];

        if (empty($h)) {
            $h = 100;
        }

        if (empty($w)) {
            $w = 100;
        }
        
        $image = new \Micro\Image(APPPATH.$s);
        $image->thumb($w, $h);
    }

    /**
     * GET
     */
    public function downloadAction() {
        $q = $this->request->getQuery();
    }
}