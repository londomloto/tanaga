<?php
namespace App\Roles\Models;

class RolePermission extends \Micro\Model {

    public function initialize() {
        $this->belongsTo(
            'srp_smc_id',
            'App\Modules\Models\ModuleCapability',
            'smc_id',
            array(
                'alias' => 'Capability'
            )
        );
    }

    public function getSource() {
        return 'sys_roles_permissions';
    }

}