<?php
namespace App\Kanban\Controllers;

use App\Kanban\Models\KanbanPanel;

class KanbanPanelsController extends \Micro\Controller {

    public function findAction() {
        $result = KanbanPanel::get()->paginate();
        return $result;
    }

    public function deleteAction($id) {
        $panel = KanbanPanel::get($id)->data;
        $success = FALSE;
        if ($panel) {
            $success = $panel->delete();
        }
        return array(
            'success' => $success
        );
    }
}