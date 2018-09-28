<?php
namespace App\Proposal\Controllers;

use App\Proposal\Models\Attachment;

class AttachmentController extends \Micro\Controller {

    public function findAction() {
        return Attachment::get()->filterable()->paginate();
    }

}