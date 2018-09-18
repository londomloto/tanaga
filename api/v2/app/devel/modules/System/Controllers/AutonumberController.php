<?php
namespace App\System\Controllers;

use App\System\Models\Autonumber;

class AutonumberController extends \Micro\Controller {

    public function findAction() {
        return Autonumber::get()->paginate();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $query = Autonumber::get($id);

        if ($query->data) {
            $query->data->save($post);
        }

        return $query;
    }
}