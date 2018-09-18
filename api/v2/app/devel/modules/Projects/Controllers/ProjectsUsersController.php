<?php
namespace App\Projects\Controllers;

use App\Projects\Models\ProjectUser,
    App\Users\Models\User;

class ProjectsUsersController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getParams();

        $query = ProjectUser::get()
            ->join('App\Users\Models\User', 'spu_su_id = b.su_id', 'b');

        if (isset($params['project'])) {
            $query->andWhere('spu_sp_id = :project:', array('project' => $params['project']));
        }

        $result = $query->sortable()->paginate();

        $result->data = $result->data->filter(function($rec){
            if ($rec->user) {
                $data = $rec->user->toArray();
                $data['spu_id'] = $rec->spu_id;
                return $data;
            }
        });

        return $result;
    }

    public function createAction() {
        $post = $this->request->getJson();

        // check exists
        $found = ProjectUser::findFirst(array(
            'spu_su_id = :user: AND spu_sp_id = :project:',
            'bind' => array(
                'user' => $post['spu_su_id'],
                'project' => $post['spu_sp_id']
            )
        ));

        if ($found) {
            return array(
                'success' => FALSE,
                'message' => 'User already registered'
            );
        }

        $data = new ProjectUser();

        if ($data->save($post)) {
            $query = ProjectUser::get($data->spu_id);
            if ($query->data) {
                $user = $query->data->user->toArray();
                $user['spu_id'] = $query->data->spu_id;

                $query->data = $user;
            }
            return $query;
        }

        return ProjectUser::none();
    }

    public function deleteAction($id) {
        $data = ProjectUser::get($id)->data;
        
        if ($data) {
            $data->delete();
        }

        return array('success' => TRUE);
    }
}