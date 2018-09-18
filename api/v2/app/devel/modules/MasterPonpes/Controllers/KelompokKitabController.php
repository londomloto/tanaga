<?php
namespace App\MasterPonpes\Controllers;

use App\MasterPonpes\Models\KelompokKitab;

class KelompokKitabController extends \Micro\Controller {
    public function findAction() {
        return KelompokKitab::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new KelompokKitab();
        
        if ($data->save($post)) {
            return KelompokKitab::get($data->id_kel_kitab);
        }

        return KelompokKitab::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = KelompokKitab::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = KelompokKitab::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}