<?php
namespace App\Tasks\Models;

class TaskComment extends \Micro\Model {


    public function getSource() {
        return 'trx_tasks_comments';
    }

}