<?php
namespace App\Roles\Controllers;

use App\Roles\Models\Role,
    App\Roles\Models\RoleKanban,
    App\Kanban\Models\KanbanSetting;

class RolesKanbanController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getParams();
        $display = isset($params['display']) ? $params['display'] : NULL;

        switch($display) {
            case 'setup':

                $query = KanbanSetting::get()
                    ->alias('a')
                    ->columns("
                        b.srk_id,
                        a.ks_id AS srk_ks_id,
                        a.ks_name AS srk_ks_name,
                        a.ks_description As srk_ks_description,
                        COALESCE(b.srk_selected, 0) AS srk_selected
                    ");

                $role = (isset($params['role']) && ! empty($params['role'])) ? $params['role'] : -1;
                $query->join('App\Roles\Models\RoleKanban', 'a.ks_id = b.srk_ks_id AND b.srk_sr_id = '.$role, 'b', 'left');
                
                $data = $query->execute();

                return array(
                    'success' => TRUE,
                    'data' => $data
                );

            default:
                return RoleKanban::get()->paginate();
        }
        
    }

}