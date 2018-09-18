<?php
namespace App\Modules\Models;

use App\Modules\Models\ModuleCapability;
use Micro\Helpers\Text;
use Phalcon\Mvc\Model\Relation;

class Module extends \Micro\Model {

    public function initialize() {

        $this->hasMany(
            'sm_id',
            'App\Modules\Models\ModuleCapability',
            'smc_sm_id',
            array(
                'alias' => 'Capabilities',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasMany(
            'sm_id',
            'App\Roles\Models\RoleMenu',
            'srm_sm_id',
            array(
                'alias' => 'RoleAccesses',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasMany(
            'sm_api',
            'App\Menus\Models\Menu',
            'smn_path',
            array(
                'alias' => 'Menus'
            )
        );
    }

    public function getSource() {
        return 'sys_modules';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        $abbr = empty($data['sm_title']) ? $data['sm_name'] : $data['sm_title'];
        $abbr = Text::initial($abbr);
        $data['sm_abbr'] = $abbr;
        return $data;
    }

    public function saveCapabilities($items) {
        $create = array();
        $update = array();
        $delete = array();

        $exists = array_map(function($item){ return $item['smc_id']; }, $this->capabilities->toArray());
        $sliced = array();

        foreach($items as $item) {
            if (empty($item['smc_id'])) {
                $create[] = $item;
            } else {
                if (in_array($item['smc_id'], $exists)) {
                    $update[] = $item;
                    $sliced[] = $item['smc_id'];    
                }
            }
        }

        for ($i = count($exists) - 1; $i >= 0; $i--) {
            if ( ! in_array($exists[$i], $sliced)) {
                $delete[] = $exists[$i];
            }
        }

        if (count($delete) > 0) {
            ModuleCapability::find('smc_id IN ('.implode(',', $delete).')')->delete();
        }

        if (count($update) > 0) {
            foreach($update as $item) {
                $c = ModuleCapability::findFirst($item['smc_id']);
                $c->save($item);
            }
        }

        if (count($create) > 0) {
            foreach($create as $item) {
                $c = new ModuleCapability();
                $item['smc_sm_id'] = $this->sm_id;
                $c->save($item);
            }
        }
    }

}