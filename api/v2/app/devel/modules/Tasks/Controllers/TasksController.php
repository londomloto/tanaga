<?php
namespace App\Tasks\Controllers;

use App\Tasks\Models\Task;

class TasksController extends \Micro\Controller {

    public function findAction() {
        return Task::get()->paginate();
    }

    public function startAction($data) {
        // logic here...
        $this->next();
    }

    public function todoAction($data) {
        // logic here...
        $this->next();
    }

    public function doingAction($data) {
        // logic here...
        $this->next();
    }

    public function doneAction() {
        // logic here...
        $this->next();
    }

}

