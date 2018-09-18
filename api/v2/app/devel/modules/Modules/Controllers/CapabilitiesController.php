<?php
namespace App\Modules\Controllers;

use App\Modules\Models\Capability;

class CapabilitiesController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getQuery();
        $query = Capability::query();

        if (isset($params['module'])) {
            $query->where('smc_sm_id = :module:', array('module' => $params['module']));
        }

        return $query->paginate();
    }

    public function createAction() {
        $data = $this->request->getJson();
        $caps = new Capability();
        $caps->save($data);

        return array(
            'success' => TRUE,
            'data' => $caps->toArray()
        );
    }

    public function updateAction($id) {
        $query = Capability::queryFirst($id);
        if ($query->data) {
            $data = $this->request->getJson();
            $query->data->save($data);
        }
        return $query;
    }

    public function deleteAction($id) {
        $query = Capability::queryFirst($id);
        if ($query->data) {
            if ($query->data->delete()) {
                return array(
                    'success' => TRUE
                );
            }
        }
        return array(
            'success' => FALSE
        );
    }
}