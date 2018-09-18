<?php
namespace App\Kanban\Controllers;

use App\Kanban\Models\KanbanPanel;

class KanbanPanelsController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getParams();

        if (isset($params['project'])) {
            $query = KanbanPanel::get()
                ->alias('a')
                ->join('App\Kanban\Models\KanbanSetting', 'a.kp_ks_id = b.ks_id', 'b')
                ->join('App\Projects\Models\Project', 'b.ks_id = c.sp_worksheet_id', 'c')
                ->andWhere('c.sp_name = :project:', array('project' => $params['project']))
                ->filterable();

            return $query->paginate();
        } else {
            return KanbanPanel::get()->filterable()->paginate();
        }
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