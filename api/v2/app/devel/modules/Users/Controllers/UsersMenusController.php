<?php
namespace App\Users\Controllers;

class UsersMenusController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getParams();
        $display = isset($params['display']) ? $params['display'] : NULL;

        switch($display) {
            case 'setup':
                break;

            case 'authorized':
                return array(
                    'success' => TRUE,
                    'data' => $this->role->menus()
                );
        }
    }

}