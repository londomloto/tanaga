<?php
namespace App\MasterInstrumen\Controllers;

use App\MasterInstrumen\Models\Group;

class GroupController extends \Micro\Controller {
    public function findAction() {
        return Group::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Group();
        
        if ($data->save($post)) {
            return Group::get($data->id_group);
        }

        return Group::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = Group::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Group::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}