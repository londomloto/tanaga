<?php
namespace App\MasterInstrumen\Controllers;

use App\MasterInstrumen\Models\Kategori;

class KategoriController extends \Micro\Controller {
    public function findAction() {
        return Kategori::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Kategori();
        
        if ($data->save($post)) {
            return Kategori::get($data->id_kategori);
        }

        return Kategori::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = Kategori::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Kategori::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}