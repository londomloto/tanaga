<?php
namespace App\Tasks\Models;

class TaskUser extends \Micro\Model {

    public function getSource() {
        return 'trx_tasks_users';
    }

}