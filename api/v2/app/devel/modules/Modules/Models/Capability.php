<?php
namespace App\Modules\Models;

class Capability extends \Micro\Model {

    public function initialize() {
        $this->hasMany(
            'smc_id',
            'App\Roles\Models\RolePermission',
            'srp_smc_id',
            array(
                'alias' => 'Permissions',
                'foreignKey' => array(
                    'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
                )
            )
        );
    }

    public function getSource() {
        return 'sys_modules_capabilities';
    }

}