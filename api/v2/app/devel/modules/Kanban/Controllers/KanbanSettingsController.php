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
        
        $display = $this->request->getQuery('display');
        $project = $this->request->getQuery('project');

        $user = $this->auth->user();

        if ($result->data) {
            $record = $result->data;
            
            $result->data = $record->toArray();
            
            $panels = $record->getPanels(array('order' => 'kp_order ASC'))->filter(function($p) use($display, $project, $user){
                $panel = $p->toArray();
                $panel['kp_busy'] = FALSE;
                $panel['kp_more'] = FALSE;
                $panel['kp_info'] = '(0 Item)';
                $panel['kp_data'] = array();

                $panel['kp_statuses'] = $p->statuses->filter(function($s){
                    return $s->toArray();
                });
                
                return $panel;

                /*if ($display == 'granted') {
                    $setup = \App\Roles\Models\RolePanel::find(array(
                        'srs_sp_id = :project: AND srs_sr_id = :role: AND srs_kp_id = :panel:',
                        'bind' => array(
                            'project' => $project,
                            'role' => $user['sr_id'],
                            'panel' => $panel['kp_id']
                        )
                    ))->toArray();

                    if (count($setup) > 0) {
                        $display = 'hide';
                        
                        if (count($setup) != count($panel['kp_statuses'])) {
                            $display = 'custom';
                        } else {
                            $revoked = array();
                            $granted = array();

                            foreach($setup as $s) {
                                if ($s['srs_checked'] == 1) {
                                    $granted[$s['srs_kst_id']] = TRUE;
                                } else {
                                    $revoked[$s['srs_kst_id']] = TRUE;
                                }
                            }

                            if (count($granted) == 0) {
                                $display = 'hide';
                            } else {
                                $display = 'custom';
                                
                                foreach($revoked as $key => $bool) {
                                    $index = self::__findIndex($key, $panel['kp_statuses']);
                                    if ($index !== -1) {
                                        array_splice($panel['kp_statuses'], $index, 1);
                                    }
                                }
                            }
                        }

                        return $display == 'hide' ? FALSE : $panel;
                    } else {
                        return $panel;
                    }
                } else {
                    return $panel;    
                }*/
                
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

    private static function __findIndex($needle, $haystack) {
        $count = count($haystack);

        for ($i = 0; $i < $count; $i++) {
            if ($haystack[$i]['kst_id'] == $needle) {
                return $i;
            }
        }

        return -1;
    }
}