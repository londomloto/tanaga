<?php
namespace App\Bpmn\Controllers;

use App\Bpmn\Models\FormUser;

class FormsUsersController extends \Micro\Controller {

    public function deleteAction($id) {
        $query = FormUser::get($id);
        $done = FALSE;
        if ($query->data) {
            $done = $query->data->delete();
        }
        return array(
            'success' => $done
        );
    }

}