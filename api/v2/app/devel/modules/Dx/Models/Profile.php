<?php
namespace App\Dx\Models;

class Profile extends \Micro\Model {

    public function initialize() {

        $this->hasMany(
            'profile_id',
            'App\Dx\Models\Mapping',
            'map_profile_id',
            array(
                'alias' => 'Mapping',
                'foreignKey' => array(
                    'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
                )
            )
        );

    }

    public function getSource() {
        return 'dx_profiles';
    }
    
}