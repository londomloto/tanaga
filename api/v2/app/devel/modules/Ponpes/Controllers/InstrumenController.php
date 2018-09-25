<?php
namespace App\Ponpes\Controllers;

use App\MasterInstrumen\Models\Instrumen as Master;
use App\Ponpes\Models\Instrumen;

class InstrumenController extends \Micro\Controller {

    public function findAction() {
        $payload = $this->request->getQuery();
        $ponpes = (int)$payload['id_ponpes'];

        $query = Master::get()
            ->alias('a')
            ->columns(array(
                'b.id_data',
                'a.id_instrumen',
                'a.kode_group',
                'a.kode_init',
                'a.deskripsi',
                'b.nilai'
                /*'a.*'*/
            ));

        if (isset($payload['context'])) {
            $context = json_decode($payload['context'], TRUE);
            $w = array();
            foreach($context as $c) {
                $w[] = "UPPER(a.kode_init) LIKE '".strtoupper($c)."%'";
            }
            if (count($w) > 0) {
                $query->andWhere('('.implode(' OR ', $w).')');
            }
        }

        

        if (isset($payload['tahun'])) {
            $query->join('App\Ponpes\Models\Instrumen', 'a.id_instrumen = b.id_instrumen AND b.id_ponpes = '.$ponpes.' AND b.tahun = '.$payload['tahun'], 'b', 'LEFT');
        } else {
            $query->join('App\Ponpes\Models\Instrumen', 'a.id_instrumen = b.id_instrumen AND b.id_ponpes = '.$ponpes, 'b', 'LEFT');
        }
        
        $result = $query->execute()->filter(function($row){
            $arr = array(
                'id_data' => $row->id_data,
                'id_instrumen' => $row->id_instrumen,
                'kode_group' => $row->kode_group,
                'kode_init' => $row->kode_init,
                'deskripsi' => $row->deskripsi,
                'nilai' => $row->nilai
            );
            return $arr;
        });

        return array(
            'success' => TRUE,
            'data' => $result
        );

    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Instrumen();
        if ($data->save($post)) {
            return Instrumen::get($data->id_data);
        }
        return Instrumen::none();
    }

    public function updateAction($id) {
        $item = Instrumen::get($id);
        $post = $this->request->getJson();
        if ($item->data) {
            $item->data->save($post);
        }
        return $item;
    }

}