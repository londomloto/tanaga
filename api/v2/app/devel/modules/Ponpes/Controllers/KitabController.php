<?php
namespace App\Ponpes\Controllers;

use App\Ponpes\Models\Kitab;

class KitabController extends \Micro\Controller {

    public function findAction() {
        return Kitab::get()->filterable()->sortable()->paginate();
    }
    
    public function createAction() {
        $post = $this->request->getJson();
        $data = new Kitab();
        if ($data->save($post)) {
            return Kitab::get($data->id_kitab);
        }
        return Kitab::none();
    }

    public function updateAction($id) {
        $item = Kitab::get($id);
        $post = $this->request->getJson();
        if ($item->data) {
            $item->data->save($post);
        }
        return $item;
    }

    public function deleteAction($id) {
        $data = Kitab::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete();
        }
        return array(
            'success' => $done
        );
    }
}