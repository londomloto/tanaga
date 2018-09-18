<?php
namespace App\Roles\Models;

use Micro\Helpers\Text,
    App\Roles\Models\RoleKanban,
    App\Roles\Models\RolePermission,
    App\Roles\Models\RoleAccess,
    Phalcon\Mvc\Model\Relation;

class Role extends \Micro\Model {

    public function initialize() {
        $this->hasMany(
            'sr_id',
            'App\Roles\Models\RoleKanban',
            'srk_sr_id',
            array(
                'alias' => 'Kanban',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasMany(
            'sr_id',
            'App\Bpmn\Models\FormRole',
            'bfr_sr_id',
            array(
                'alias' => 'FormRoles',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasMany(
            'sr_id',
            'App\Roles\Models\RolePermission',
            'srp_sr_id',
            array(
                'alias' => 'RolePermissions',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasManyToMany(
            'sr_id',
            'App\Roles\Models\RolePermission',
            'srp_sr_id',
            'srp_smc_id',
            'App\Modules\Models\ModuleCapability',
            'smc_id',
            array(
                'alias' => 'Permissions'
            )
        );

        $this->hasMany(
            'sr_id',
            'App\Roles\Models\RoleMenu',
            'srm_sr_id',
            array(
                'alias' => 'RoleMenus'
            )
        );

        $this->hasManyToMany(
            'sr_id',
            'App\Roles\Models\RoleMenu',
            'srm_sr_id',
            'srm_smn_id',
            'App\Menus\Models\Menu',
            'smn_id',
            array(
                'alias' => 'Menus'
            )
        );
    }

    public function getSource() {
        return 'sys_roles';
    }

    public function getMenus($options = array()) {
        $conditions = '';

        if (isset($options[0]) && is_string($options[0])) {
            $conditions = $options[0];
            unset($options[0]);
        } else if (isset($options['conditions'])) {
            $conditions = $options['conditions'];
            unset($options['conditions']);
        }

        if ( ! empty($conditions)) {
            $conditions .= ' AND ';
        }

        $conditions .= 'App\Roles\Models\RoleMenu.srm_selected = 1';
        $options['conditions'] = $conditions;

        return parent::getMenus($options);
    }

    public function getPermissions($options = array()) {
        $perms = array();

        foreach($this->getRolePermissions($options) as $prof) {
            if (($cap = $prof->capability) && ($mod = $cap->module)) {
                $perms[] = array(
                    'authorized' => $prof->srp_selected ? TRUE : FALSE,
                    'permission' => strtolower($cap->smc_name).'@'.$mod->sm_name,
                    'capability' => $cap->smc_name,
                    'module_name' => $mod->sm_name,
                    'module_path' => $mod->sm_api
                );
            }
        }

        return $perms;
    }

    public function getAccesses() { 
        $acceses = array();
        foreach($this->getRoleMenus() as $prof) {
            if (($mod = $prof->module)) {
                $acceses[] = array(
                    'authorized' => $prof->srm_selected == 1 ? TRUE : FALSE,
                    'permission' => 'access@'.$mod->sm_name,
                    'capability' => 'access',
                    'module_name' => $mod->sm_name,
                    'module_path' => $mod->sm_api
                );
            }
        }
        return $acceses;
    }

    public function beforeSave() {
        $slug = Text::slug($this->sr_name);
        $this->sr_slug = $slug;
    }

    public function saveKanban($items) {
        if (count($items) === 0) {
            // reset
            $this->kanban->delete();
            return;
        }

        $create = array();
        $update = array();
        $delete = array();

        $exists = array_map(function($item){ return $item['srk_id']; }, $this->kanban->toArray());
        $sliced = array();

        foreach($items as $item) {
            if (empty($item['srk_id'])) {
                if ($item['srk_selected']) {
                    $create[] = $item;    
                }
            } else {
                if (in_array($item['srk_id'], $exists)) {
                    if ($item['srk_selected']) {
                        $update[] = $item;
                        $sliced[] = $item['srk_id'];    
                    }
                }
            }
        }

        for ($i = count($exists) - 1; $i >= 0; $i--) {
            if ( ! in_array($exists[$i], $sliced)) {
                $delete[] = $exists[$i];
            }
        }

        if (count($delete) > 0) {
            $deleted = RoleKanban::find('srk_id IN ('.implode(',', $delete).')');
            foreach($deleted as $item) {
                $item->srk_selected = 0;
                $item->save();
            }
        }

        if (count($update) > 0) {
            foreach($update as $item) {
                $k = RoleKanban::findFirst($item['srk_id']);
                $k->save($item);
            }
        }

        if (count($create) > 0) {
            foreach($create as $item) {
                $k = new RoleKanban();
                $item['srk_sr_id'] = $this->sr_id;
                $k->save($item);
            }
        }
    }

    public function saveMenus($items) {
        $create = array();
        $update = array();
        $exists = array();

        foreach($this->roleMenus as $rm) {
            $token = $rm->getToken();
            $exists[$token] = $rm->srm_id;
        }

        foreach($items as $item) {
            list($menu, $module, $selected) = explode(':', $item);

            $menu = empty($menu) ? NULL : $menu;
            $token = sprintf('%d:%d', $menu, $module);

            if ( ! isset($exists[$token])) {
                $create[] = array(
                    'menu' => $menu,
                    'module' => $module,
                    'selected' => $selected
                );
            } else {
                $update[] = array(
                    'id' => $exists[$token],
                    'menu' => $menu,
                    'module' => $module,
                    'selected' => $selected
                );

                unset($exists[$token]);
            }
        
        }

        foreach($exists as $token => $id) {
            $rm = RoleMenu::findFirst($id);
            if ($rm) {
                $rm->delete();
            }
        }

        foreach($update as $elem) {
            $rm = RoleMenu::findFirst($elem['id']);

            if ($rm) {
                $rm->srm_smn_id = $elem['menu'];
                $rm->srm_sm_id = $elem['module'];
                $rm->srm_selected = $elem['selected'];
                $rm->save();
            }
        }

        foreach($create as $elem) {
            $rm = new RoleMenu();
            
            $rm->srm_sr_id = $this->sr_id;
            $rm->srm_sm_id = $elem['module'];
            $rm->srm_smn_id = $elem['menu'];
            $rm->srm_selected = $elem['selected'];

            $rm->save();
        }
    }

    public function savePermissions($items) {
        $create = array();
        $update = array();
        $exists = array();

        foreach($this->rolePermissions as $rp) {
            $exists[$rp->srp_smc_id] = $rp;
        }

        foreach($items as $cap) {
            if ( ! isset($exists[$cap])) {
                $create[] = $cap;
            } else {
                $update[] = $cap;
                unset($exists[$cap]);
            }
        }

        foreach($exists as $cap => $rp) {
            $rp->srp_selected = 0;
            $rp->save();
        }

        foreach($update as $cap) {
            $rp = RolePermission::findFirst(array(
                'srp_sr_id = :role: AND srp_smc_id = :capability:',
                'bind' => array(
                    'role' => $this->sr_id,
                    'capability' => $cap
                )
            ));

            if ($rp) {
                $rp->srp_selected = 1;
                $rp->save();
            }
        }

        foreach($create as $cap) {
            $rp = new RolePermission();
            $rp->srp_sr_id = $this->sr_id;
            $rp->srp_smc_id = $cap;
            $rp->srp_selected = 1;
            $rp->save();
        }
    }

    public static function defaultRole() {
        return self::findFirst('sr_default = 1');
    }
}