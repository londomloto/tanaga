<?php
namespace App\MasterPonpes\Controllers;

use App\MasterPonpes\Models\JenisSarana;

class JenisSaranaController extends \Micro\Controller {
    public function findAction() {
        return JenisSarana::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new JenisSarana();
        
        if ($data->save($post)) {
            return JenisSarana::get($data->id_kel_kitab);
        }

        return JenisSarana::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = JenisSarana::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = JenisSarana::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}