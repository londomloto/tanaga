<?php
namespace App\MasterPonpes\Controllers;

use App\MasterPonpes\Models\Tipe;

class TipeController extends \Micro\Controller {
    public function findAction() {
        return Tipe::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Tipe();
        
        if ($data->save($post)) {
            return Tipe::get($data->id_tipe);
        }

        return Tipe::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = Tipe::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Tipe::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}