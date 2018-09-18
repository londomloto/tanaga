<?php
namespace App\Users\Controllers;

use App\Modules\Models\Module,
    App\Users\Models\User,
    App\Users\Models\UserPermission,
    App\Modules\Models\ModuleCapability;

class UsersPermissionsController extends \Micro\Controller {

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
                
                if (isset($params['user']) && ! empty($params['user'])) {
                    $user = User::findFirst($params['user']);

                    if ($user) {
                        if ($user->userMenus->count() == 0) {
                            $role = $user->role;
                            if ($role) {
                                foreach($role->roleMenus as $rm) {
                                    if ($rm->srm_selected) {
                                        $menuSelection[$rm->srm_smn_id] = TRUE;
                                    }
                                }
                            }
                        } else {
                            foreach($user->userMenus as $um) {
                                if ($um->sum_selected) {
                                    $menuSelection[$um->sum_smn_id] = TRUE;    
                                }
                            }    
                        }

                        if ($user->userPermissions->count() == 0) {
                            $role = $user->role;
                            if ($role) {
                                foreach($role->rolePermissions as $rp) {
                                    if ($rp->srp_selected) {
                                        $permSelection[$rp->srp_smc_id] = TRUE;
                                    }
                                }
                            }
                        } else {
                            foreach($user->userPermissions as $up) {
                                if ($up->sup_selected) {
                                    $permSelection[$up->sup_smc_id] = TRUE;
                                }
                            }
                        }
                    }
                }

                foreach(Module::find() as $module) {
                    if ($module->menus->count() == 0 && $module->capabilities->count() == 0) {
                        continue;
                    }

                    $item = array(
                        'module' => $module->toArray()
                    );

                    $menus = $module->getMenus(array('limit' => 3))->filter(function($menu) use ($menuSelection, $combo) {
                        $array = $menu->toArray();
                        $array['smn_title_path'] = $menu->getTitlePath();

                        $array['smn_selected_1'] = '0';
                        $array['smn_selected_2'] = '0';

                        if (isset($menuSelection[$menu->smn_id])) {
                            $array['smn_selected_'.$combo] = '1';
                        }

                        return $array;
                    });

                    $item['menu'] = isset($menus[0]) ? $menus[0] : NULL;
                    // $item['menus'] = $menus;

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

            case 'authorized':
                return array(
                    'success' => TRUE,
                    'data' => $this->role->permissions()
                );

            default:
                return UserPermission::get()->paginate();
        }
        
    }

    public function createAction() {
        $post = $this->request->getJson();

        foreach($post as $id => $data) {
            $user = User::findFirst($id);
            if ($user) {
                $user->saveMenus($data['menus']);
                $user->savePermissions($data['permissions']);
            }
        }

        return array(
            'success' => TRUE
        );
    }
}