<?php
namespace App\Ponpes\Controllers;

use App\Ponpes\Models\AssetPotensi;

class AssetPotensiController extends \Micro\Controller {
    public function findAction() {
        return AssetPotensi::get()->filterable()->sortable()->paginate();
    }

    public function createAction(){
        $post = $this->request->getJson();
        $data = new AssetPotensi();

        if ($data->save($post)) {
            return AssetPotensi::get($data->id_asset_potensi);
        }
        return AssetPotensi::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = AssetPotensi::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = AssetPotensi::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete();
        }

        return array(
            'success' => $done
        );
    }
}