<?php
namespace App\Bpmn\Controllers;

use App\Bpmn\Models\Form;

class FormsController extends \Micro\Controller {

    public function findAction() {

        $params = $this->request->getQuery();

        if (isset($params['activity'])) {
            $activity = empty($params['activity']) ? -1 : $params['activity']; 

            $data = Form::get()
                ->where('bf_activity = :activity:', array('activity' => $activity))
                ->execute()
                ->filter(function($row) {
                    $item = $row->toArray();
                    
                    $item['bf_roles'] = $row->formsRoles->filter(function($prof){
                        return $prof->toArray();
                    });

                    $item['bf_users'] = $row->formsUsers->filter(function($prof){
                        return $prof->toArray();
                    });

                    return $item;
                });

            return array(
                'success' => TRUE,
                'data' => $data
            );
        } else {
            return Form::get()->paginate();
        }
    }

    public function createAction() {
        $post = $this->request->getJson();
        $form = new Form();

        if (empty($post['bf_description'])) {
            $post['bf_description'] = 'No description';
        }

        if ($form->save($post)) {
            return Form::get($form->bf_id);
        }

        return Form::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $form = Form::get($id);

        if ($form->data) {

            if ($form->data->save($post)) {

                if (isset($post['bf_roles'])) {
                    $form->data->saveRoles($post['bf_roles']);
                }

                if (isset($post['bf_users'])) {
                    $form->data->saveUsers($post['bf_users']);
                }

                $form->data->refresh();

                $data = $form->data->toArray();

                $data['bf_roles'] = $form->data->formsRoles->filter(function($prof){
                    return $prof->toArray();
                });

                $data['bf_users'] = $form->data->formsUsers->filter(function($prof){
                    return $prof->toArray();
                });

                return array(
                    'success' => TRUE,
                    'data' => $data
                );
            }

            
        }

        return array(
            'success' => FALSE,
            'data' => NULL
        );
    }

    public function deleteAction($id) {
        $query = Form::get($id);
        $done = FALSE;
        if ($query->data) {
            $done = $query->data->delete();
        }
        return array(
            'success' => $done
        );
    }

    public function uploadAction($id) {
        $query = Form::get($id);
        
        if ($query->data) {

            if ($this->request->hasFiles()) {

                if ( ! empty($query->data->bf_tpl_file)) {
                    // delete first
                    $path = APPPATH.'public/resources/forms/'.$query->data->bf_tpl_file;
                    if (file_exists($path) && ! is_dir($path)) {
                        @unlink($path);

                        $data = array();
                        
                        $data['bf_tpl_file'] = NULL;
                        $data['bf_tpl_orig'] = NULL;

                        $query->data->save($data);
                    }
                }

                foreach($this->request->getFiles() as $file) {
                    $type = $file->getExtension();
                    $orig = $file->getName();
                    $name = md5($orig.date('YmdHis')).'.'.$type;

                    $path = APPPATH.'public/resources/forms/'.$name;

                    if (@$file->moveTo($path)) {
                        $data = array();
                        
                        $data['bf_tpl_file'] = $name;
                        $data['bf_tpl_orig'] = $orig;

                        $query->data->save($data);
                    }
                }
            }

            return Form::get($id);
        }

        return Form::none();
    }
    
    public function downloadAction($bf_id = false) {
        if ($bf_id) {
            $query = Form::get($bf_id);
            if($query->data){
                $filepath = APPPATH.'public/resources/forms/'.$query->data->bf_tpl_file;
                if(file_exists($filepath)){                    
                    header("Content-Type: application/octet-stream");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-disposition: attachment; filename=\"".$query->data->bf_tpl_orig."\""); 
                    echo file_get_contents($filepath);
                    exit();                
                }
                exit("File not found.");                
            }
            exit("Invalid ID.");                
        }
        exit("Invalid parameters.");                
    }

    public function viewAction($filename = false) {
        if ($filename) {
            $filepath = APPPATH.'public/resources/forms/'.$filename;
            header('Content-Type: text/html; charset=utf-8'); // UTF-8
            $html = file_get_contents($filepath);
            echo $html;
        }
    }

}