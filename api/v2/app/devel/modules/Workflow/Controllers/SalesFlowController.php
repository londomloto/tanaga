<?php
namespace App\Workflow\Controllers;

use App\Workflow\Models\SalesFlow,
    App\Workflow\Models\SalesFlowStatus;

class SalesFlowController extends \Micro\Controller {
    
    public function findAction() {
        $params = $this->request->getQuery();
        $statuses = isset($params['statuses']) ? json_decode($params['statuses']) : array();

        if ( ! empty($statuses)) {
            $data = SalesFlowStatus::get()
                ->filter(function($row){
                    $trans = $row->trans;
                    $status  = $row->status;

                    $data = array();
                    $data['id'] = NULL;
                    $data['text'] = NULL;
                    $data['status'] = $row->id;
                    $data['status_text'] = $status ? $status->label : NULL;
                    $data['status_date'] = $row->created;
                    $data['current'] = $status ? $status->source_id : NULL;
                    $data['target'] = $row->target;
                    $data['worker'] = $row->worker;
                })
                ->execute();

            return array(
                'success' => TRUE,
                'data' => $data
            );
        }
    }

    public function findByIdAction($id) {

    }

    public function createAction() {

    }

    public function updateAction($id) {

    }

    public function deleteAction($id) {

    }

}