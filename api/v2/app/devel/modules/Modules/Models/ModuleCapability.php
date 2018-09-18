<?php
namespace App\Modules\Models;

class ModuleCapability extends \Micro\Model {

    public function initialize() {
        $this->belongsTo(
            'smc_sm_id',
            'App\Modules\Models\Module',
            'sm_id',
            array(
                'alias' => 'Module'
            )
        );
    }

    public function getSource() {
        return 'sys_modules_capabilities';
    }

    public function getNamespace() {
        $name = $this->smc_name;

        if ($this->module) {
            $name .= '@'.$this->module->sm_name;
        }

        return $name;
    }

}