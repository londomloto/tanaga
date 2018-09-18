<?php
namespace App\Roles\Controllers;

use App\Roles\Models\Role,
    App\Roles\Models\RolePanel,
    App\Projects\Models\Project;

class RolesPanelsController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getParams();
        $display = isset($params['display']) ? $params['display'] : NULL;

        $role = Role::findFirst($params['role']);
        $project = Project::findFirst($params['project']);

        switch($display) {
            case 'setup':
            
                return RolePanel::querySetup($project, $role);
                
            case 'granted':
                
                return RolePanel::queryGranted($project, $role);

            default:
                
                return RolePanel::get()->paginate();
        }
    }

    public function saveAction() {
        $post = $this->request->getJson();
        $role = Role::findFirst($post['role']);
        $project = Project::findFirst($post['project']);

        foreach($post['panels'] as $panel) {
            if ($panel['panel_display'] == 'show') {
                RolePanel::find(array(
                    'srs_sp_id = :project: AND srs_sr_id = :role: AND srs_kp_id = :panel:',
                    'bind' => array(
                        'project' => $project->sp_id,
                        'role' => $role->sr_id,
                        'panel' => $panel['panel_id']
                    )
                ))->delete();
            } else {
                $created = array();
                $updated = array();
                $deleted = array();

                $found = RolePanel::find(array(
                    'srs_sp_id = :project: AND srs_sr_id = :role: AND srs_kp_id = :panel:',
                    'bind' => array(
                        'project' => $project->sp_id,
                        'role' => $role->sr_id,
                        'panel' => $panel['panel_id']
                    )
                ))->toArray();

                $saved = array();

                foreach($found as $item) {
                    $key = $item['srs_id'];
                    $saved[$key] = array(
                        'checked' => $item['srs_checked']
                    );
                }

                foreach($panel['panel_statuses'] as &$item) {

                    if ($panel['panel_display'] == 'hide') {
                        $item['srs_checked'] = 0;
                    }

                    if (is_null($item['srs_id'])) {
                        $created[] = $item;
                    } else {
                        if (isset($saved[$item['srs_id']])) {
                            // $item['srs_checked'] = $saved[$item['srs_id']]['checked'];
                            $updated[] = $item;
                            unset($saved[$item['srs_id']]);
                        }
                    }
                }

                foreach($created as $data) {
                    $item = new RolePanel();
                    $item->save($data);
                }

                foreach($updated as $data) {
                    $item = RolePanel::findFirst($data['srs_id']);
                    if ($item) {
                        $item->save($data);
                    }
                }
            }
        }
        
        return array(
            'success' => TRUE
        );
    }

}