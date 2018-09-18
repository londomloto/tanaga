<?php
namespace App\MasterPonpes\Controllers;

use App\MasterPonpes\Models\Organizer;

class OrganizerController extends \Micro\Controller {
    public function findAction() {
        return Organizer::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Organizer();
        
        if ($data->save($post)) {
            return Organizer::get($data->id_orgz);
        }

        return Organizer::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = Organizer::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Organizer::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}