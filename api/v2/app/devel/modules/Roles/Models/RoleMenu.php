<?php
namespace App\Roles\Models;

class RoleMenu extends \Micro\Model {

    public function initialize() {
        $this->belongsTo(
            'srm_sm_id',
            'App\Modules\Models\Module',
            'sm_id',
            array(
                'alias' => 'Module'
            )
        );

        $this->belongsTo(
            'srm_smn_id',
            'App\Menus\Models\Menu',
            'smn_id',
            array(
                'alias' => 'Menu'
            )
        );
    }

    public function getSource() {
        return 'sys_roles_menus';
    }

    public function getToken() {
        if (empty($this->srm_sm_id)) {
            if ($this->menu && ($module = $this->menu->module)) {
                return sprintf('%d:%d', $this->srm_smn_id, $module->sm_id);
            }
            return sprintf('%d:%d', $this->srm_smn_id, 0);
        } else {
            return sprintf('%d:%d', $this->srm_smn_id, $this->srm_sm_id);
        }
    }

}