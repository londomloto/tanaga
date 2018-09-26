<?php
namespace App\Masjid\Controllers;

use App\Masjid\Models\Masjid;
use App\Masjid\Models\Author;

class MasjidController extends \Micro\Controller {

    public function findAction() {
        $query = Masjid::get()
            ->alias('a')
            ->columns('a.*')
            ->filterable();
        
        if ( ! $this->role->can('manage_app@application')) {
            $auth = $this->auth->user();
            $query->join('App\Masjid\Models\Author', 'a.id_rumah_ibadah = b.id_rumah_ibadah', 'b');
            $query->andWhere('b.su_id = :user:', array('user' => $auth['su_id']));
        }

        return $query->paginate()->filter(function($row){
            return $row->toArray();
        });
    }

    public function findByIdAction($id) {
        $result = Masjid::get($id);

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
        $data = new Masjid();
        if ($data->save($post)) {
            $user = $this->auth->user();

            $auth = new Author();
            $auth->id_rumah_ibadah = $data->id_rumah_ibadah;
            $auth->su_id = $user['su_id'];
            $auth->posisi = 'Operator';
            $auth->save();

            return Masjid::get($data->id_rumah_ibadah);
        }

        return Masjid::none();
    }

    public function updateAction($id) {
        $item = Masjid::get($id);
        $post = $this->request->getJson();

        if ($item->data) {
            $item->data->save($post);
        }
        
        return $item;
    }

    public function deleteAction($id) {
        $data = Masjid::get($id)->data;
        $done = FALSE;
        if ($data) {
            $done = $data->delete();
        }
        return array(
            'success' => $done
        );
    }

    public function uploadAction($id) {
        $item = Masjid::get($id);
        $done = FALSE;
        $data = NULL;
        $message = '';

        if ($item->data && $this->request->hasFiles()) {
            $path = APPPATH.'public/resources/masjid/';
            
            $this->uploader->initialize(array(
                'path' => $path,
                'types' => array('jpg', 'jpeg', 'png'),
                'encrypt' => TRUE
            ));

            $done = $this->uploader->upload();

            if ($done) {
                $info = $this->uploader->getResult();
                $item->data->img_gedung = $info->filename;
                $item->data->save();
                
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