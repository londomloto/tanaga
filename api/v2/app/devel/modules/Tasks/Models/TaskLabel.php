<?php
namespace App\Tasks\Models;

class TaskLabel extends \Micro\Model {

    public function getSource() {
        return 'trx_tasks_labels';
    }

}