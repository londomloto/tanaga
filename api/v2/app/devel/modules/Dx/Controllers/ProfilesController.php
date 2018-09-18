<?php
namespace App\Dx\Controllers;

use App\Dx\Models\Profile;

class ProfilesController extends \Micro\Controller {

    public function findAction() {
        return Profile::get()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();

        if ( ! empty($post['profile_name'])) {
            $profile = new Profile();
            if ($profile->save($post)) {
                return Profile::get($profile->profile_id);
            }
        }
        return Profile::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $profile = Profile::get($id);

        if ($profile->data) {
            $profile->data->save($post);
        }

        return $profile;
    }

    public function deleteAction($id) {
        $profile = Profile::get($id);
        $success = FALSE;

        if ($profile->data) {
            $success = $profile->data->delete();
        }

        return array(
            'success' => $success
        );
    }
}