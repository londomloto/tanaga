<?php
namespace App\Roles\Controllers;

use App\Roles\Models\Role;

class RolesController extends \Micro\Controller {

    public function findAction() {
        return Role::get()->filterable()->orderBy('sr_id ASC')->paginate();
    }

    public function findByIdAction($id) {
        return Role::get($id);
    }

    public function createAction() {
        $this->role->validate('manage@roles');
        $post = $this->request->getJson();
        $role = new Role();

        if ($role->save($post)) {
            return Role::get($role->sr_id);
        }

        return Role::none();
    }

    public function updateAction($id) {
        
        $this->role->validate('manage@roles');

        $query = Role::get($id);
        
        if ($query->data) {
            $post = $this->request->getJson();

            if ($post['sr_default'] == 1) {
                foreach(Role::find('sr_id != '.$id) as $model) {
                    $model->sr_default = 0;
                    $model->save();    
                }
            }

            if ($query->data->save($post)) {
                if (isset($post['sr_kanban'])) {
                    $query->data->saveKanban($post['sr_kanban']);
                }

                if (isset($post['sr_permissions'])) {
                    $query->data->savePermissions($post['sr_permissions']);
                }
            }
        }

        return $query;
    }

    public function deleteAction($id) {
        $role = Role::get($id);
        $success = FALSE;
        
        if ($role->data) {
            $success = $role->data->delete();
        }
        
        return array(
            'success' => $success
        );
    }
} 