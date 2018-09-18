<?php
namespace App\Projects\Models;

class ProjectUser extends \Micro\Model {

    public function initialize() {
        $this->belongsTo(
            'spu_su_id',
            'App\Users\Models\User',
            'su_id',
            array(
                'alias' => 'User'
            )
        );

        $this->belongsTo(
            'spu_sp_id',
            'App\Projects\Models\Project',
            'sp_id',
            array(
                'alias' => 'Project'
            )
        );
    }

    public function getSource() {
        return 'sys_projects_users';
    }

}