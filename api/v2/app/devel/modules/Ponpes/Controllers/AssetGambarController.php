<?php
namespace App\Ponpes\Controllers;

use App\Ponpes\Models\AssetGambar;

class AssetGambarController extends \Micro\Controller {

    public function findAction() {

        return AssetGambar::get()->filterable()->sortable()->paginate();

    }

    public function uploadAction() {
        $result = array(
            'success' => FALSE,
            'data' => NULL
        );

        if ($this->request->hasFiles()) {
            $this->uploader->initialize(array(
                'path' => APPPATH.'public/resources/asset/',
                'encrypt' => TRUE,
                'types' => array('jpeg', 'jpg', 'png')
            ));

            if ($this->uploader->upload()) {
                
                $upload = $this->uploader->getResult();

                $result['success'] = TRUE;
                $result['data'] = array(
                    'gambar' => $upload->filename,
                    'thumb' => $this->url->getSiteUrl('assets/thumb').'?s=public/resources/asset/'.$upload->filename
                );
            }
        }

        return $result;
    }

    public function downloadAction() {
        $post = $this->request->getJson();
        $file = APPPATH.'public/resources/asset/'.$post['image'];

        if (file_exists($file) && ! is_dir($file)) {
            $this->file->download($file);
        } else {
            throw new \Exception("Gambar tidak ditemukan", 404);
        }
    }

}