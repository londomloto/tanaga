<?php
namespace App\Kanban\Controllers;

use App\Kanban\Models\KanbanSetting;

class KanbanWorkspacesController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getQuery();
        $kanban = KanbanSetting::get($params['kanban'])->data;

        if ($kanban) {
            $workspaces = $kanban->getWorkspaces();

            return array(
                'success' => TRUE,
                'data' => $workspaces
            );
        }

        return array(
            'success' => TRUE,
            'data' => array()
        );
    }

}