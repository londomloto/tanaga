<?php
namespace App\MasterPonpes\Controllers;

use App\MasterPonpes\Models\Sarana;

class SaranaController extends \Micro\Controller {
    public function findAction() {
        return Sarana::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Sarana();
        
        if ($data->save($post)) {
            return Sarana::get($data->id_sarana);
        }

        return Sarana::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = Sarana::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Sarana::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}