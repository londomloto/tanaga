<?php
namespace App\Ponpes\Controllers;

use App\Ponpes\Models\Sarana;

class SaranaController extends \Micro\Controller {

    public function findAction() {
        return Sarana::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Sarana();
        if ($data->save($post)) {
            return Sarana::get($data->id_item);
        }
        return Sarana::none();
    }

    public function updateAction($id) {
        $item = Sarana::get($id);
        $post = $this->request->getJson();
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