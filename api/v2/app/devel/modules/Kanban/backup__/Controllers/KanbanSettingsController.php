<?php
namespace App\Kanban\Controllers;

use App\Kanban\Models\KanbanSetting,
    App\Kanban\Models\KanbanPanel;

class KanbanSettingsController extends \Micro\Controller {

    public function findAction() {
        $result = KanbanSetting::get()->paginate();
        return $result;
    }

    public function findByIdAction($id) {
        $result = KanbanSetting::get($id);

        if ($result->data) {
            $record = $result->data;
            
            $result->data = $record->toArray();
            
            $panels = $record->getPanels(array('order' => 'kp_order ASC'))->filter(function($p){
                $panel = $p->toArray();
                $panel['kp_statuses'] = $p->statuses->filter(function($s){
                    return $s->toArray();
                });
                
                $panel['kp_data'] = array();
                $panel['kp_busy'] = FALSE;

                return $panel;
            });

            $result->data['ks_panels'] = $panels;

        }
        return $result;   
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new KanbanSetting();

        if ($data->save($post)) {
            if (isset($post['ks_panels'])) {
                $data->savePanels($post['ks_panels']);
            }

            return KanbanSetting::get($data->ks_id);
        }

        return KanbanSetting::none();
    }

    public function updateAction($id) {
        $result = KanbanSetting::get($id);

        if ($result->data) {
            $post = $this->request->getJson();
            $result->data->save($post);

            if (isset($post['ks_panels'])) {
                $result->data->savePanels($post['ks_panels']);
            }
        }

        return $result;
    }

    public function deleteAction($id) {
        $result = KanbanSetting::get($id);
        $success = FALSE;

        if ($result->data) {
            $success = $result->data->delete();
        }

        return array(
            'success' => $success
        );
    }
}