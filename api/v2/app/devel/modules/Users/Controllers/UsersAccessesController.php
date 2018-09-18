<?php
namespace App\Users\Controllers;

class UsersAccessesController extends \Micro\Controller {

    public function findAction() {
        return array(
            'success' => TRUE,
            'data' => $this->role->accesses()
        );
    }

}