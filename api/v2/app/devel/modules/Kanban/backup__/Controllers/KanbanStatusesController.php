<?php
namespace App\Kanban\Controllers;

use App\Kanban\Models\KanbanStatus;

class KanbanStatusesController extends \Micro\Controller {
    
    public function findAction() {
        
    }

    public function deleteAction($id) {
        $status = KanbanStatus::get($id);
        if ($status->data) {
            $status->data->delete();
        }
        return array(
            'success' => TRUE
        );
    }
}