<?php
namespace App\Ponpes\Controllers;

use App\Ponpes\Models\Lembaga;

class LembagaController extends \Micro\Controller {

    public function findAction() {
        return Lembaga::get()->filterable()->sortable()->paginate();
    }
    
    public function createAction() {
        $post = $this->request->getJson();
        $data = new Lembaga();
        if ($data->save($post)) {
            return Lembaga::get($data->id_lembaga);
        }
        return Lembaga::none();
    }

    public function updateAction($id) {
        $item = Lembaga::get($id);
        $post = $this->request->getJson();
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