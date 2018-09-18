<?php
namespace App\MasterInstrumen\Controllers;

use App\MasterInstrumen\Models\Instrumen;

class InstrumenController extends \Micro\Controller {
    public function findAction() {
        return Instrumen::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Instrumen();
        
        if ($data->save($post)) {
            return Instrumen::get($data->id_instrumen);
        }

        return Instrumen::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = Instrumen::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Instrumen::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}