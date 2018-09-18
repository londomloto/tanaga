<?php
namespace App\Bpmn\Controllers;

use App\Bpmn\Models\Link;

class LinksController extends \Micro\Controller {

    public function findAction() {
        $query = Link::get()
            ->filterable();

        return $query->paginate();
    }

}