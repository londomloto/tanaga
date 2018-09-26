<?php
namespace App\Ponpes\Controllers;

use App\Ponpes\Models\Ponpes;
use App\Ponpes\Models\Author;

class PonpesController extends \Micro\Controller {

    public function findAction() {
        $query = Ponpes::get()
            ->alias('a')
            ->columns('a.*')
            ->filterable();
        
        if ( ! $this->role->can('manage_app@application')) {
            $auth = $this->auth->user();
            $query->join('App\Ponpes\Models\Author', 'a.id_ponpes = b.id_ponpes', 'b');
            $query->andWhere('b.su_id = :user:', array('user' => $auth['su_id']));
        }

        return $query->paginate()->filter(function($row){
            return $row->toArray();
        });
    }

    public function findByIdAction($id) {
        $result = Ponpes::get($id);

        if ($result->data && ! $this->role->can('manage_app@application')) {

            $authors = $result->data->getAuthors()->filter(function($item){
                return $item->toArray();
            });

            $authors = array_map(function($item){ return $item['su_id']; }, $authors);
            $user = $this->auth->user();

            if ( ! in_array($user['su_id'], $authors)) {
                return array(
                    'success' => FALSE,
                    'status' => 403,
                    'message' => 'Anda tidak memiliki hak untuk mengakses data bersangkutan'
                );
            }
            
        }

        return $result;
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Ponpes();
        if ($data->save($post)) {
            $user = $this->auth->user();

            $auth = new Author();
            $auth->id_ponpes = $data->id_ponpes;
            $auth->su_id = $user['su_id'];
            $auth->posisi = 'Operator';
            $auth->save();

            return Ponpes::get($data->id_ponpes);
        }

        return Ponpes::none();
    }

    public function updateAction($id) {
        $item = Ponpes::get($id);
        $post = $this->request->getJson();

        if ($item->data) {
            $item->data->save($post);
        }
        
        return $item;
    }

    public function deleteAction($id) {
        $data = Ponpes::get($id)->data;
        $done = FALSE;
        if ($data) {
            $done = $data->delete();
        }
        return array(
            'success' => $done
        );
    }

    public function uploadAction($id) {
        $item = Ponpes::get($id);
        $done = FALSE;
        $data = NULL;
        $message = '';

        if ($item->data && $this->request->hasFiles()) {
            $path = APPPATH.'public/resources/ponpes/';
            $this->uploader->initialize(array(
                'path' => $path,
                'types' => array('jpg', 'jpeg', 'png'),
                'encrypt' => TRUE
            ));

            if ($this->uploader->upload()) {
                $info = $this->uploader->getResult();
                $item->data->img_gedung = $info->filename;
                $item->data->save();
                $done = TRUE;
                $data = $item->data->toArray();
            } else {
                $message = $this->uploader->getMessage();
            }
        }

        return array(
            'success' => $done,
            'data' => $data,
            'message' => $message
        );
    }
}