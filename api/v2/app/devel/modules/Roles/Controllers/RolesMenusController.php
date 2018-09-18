<?php
namespace App\Roles\Controllers;

use App\Menus\Models\Menu;

class RolesMenusController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getQuery();

        if (isset($params['setup'])) {
            $query = Menu::get()
                ->columns("
                    a.srm_id,
                    smn_id as srm_smn_id,
                    smn_title as srm_smn_title,
                    smn_icon as srm_smn_icon
                ");

            $role = (isset($params['role']) && ! empty($params['role'])) ? $params['role'] : -1;

            $query->join('App\Roles\Models\RoleMenu', 'smn_id = a.srm_smn_id AND a.srm_sr_id = '.$role, 'a', 'left');

            $data = $query->execute();

            return array(
                'success' => TRUE,
                'data' => $data->toArray()
            );
        }
    }

}