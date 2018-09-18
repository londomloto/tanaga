<?php
namespace App\Roles\Controllers;

use App\Modules\Models\Module,
    App\Roles\Models\Role,
    App\Roles\Models\RolePermission,
    App\Modules\Models\ModuleCapability;

class RolesPermissionsController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getParams();
        $display = isset($params['display']) ? $params['display'] : NULL;

        switch($display) {
            case 'setup':

                $result = array(
                    'success' => TRUE,
                    'data' => array()
                );

                $menuSelection = array();
                $permSelection = array();

                $combo = isset($params['combo']) ? $params['combo'] : 1;

                if (isset($params['role']) && ! empty($params['role'])) {
                    $role = Role::findFirst($params['role']);

                    if ($role) {
                        foreach($role->roleMenus as $rm) {
                            if ($rm->srm_selected) {
                                $token = $rm->getToken();
                                $menuSelection[$token] = TRUE;
                            }
                        }

                        foreach($role->rolePermissions as $rp) {
                            if ($rp->srp_selected) {
                                $permSelection[$rp->srp_smc_id] = TRUE;
                            }
                        }
                    }
                }

                $modules = Module::find(array('order' => 'sm_title ASC'));

                foreach($modules as $module) {
                    
                    $item = array(
                        'module' => $module->toArray()
                    );

                    $menus = $module->getMenus(array('limit' => 1))->filter(function($menu) use ($menuSelection, $combo) {
                        $array = $menu->toArray();
                        $array['smn_title_path'] = $menu->getTitlePath();

                        return $array;
                    });

                    $item['menu'] = isset($menus[0]) ? $menus[0] : FALSE;
                    $item['menu_token'] = sprintf('%d:%d', $item['menu'] ? $item['menu']['smn_id'] : 0, $module->sm_id);

                    $item['menu_selected_1'] = '0';
                    $item['menu_selected_2'] = '0';

                    if (isset($menuSelection[$item['menu_token']])) {
                        $item['menu_selected_'.$combo] = '1';
                    }

                    $item['capabilities'] = $module->capabilities->filter(function($cap) use ($permSelection, $combo) {
                        $array = $cap->toArray();
                        $array['smc_selected_1'] = '0';
                        $array['smc_selected_2'] = '0';

                        if (isset($permSelection[$cap->smc_id])) {
                            $array['smc_selected_'.$combo] = '1';
                        }

                        return $array;
                    });

                    $result['data'][] = $item;
                }
                return $result;

            default:
                return RolePermission::get()->paginate();
        }
        
    }

    public function createAction() {
        $post = $this->request->getJson();

        foreach($post as $id => $data) {
            $role = Role::findFirst($id);
            if ($role) {
                $role->saveMenus($data['menus']);
                $role->savePermissions($data['permissions']);
            }
        }

        return array(
            'success' => TRUE
        );
    }
}