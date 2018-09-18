<?php
namespace App\MasterPonpes\Controllers;

use App\MasterPonpes\Models\JenisOrganisasi;

class JenisOrganisasiController extends \Micro\Controller {
    public function findAction() {
        return JenisOrganisasi::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new JenisOrganisasi();
        
        if ($data->save($post)) {
            return JenisOrganisasi::get($data->id_tipe_org);
        }

        return JenisOrganisasi::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = JenisOrganisasi::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = JenisOrganisasi::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}