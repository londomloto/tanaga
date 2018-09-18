<?php
namespace App\Siteplan\Controllers;

use App\Siteplan\Models\SiteplanStatus;

class SiteplanStatusesController extends \Micro\Controller {

    public function findAction() {

        $params = $this->request->getQuery();
        $statuses = isset($params['statuses']) ? json_decode($params['statuses']) : array();

        if ( ! empty($statuses)) {
            $data = SiteplanStatus::get()
                ->where('tsps_status IN ('.implode(',', $statuses).')')
                ->execute()
                ->filter(function($row){
                    $data = array(
                        'id' => $row->tsps_id,
                        'text' => $row->siteplan->tsp_name
                    );
                    return $data;
                });

            
            return array(
                'success' => TRUE,
                'data' => $data
            );

        }

        return array(
            'success' => TRUE,
            'data' => array()
        );

    }

    public function deleteAction($id) {
        $status = SiteplanStatus::get($id);
        $done = FALSE;
        if ($status->data) {
            $done = $status->data->delete();
        }
        return array('success' => $done);
    }

}