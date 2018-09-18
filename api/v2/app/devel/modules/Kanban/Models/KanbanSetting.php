<?php
namespace App\Kanban\Models;

use App\Kanban\Models\KanbanPanel,
    App\Bpmn\Models\Diagram,
    App\Bpmn\Models\Form;

class KanbanSetting extends \Micro\Model {

    public function initialize() {

        $this->hasMany(
            'ks_id',
            'App\Kanban\Models\KanbanPanel',
            'kp_ks_id',
            array(
                'alias' => 'Panels',
                'foreignKey' => array(
                    'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasMany(
            'ks_id',
            'App\Roles\Models\RoleKanban',
            'srk_ks_id',
            array(
                'alias' => 'KanbanRoles',
                'foreignKey' => array(
                    'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasMany(
            'ks_id',
            'App\Users\Models\UserKanban',
            'suk_ks_id',
            array(
                'alias' => 'KanbanUsers',
                'foreignKey' => array(
                    'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
                )
            )
        );

    }

    public function getSource() {
        return 'kanban_settings';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);

        $URL = $this->getDI()->get('url');
        
        $data['ks_image_url'] = $URL->getBaseUrl().'public/resources/worksheets/'.$data['ks_image'];
        $data['ks_image_thumb'] = $URL->getSiteUrl('assets/thumb').'?s=public/resources/worksheets/'.$data['ks_image'];

        return $data;
    }

    public function savePanels($items) {
        $create = array();
        $update = array();
        $delete = array();

        $size = count($items);

        for ($i = 0; $i < $size; $i++) {
            $items[$i]['kp_order'] = $i;
        }

        $exists = array_map(function($item){ return $item['kp_id']; }, $this->panels->toArray());

        $sliced = array();

        foreach($items as $item) {
            if ( ! isset($item['kp_id'])) {
                $create[] = $item;
            } else {
                if (in_array($item['kp_id'], $exists)) {
                    $update[] = $item;
                    $sliced[] = $item['kp_id'];
                }
            }
        }

        for ($i = count($exists) - 1; $i >= 0; $i--) {
            if ( ! in_array($exists[$i], $sliced)) {
                $delete[] = $exists[$i];
            }
        }

        if (count($delete) > 0) {
            KanbanPanel::find('kp_id IN ('.implode(',', $delete).')')->delete();
        }

        if (count($update) > 0) {
            foreach($update as $item) {
                $panel = KanbanPanel::findFirst($item['kp_id']);
                if ($panel->save($item)) {
                    if (isset($item['kp_statuses'])) {
                        $panel->saveStatuses($item['kp_statuses']);
                    }
                }
            }
        }

        if (count($create) > 0) {
            foreach($create as $item) {
                $panel = new KanbanPanel();
                $item['kp_ks_id'] = $this->ks_id;
                if ($panel->save($item)) {
                    if (isset($item['kp_statuses'])) {
                        $panel->saveStatuses($item['kp_statuses']);
                    }
                }
            }
        }

    }

    public function getWorkers() {
        $diagrams = array();
        $stacks = array();
        $bpmn = \Phalcon\DI::getDefault()->get('bpmn');

        foreach($this->panels as $panel) {
            foreach($panel->statuses as $status) {
                if ( ! in_array($status->kst_diagrams_id, $stacks)) {
                    $stacks[] = $status->kst_diagrams_id;
                }
            }
        }

        $diagrams = Diagram::get()
            ->where('id IN ('. implode(',', $stacks) .')')
            ->execute();

        $workers = array();

        foreach($diagrams as $diagram) {
            $worker = $bpmn->worker($diagram->slug);
            if ($worker) {
                $workers[] = $worker;
            }
        }

        return $workers;
    }

    public function getWorkspaces() {

        $diagrams = array();
        $stacks = array();
        $deploy = array();
        $bpmn = \Phalcon\DI::getDefault()->get('bpmn');

        foreach($this->panels as $panel) {
            foreach($panel->statuses as $status) {
                if ( ! in_array($status->kst_diagrams_id, $stacks)) {
                    $stacks[] = $status->kst_diagrams_id;
                }

                $slot = $status->kst_status;

                if ( ! isset($deploy[$slot])) {
                    $deploy[$slot] = array($panel->kp_id);
                } else {
                    if ( ! in_array($panel->kp_id, $deploy[$slot])) {
                        $deploy[$slot][] = $panel->kp_id;
                    }
                }
            }
        }

        $diagrams = Diagram::get()->inWhere('id', $stacks)->execute();
        $workers = array();

        foreach($diagrams as $diagram) {
            $worker = $bpmn->worker($diagram->slug);
            if ($worker) {
                $workers[] = $worker;
            }
        }

        $workspaces = array();

        foreach($workers as $worker) {
            
            $acts = $worker->activities();
            $group = $worker->name();

            foreach($acts['data'] as $act) {
                $form = Form::findFirst(array(
                    'bf_activity = :act:',
                    'bind' => array(
                        'act' => $act['id']
                    )
                ));

                if ($form) {
                    $data = $form->toArray();

                    $data['bf_init'] = $act['init'];
                    $data['bf_roles'] = array();
                    $data['bf_users'] = array();
                    
                    // grab path
                    $path = APPPATH.'public/resources/forms/'.$form->bf_tpl_file;

                    if (file_exists($path) && ! is_dir($path)) {
                        /*ob_start();
                        include($path);
                        $html = ob_get_contents();
                        ob_end_clean();

                        $data['bf_html'] = $html;*/
                        $data['bf_html'] = '<div></div>';
                    } else {
                        $data['bf_html'] = '<div></div>';
                    }

                    foreach($form->FormsRoles as $prof) {
                        $data['bf_roles'][] = $prof->toArray();
                    }

                    foreach($form->FormsUsers as $prof) {
                        $data['bf_users'][] = $prof->toArray();
                    }

                    if ( ! isset($workspaces[$group])) {
                        $workspaces[$group] = array(
                            'worker' => $group,
                            'deploy' => $deploy,
                            'kanban' => array(
                                'ks_id' => $this->ks_id,
                                'ks_api' => $this->ks_api
                            ),
                            'forms' => array($data)
                        );
                    } else {
                        $workspaces[$group]['forms'][] = $data;
                    }
                } else {
                    if ( ! isset($workspaces[$group])) {
                        $workspaces[$group] = array(
                            'worker' => $group,
                            'deploy' => $deploy,
                            'kanban' => array(
                                'ks_id' => $this->ks_id,
                                'ks_api' => $this->ks_api
                            ),
                            'forms' => array()
                        );
                    }
                }
            }
        }

        return array_values($workspaces);
    }

}