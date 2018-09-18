<?php
namespace App\Dashboard\Controllers;

use App\Projects\Models\Project,
    App\Tasks\Models\TaskStatus;

class StatusesController extends \Micro\Controller {

    public function findAction() {

        $params = $this->request->getParams();

        $series = array();
        $config = array(
            'title' => '',
            'subtitle' => ''
        );

        if (isset($params['projects'])) {
            $projectIds = json_decode($params['projects']);

            $projects = Project::get()
                ->inWhere('sp_id', $projectIds)
                ->execute()
                ->map(function($e){
                    return $e;
                });

            $serie = array(
                'name' => 'statuses',
                'data' => array()
            );

            foreach($projects as $project) {
                
                $worksheet = $project->worksheet;

                if ($worksheet) {
                    foreach($worksheet->panels as $panel) {
                        $group = $panel->kp_title;

                        if ( ! isset($serie['data'][$group])) {
                            $serie['data'][$group] = array(
                                'name' => $group,
                                'y' => 0,
                                'color' => $panel->kp_accent
                            );
                        }

                        $statuses = array_map(function($e){ return $e['kst_status']; }, $panel->statuses->toArray());

                        if (count($statuses) > 0) {
                            $query = TaskStatus::get()
                                ->alias('a')
                                ->columns("COUNT(1) AS total")
                                ->join('App\Tasks\Models\Task', 'a.tts_tt_id = b.tt_id', 'b')
                                ->inWhere('a.tts_status', $statuses)
                                ->andWhere('a.tts_deleted = 0') 
                                ->andWhere('b.tt_sp_id = :project:', array('project' => $project->sp_id))
                                ->execute();

                            if ($query->count() > 0) {
                                $serie['data'][$group]['y'] += (double)$query[0]->total;
                            }
                        }
                    }
                }
            }

            $serie['data'] = array_values($serie['data']);
            $series[] = $serie;
        }


        $result = array(
            'success' => TRUE,
            'data' => array(
                'config' => $config,
                'series' => $series
            )
        );

        return $result;

    }

}