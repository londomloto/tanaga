<?php
namespace App\Roles\Models;

class RolePanel extends \Micro\Model {

    public function getSource() {
        return 'sys_roles_panels';
    }

    public static function queryGranted($project, $role) {
        $data = self::get()
            ->where('srs_sp_id = :project: AND srs_sr_id = :role:', array(
                'project' => $project->sp_id,
                'role' => $role->sr_id
            ))
            ->execute();

        return (object) array(
            'success' => TRUE,
            'data' => $data
        );
    }

    public static function querySetup($project, $role) {

        $result = array(
            'success' => TRUE,
            'data' => array()
        );

        if ($project->worksheet) {
            $data = array();

            foreach($project->worksheet->panels as $panel) {

                $statuses = array();
                $maps = array();

                $query = RolePanel::find(array(
                    'srs_sp_id = :project: AND srs_sr_id = :role: AND srs_kp_id = :panel:',
                    'bind' => array(
                        'project' => $project->sp_id,
                        'role' => $role->sr_id,
                        'panel' => $panel->kp_id
                    )
                ));

                if ($query->count() == 0) {
                    $display = 'show';
                } else {
                    $display = 'hide';

                    foreach($query as $item) {
                        $maps[$item->srs_kst_id] = array(
                            'id' => $item->srs_id,
                            'checked' => $item->srs_checked
                        );

                        if ($item->srs_checked == 1) {
                            $display = 'custom';
                        }
                    }
                }

                foreach($panel->statuses as $ps) {
                    $ps = $ps->toArray();
                    
                    $id = NULL;
                    $checked = '0';

                    if (isset($maps[$ps['kst_id']])) {
                        $id = $maps[$ps['kst_id']]['id'];
                        $checked = (string) $maps[$ps['kst_id']]['checked'];
                    }

                    $statuses[] = array(
                        'srs_id' => $id,
                        'srs_sr_id' => $role->sr_id,
                        'srs_sp_id' => $project->sp_id,
                        'srs_kp_id' => $panel->kp_id,
                        'srs_kst_id' => $ps['kst_id'],
                        'srs_label' => $ps['kst_label'],
                        'srs_source_label' => $ps['kst_source_label'],
                        'srs_target_label' => $ps['kst_target_label'],
                        'srs_checked' => $checked
                    );
                }

                $data[] = array(
                    'panel_id' => $panel->kp_id,
                    'panel_title' => $panel->kp_title,
                    'panel_statuses' => $statuses,
                    'panel_display' => $display,
                    'panel_custom' => $display == 'custom' ? TRUE : FALSE
                );

            }

            $result['data'] = $data;
        }

        return $result;

    }

}