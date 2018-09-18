<?php
namespace App\MasterWilayah\Controllers;

use App\MasterWilayah\Models\Propinsi;

class PropinsiController extends \Micro\Controller {
    public function findAction() {
        return Propinsi::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Propinsi();
        
        if ($data->save($post)) {
            return Propinsi::get($data->id_propinsi);
        }

        return Propinsi::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = Propinsi::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Propinsi::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}