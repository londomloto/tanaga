<?php
namespace App\Modules\Controllers;

use App\Modules\Models\Module,
    App\Modules\Models\Capability;

class ModulesController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getQuery();
        $result = Module::get()->orderBy('sm_title ASC')->paginate();

        if (isset($params['capability'])) {
            $result->map(function($item){
                $data = $item->toArray();
                $data['sm_capabilities'] = $item->capabilities->toArray();
                return $data;
            });
        }

        return $result;
    }

    public function findByIdAction($id) {
        return Module::get($id);
    }

    public function createAction() {
        $data = $this->request->getJson();
        $module = new Module();

        if ($module->save($data)) {
            if (isset($data['sm_capabilities'])) {
                $module->saveCapabilities($data['sm_capabilities']);
            }

            return Module::get($module->sm_id);
        }

        return Module::none();
    }

    public function updateAction($id) {
        $query = Module::get($id);

        if ($query->data) {
            $post = $this->request->getJson();
            if ($query->data->save($post)) {
                if (isset($post['sm_capabilities'])) {
                    $query->data->saveCapabilities($post['sm_capabilities']);
                }
            }
        }
        
        return $query;
    }

    public function deleteAction($id) {
        $query = Module::get($id);
        $success = FALSE;

        if ($query->data) {
            $success = $query->data->delete();
        }
        
        return array(
            'success' => $success
        );
    }
}