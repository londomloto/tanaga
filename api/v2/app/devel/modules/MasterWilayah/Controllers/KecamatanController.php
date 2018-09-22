<?php
namespace App\MasterWilayah\Controllers;

use App\MasterWilayah\Models\Kecamatan;

class KecamatanController extends \Micro\Controller {
    public function findAction() {
        return Kecamatan::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $kode = (int) $post['kode_kecamatan'];

        if ( ! $kode) {
            throw new \Exception("Kode kecamatan tidak valid, pastikan berupa angka");
        }

        $data = Kecamatan::findFirstByKode($kode);
        
        if ($data) {
            throw new \Exception("Kode kecamatan sudah digunakan");
        }

        $data = new Kecamatan();

        if ($data->save($post)) {
            return Kecamatan::get($data->id_kecamatan);
        }

        return Kecamatan::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $kode = (int)$post['kode_kecamatan'];

        $data = Kecamatan::findFirst($id);

        if ($data) {
            if ($data->kode_kecamatan != $kode) {
                $data = Kecamatan::findFirstByKode($kode);
                if ($data) {
                    throw new \Exception("Kode kecamatan sudah digunakan");
                }
            }
        }

        $item = Kecamatan::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Kecamatan::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}