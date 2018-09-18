<?php
namespace App\Bpmn\Controllers;

use App\Bpmn\Models\FormRole;

class FormsRolesController extends \Micro\Controller {

    public function deleteAction($id) {
        $query = FormRole::get($id);
        $done = FALSE;
        if ($query->data) {
            $done = $query->data->delete();
        }
        return array(
            'success' => $done
        );
    }

}