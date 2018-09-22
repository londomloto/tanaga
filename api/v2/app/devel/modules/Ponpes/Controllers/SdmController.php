<?php
namespace App\Ponpes\Controllers;

use App\Ponpes\Models\Sdm;

class SdmController extends \Micro\Controller {

    public function findAction() {
        return Sdm::get()->filterable()->sortable()->paginate();
    }
    
    public function createAction() {
        $post = $this->request->getJson();
        $data = new Sdm();
        if ($data->save($post)) {
            return Sdm::get($data->id_sdm);
        }
        return Sdm::none();
    }

    public function updateAction($id) {
        $item = Sdm::get($id);
        $post = $this->request->getJson();
        if ($item->data) {
            $item->data->save($post);
        }
        return $item;
    }

    public function deleteAction($id) {
        $data = Sdm::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete();
        }
        return array(
            'success' => $done
        );
    }
}