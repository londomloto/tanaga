<?php
namespace App\Users\Controllers;

use App\Users\Models\UserKanban,
    App\Users\Models\User,
    App\Roles\Models\Role,
    App\Roles\Models\RoleKanban,
    App\Kanban\Models\KanbanSetting;

class UsersKanbanController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getParams();
        $display = isset($params['display']) ? $params['display'] : NULL;

        switch($display) {
            case 'setup':

                $user = (isset($params['user']) && ! empty($params['user'])) ? $params['user'] : -1;

                $query = KanbanSetting::get()
                    ->alias('a')
                    ->columns("
                        b.suk_id,
                        a.ks_id AS suk_ks_id,
                        a.ks_name As suk_ks_name,
                        a.ks_description As suk_ks_description,
                        COALESCE(b.suk_selected, 0) as suk_selected,
                        'user' AS suk_source
                    ");

                $query->join('App\Users\Models\UserKanban', 'b.suk_ks_id = a.ks_id AND b.suk_su_id = ' . $user, 'b', 'left');

                $kanban = $query->execute()->toArray();
                $exists = UserKanban::get()->where('suk_su_id = :user:', array('user' => $user))->execute();

                if (count($exists) == 0) {
                    if (isset($params['role'])) {
                        $role = Role::get($params['role'])->data;
                        if ($role) {
                            $select = array_map(function($k){ return $k['srk_ks_id']; }, $role->kanban->toArray());
                            if (count($select) > 0) {
                                foreach($kanban as &$k) {
                                    $k['suk_source'] = 'role';
                                    if (in_array($k['suk_ks_id'], $select)) {
                                        $k['suk_selected'] = 1;
                                    }
                                }
                            }
                        }
                    }
                }

                return array(
                    'success' => TRUE,
                    'data' => $kanban
                );

            default:

                $data = array();

                if (isset($params['user']) && ! empty($params['user'])) {
                    $data = UserKanban::get()
                        ->alias('a')
                        ->columns(array(
                            'b.ks_id AS ks_id',
                            'b.ks_name AS ks_name',
                            'b.ks_description AS ks_description'
                        ))
                        ->join('App\Kanban\Models\KanbanSetting', 'a.suk_ks_id = b.ks_id', 'b', 'left')
                        ->where('a.suk_selected = 1 AND a.suk_su_id = :user:', array('user' => $params['user']))
                        ->execute()
                        ->toArray();

                    if (count($data) === 0) {
                        // try grab from role
                        $user = User::findFirst($params['user']);
                        if ($user && ($role = $user->role)) {
                            $data = RoleKanban::get()
                                ->alias('a')
                                ->columns(array(
                                    'b.ks_id AS ks_id',
                                    'b.ks_name AS ks_name',
                                    'b.ks_description AS ks_description'
                                ))
                                ->join('App\Kanban\Models\KanbanSetting', 'a.srk_ks_id = b.ks_id', 'b', 'left')
                                ->where('a.srk_selected = 1 AND a.srk_sr_id = :role:', array('role' => $role->sr_id))
                                ->execute()
                                ->toArray();
                        }
                    }

                }

                return array(
                    'success' => TRUE,
                    'data' => $data
                );

        }
    }

}