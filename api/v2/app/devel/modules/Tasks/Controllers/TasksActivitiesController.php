<?php
namespace App\Tasks\Controllers;

use App\Tasks\Models\TaskActivity;
use Micro\Helpers\Markdown;

class TasksActivitiesController extends \Micro\Controller {

    public function findAction() {
        $device = $this->request->getQuery('device');
        $result = TaskActivity::get()->filterable()->sortable()->paginate();

        if ($device == 'mobile') {
            $result->map(function($row){
                $data = $row->toArray();
                $data['tta_html'] = Markdown::html($data['tta_verb']);
                return $data;
            });
        }

        return $result;
    }

    public function createAction() {
        $post = $this->request->getJson();
        $text = $post['tta_data'];
        
        if (trim(strip_tags($text)) == '') {
            return array(
                'success' => FALSE
            );
        }

        $type = isset($post['tta_type']) ? $post['tta_type'] : 'comment';

        return TaskActivity::log($type, $post);
    }

    public function updateAction($id) {
        $query = TaskActivity::get($id);
        $post = $this->request->getJson();

        if ($query->data) {
            $query->data->save($post);
        }

        return $query;
    }

    public function uploadAction() {
        $result = array(
            'success' => FALSE,
            'data' => NULL
        );

        if ($this->request->hasFiles()) {
            $request = $this->request->getFiles();
            $file = $request[0];
            $type = $file->getExtension();
            $name = md5('attachment_'.date('ymdhis')).'.'.$type;
            $path = APPPATH.'public/resources/attachments/'.$name;

            if (@$file->moveTo($path)) {
                $code = '';
                if ($this->file->isImage($path)) {
                    /*$code = sprintf(
                        '<p>![%s](%s "%s")</p>',
                        $this->url->getBaseUrl().'public/resources/attachments/'.$name,
                        $this->url->getSiteUrl('/assets/thumb').'?s=public/resources/attachments/'.$name.'&w=100&h=100',
                        $name,
                        $name
                    );*/
                    $code .= sprintf(
                        '[image:%s]',
                        $name
                    );
                } else {
                    /*$code .= sprintf(
                        '<p><iron-icon icon="attachment"></iron-icon>&nbsp;[%s](%s)</p>',
                        $name,
                        $this->url->getBaseUrl().'public/resources/attachments/'.$name
                    );*/
                    $code .= sprintf(
                        '[attachment:%s]',
                        $name
                    );
                }

                $result['success'] = TRUE;
                $result['data'] = array(
                    'code' => $code
                );
            }
        }

        return $result;
    }
    
    public function deleteAction($id) {
        $data = TaskActivity::get($id)->data;
        if ($data) {
            $data->delete();
        }
        return array(
            'success' => TRUE
        );
    }
}