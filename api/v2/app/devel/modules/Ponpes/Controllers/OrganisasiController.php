<?php
namespace App\Ponpes\Controllers;

use App\Ponpes\Models\Organisasi;

class OrganisasiController extends \Micro\Controller {

    public function findAction() {
        return Organisasi::get()->filterable()->sortable()->paginate();
    }
    
    public function createAction() {
        $post = $this->request->getJson();
        $data = new Organisasi();
        if ($data->save($post)) {
            return Organisasi::get($data->id_org);
        }
        return Organisasi::none();
    }

    public function updateAction($id) {
        $item = Organisasi::get($id);
        $post = $this->request->getJson();
        if ($item->data) {
            $item->data->save($post);
        }
        return $item;
    }

    public function deleteAction($id) {
        $data = Organisasi::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete();
        }
        return array(
            'success' => $done
        );
    }
}