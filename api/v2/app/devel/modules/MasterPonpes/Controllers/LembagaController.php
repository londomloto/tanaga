<?php
namespace App\MasterPonpes\Controllers;

use App\MasterPonpes\Models\Lembaga;

class LembagaController extends \Micro\Controller {
    public function findAction() {
        return Lembaga::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Lembaga();
        
        if ($data->save($post)) {
            return Lembaga::get($data->id_level);
        }

        return Lembaga::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = Lembaga::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Lembaga::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}