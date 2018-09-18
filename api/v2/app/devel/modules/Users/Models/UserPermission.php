<?php
namespace App\Users\Models;

class UserPermission extends \Micro\Model {

    public function getSource() {
        return 'sys_users_permissions';
    }

}