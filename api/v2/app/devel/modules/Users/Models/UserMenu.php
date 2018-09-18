<?php
namespace App\Users\Models;

class UserMenu extends \Micro\Model {

    public function getSource() {
        return 'sys_users_menus';
    }

}