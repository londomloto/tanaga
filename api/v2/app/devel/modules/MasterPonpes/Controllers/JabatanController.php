<?php
namespace App\MasterPonpes\Controllers;

use App\MasterPonpes\Models\Jabatan;

class JabatanController extends \Micro\Controller {
    public function findAction() {
        return Jabatan::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Jabatan();
        
        if ($data->save($post)) {
            return Jabatan::get($data->id_orgz);
        }

        return Jabatan::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = Jabatan::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Jabatan::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}