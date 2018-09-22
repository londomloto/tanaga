<?php
namespace App\MasterWilayah\Controllers;

use App\MasterWilayah\Models\Kota;

class KotaController extends \Micro\Controller {
    public function findAction() {
        return Kota::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $kode = (int) $post['kode_kota'];

        if ( ! $kode) {
            throw new \Exception("Kode kota tidak valid, pastikan berupa angka");
        }

        $data = Kota::findFirstByKode($kode);
        
        if ($data) {
            throw new \Exception("Kode kota sudah digunakan");
        }

        $data = new Kota();
        
        if ($data->save($post)) {
            return Kota::get($data->id_kota);
        }

        return Kota::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $kode = (int)$post['kode_kota'];

        $data = Kota::findFirst($id);

        if ($data) {
            if ($data->kode_kota != $kode) {
                $data = Kota::findFirstByKode($kode);
                if ($data) {
                    throw new \Exception("Kode kota sudah digunakan");
                }
            }
        }

        $item = Kota::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Kota::get($id)->data;
        $done = FALSE;

        if ($data) {
            $done = $data->delete(); 
        }

        return array(
            'success' => $done
        );
    }
}