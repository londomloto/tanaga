<?php
namespace App\MasterInstrumen\Controllers;

use App\MasterInstrumen\Models\Instrumen;

class InstrumenController extends \Micro\Controller {
    public function findAction() {
        $payload = $this->request->getQuery();
        $query = Instrumen::get()
            ->filterable();
        
        if (isset($payload['params'])) {
            $params = json_decode($payload['params'], TRUE);
            if (isset($params['context'])) {
                $w = array();
                foreach($params['context'] as $item) {
                    $w[] = "UPPER(kode_group) LIKE '".$item."%'";
                }
                if (count($w) > 0) {
                    $query->andWhere('('.implode(' OR ', $w).')');
                }
            }
        }

        return $query->sortable()->paginate();
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