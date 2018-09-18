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
                foreach($this->request->getFiles() as $file) {
                    $name = $file->getName();
                    $path = APPPATH.'public/resources/avatars/'.$name;
                    if ($file->moveTo($path)) {
                        $user->save(array('su_avatar' => $name));
                    }
                    break;
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