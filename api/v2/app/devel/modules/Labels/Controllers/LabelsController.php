<?php
namespace App\Labels\Controllers;

use App\Labels\Models\Label;

class LabelsController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getParams();
        $display = isset($params['display']) ? $params['display'] : FALSE;

        $query = Label::get()->filterable();

        if ($display) {
            $query->andWhere(
                '(sl_sp_id = :project: OR sl_sp_id IS NULL )',
                array(
                    'project' => $params['project']
                )
            );
        }

        if ( ! isset($params['sort'])) {
            $query->orderBy('sl_label ASC');
        } else {
            $query->sortable();
        }

        return $query->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $post['sl_sp_id'] = isset($post['sl_sp_id']) && $post['sl_sp_id'] != '' ? $post['sl_sp_id'] : NULL;

        if ( ! empty($post['sl_sp_id'])) {
            $found = Label::findFirst(array(
                'UPPER(sl_label) = :label: AND sl_sp_id = :project:',
                'bind' => array(
                    'label' => strtoupper($post['sl_label']),
                    'project' => $post['sl_sp_id']
                )
            ));    
        } else {
            $found = Label::findFirst(array(
                'UPPER(sl_label) = :label:',
                'bind' => array(
                    'label' => strtoupper($post['sl_label'])
                )
            ));    
        }

        if ($found) {
            return array(
                'success' => FALSE,
                'message' => 'Label name already defined'
            );
        }

        $user = $this->auth->user();

        $post['sl_created_dt'] = date('Y-m-d H:i:s');
        $post['sl_created_by'] = $user['su_id'];
        
        $data = new Label();

        if ($data->save($post)) {
            return Label::get($data->sl_id);
        }

        return Label::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $post['sl_sp_id'] = isset($post['sl_sp_id']) && $post['sl_sp_id'] != '' ? $post['sl_sp_id'] : NULL;

        $found = Label::findFirst(array(
            'sl_label = :label: AND sl_id != :id:',
            'bind' => array(
                'label' => $post['sl_label'],
                'id' => $post['sl_id']
            )
        ));

        if ($found) {
            return array(
                'success' => FALSE,
                'message' => 'Label name already exists'
            );
        }

        $query = Label::get($id);

        if ($query->data) {
            $query->data->save($post);
        }

        return $query;
    }

    public function deleteAction($id) {
        $label = Label::get($id)->data;
        if ($label) {
            $label->delete();
        }

        return array(
            'success' => TRUE
        );
    }
}