<?php
namespace App\Profile\Controllers;

use App\Users\Models\User;

class ProfileController extends \Micro\Controller {
    
    public function updateAction($id) {
        $user = User::findFirst($id);
        $post = $this->request->getJson();

        if ($user) {

            if (isset($post['su_passwd']) && ! empty($post['su_passwd'])) {
                $post['su_passwd'] = $this->security->createHash($post['su_passwd']);
            }
            $user->save($post);
        }

        return array(
            'success' => TRUE
        );
    }

    public function uploadAction($id) {
        $user = User::findFirst($id);
        $data = array();

        if ($user) {
            if ($this->request->hasFiles()) {
                $this->uploader->initialize(array(
                    'path' => APPPATH.'public/resources/avatars/',
                    'types' => array('png', 'jpg', 'jpeg', 'gif', 'bmp'),
                    'encrypt' => TRUE
                ));

                if ($this->uploader->upload()) {
                    $info = $this->uploader->getResult();
                    $user->su_avatar = $info->filename;
                    $user->save();
                }
            }

            return array(
                'success' => TRUE,
                'data' => $user->toArray()
            );
        } else {
            return array(
                'success' => FALSE,
                'message' => 'User not found!'
            );    
        }
    }

}