<?php
namespace App\Dashboard\Controllers;

use App\Projects\Models\Project,
    App\Users\Models\User,
    App\Tasks\Models\Task,
    App\Tasks\Models\TaskStatus,
    App\Tasks\Models\TaskUser,
    Micro\Helpers\Theme;

class CreationController extends \Micro\Controller {

    public function findAction() {

        $params = $this->request->getParams();
        $series = array();
        $config = array();

        if (isset($params['projects'])) {
            $projectIds = json_decode($params['projects']);
            $userIds = isset($params['users']) ? json_decode($params['users']) : array();

            $projects = Project::get()
                ->inWhere('sp_id', $projectIds)
                ->execute()
                ->map(function($e){
                    return $e;
                });

            $serie = array(
                'name' => 'cration',
                'data' => array()
            );

            $users = array();
            $queryUser = FALSE;

            if (count($userIds) > 0) {
                $queryUser = TRUE;
                $users = User::get()
                    ->inWhere('su_id', $userIds)
                    ->execute()
                    ->map(function($e){
                        return $e;
                    });
            }

            foreach($projects as $project) {
                if ( ! $queryUser) {
                    $users = $project->users;
                }

                $index = 0;
                foreach($users as $user) {
                    $label = $user->getName();
                    $group = $label.'_'.$user->su_id;
                    
                    if ( ! isset($serie['data'][$group])) {
                        $serie['data'][$group] = array(
                            'name' => $label,
                            'y' => 0,
                            'color' => self::defineColor($index)
                        );
                    }

                    $query = Task::get()
                        ->alias('a')
                        ->columns('COUNT(1) as total')
                        ->where('a.tt_sp_id = :project:', array('project' => $project->sp_id))
                        ->andWhere('a.tt_creator_id = :creator:', array('creator' => $user->su_id))
                        ->execute();

                    if ($query->count() > 0) {
                        $serie['data'][$group]['y'] += (double) $query[0]->total;
                    }

                    $index++;
                }
            }

            $serie['data'] = array_values($serie['data']);
            $series[] = $serie;
        }
        
        $result = array(
            'success' => TRUE,
            'data' => array(
                'series' => $series,
                'config' => $config
            )
        );

        return $result;

    }

    public static function defineColor($index) {
        $colors = Theme::colors();
        
        if ($index >= count($colors)) {
            $index = $index % count($colors);
        }

        return $colors[$index];
    }

}