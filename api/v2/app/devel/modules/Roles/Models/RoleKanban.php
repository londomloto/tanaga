<?php
namespace App\Roles\Models;

class RoleKanban extends \Micro\Model {
    
    public function getSource() {
        return 'sys_roles_kanban';
    }
}