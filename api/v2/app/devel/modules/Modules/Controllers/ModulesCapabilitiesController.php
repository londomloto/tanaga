<?php
namespace App\Modules\Controllers;

use App\Modules\Models\ModuleCapability;

class ModulesCapabilitiesController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getQuery();
        $query = ModuleCapability::get();

        if (isset($params['module'])) {
            $query->where('smc_sm_id = :module:', array('module' => $params['module']));
        }
        
        return $query->paginate();
    }

    public function createAction() {
        $data = $this->request->getJson();
        $caps = new ModuleCapability();
        $caps->save($data);

        return array(
            'success' => TRUE,
            'data' => $caps->toArray()
        );
    }

    public function updateAction($id) {
        $query = ModuleCapability::get($id);
        if ($query->data) {
            $data = $this->request->getJson();
            $query->data->save($data);
        }
        return $query;
    }

    public function deleteAction($id) {
        $query = ModuleCapability::get($id);
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