<?php
namespace App\Kanban\Controllers;

use App\Kanban\Models\KanbanForm;

class KanbanFormsController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getQuery();
        $statuses = array();

        if (isset($params['worker']) && ! empty($params['worker'])) {
            $worker = $this->bpmn->worker($params['worker']);
            if ($worker) {
                $query = $worker->statuses();
                foreach($query['data'] as $item) {
                    
                    $status = array(
                        'kf_id' => '',
                        'kf_diagrams_id' => $worker->id(),
                        'kf_worker' => $worker->name(),
                        'kf_status' => $item['id'],
                        'kf_status_name' => $item['name'],
                        'kf_status_text' => $item['text'],
                        'kf_form_edit' => '',
                        'kf_form_view' => ''
                    );

                    $record = KanbanForm::findFirst(array(
                        'kf_status = :status:',
                        'bind' => array(
                            'status' => $item['id']
                        )
                    ));

                    if ($record) {
                        $status['kf_id'] = $record->kf_id;
                        $status['kf_form_edit'] = $record->kf_form_edit;
                        $status['kf_form_view'] = $record->kf_form_view;
                    }

                    $statuses[] = $status;
                }
            }
        }

        return array(
            'success' => TRUE,
            'data' => $statuses
        );  
    }

    public function findByIdAction($id) {
        $form = KanbanForm::get($id);

        $success = FALSE;
        $data = NULL;

        if ($form->data) {
            $success = TRUE;
            $data = $form->data->toArray();

            $content = '';

            if ($form->data->kf_form_edit) {
                $path = APPPATH.'public/resources/forms/'.$form->data->kf_form_edit;
                if (file_exists($path)) {
                    ob_start();
                    include($path);
                    $content = ob_get_contents();
                    ob_end_clean();
                }
                $data['kf_form_edit_html'] = $content;
            }

            $content = '';

            if ($form->data->kf_form_view) {
                $path = APPPATH.'public/resources/forms/'.$form->data->kf_form_view;
                if (file_exists($path)) {
                    ob_start();
                    include($path);
                    $content = ob_get_contents();
                    ob_end_clean();
                }
                $data['kf_form_view_html'] = $content;
            }
        }

        return array(
            'success' => $success,
            'data' => $data
        );
    }

    public function updateAction($id) {
        $form = KanbanForm::get($id);
        $post = $this->request->getJson();

        if ($form->data) {
            $form->data->save($post);
        }

        return $form;
    }

}