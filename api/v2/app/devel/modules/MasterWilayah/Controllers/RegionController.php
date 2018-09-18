<?php
namespace App\MasterWilayah\Controllers;

use App\MasterWilayah\Models\Region;

class RegionController extends \Micro\Controller {
    public function findAction() {
        return Region::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Region();
        
        if ($data->save($post)) {
            return Region::get($data->id_region);
        }

        return Region::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = Region::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Region::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}