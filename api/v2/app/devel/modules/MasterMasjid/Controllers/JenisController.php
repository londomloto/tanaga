<?php
namespace App\MasterMasjid\Controllers;

use App\MasterMasjid\Models\Jenis;

class JenisController extends \Micro\Controller {
    public function findAction() {
        return Jenis::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Jenis();
        
        if ($data->save($post)) {
            return Jenis::get($data->id_masjid);
        }

        return Jenis::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = Jenis::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Jenis::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}