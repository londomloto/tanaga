<?php
namespace App\Ponpes\Controllers;

use App\Ponpes\Models\Ponpes;

class PonpesController extends \Micro\Controller {

    public function findAction() {
        return Ponpes::get()->paginate();
    }

    public function findByIdAction($id) {
        return Ponpes::get($id);
    }

}