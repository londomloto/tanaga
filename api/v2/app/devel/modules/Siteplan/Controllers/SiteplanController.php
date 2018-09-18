<?php
namespace App\Siteplan\Controllers;

use App\Siteplan\Models\Siteplan,
    App\Siteplan\Models\SiteplanStatus;

class SiteplanController extends \Micro\Controller {
    
    public function findAction() {
        
    }

    // POST & PUT
    public function createAction() {
        $post = $this->request->getJson();

        if (isset($post['worker'], $post['data'])) {
            $worker = $this->bpmn->worker($post['worker']);

            if ($worker) {
                if (isset($post['data']['tsp_id'])) {
                    $site = Siteplan::findFirst($post['data']['tsp_id']);
                } else {
                    $site = new Siteplan();    
                }

                if ($site->save($post['data'])) {
                    $currentStatuses = $site->getCurrentStatuses();

                    if ($currentStatuses->count() == 0) {
                        $start = $worker->start();
                        $status = new SiteplanStatus();
                        $status->save(array(
                            'tsps_tsp_id' => $site->tsp_id,
                            'tsps_status' => $start['data']['id'],
                            'tsps_deleted' => 0,
                            'tsps_created' => date('Y-m-d H:i:s')
                        ));
                    } else {
                        
                    }

                    return Siteplan::get($site->tsp_id);
                }    
            }
        }
        

        return Siteplan::none();
    }

    public function updateAction() {

    }

    public function deleteAction() {
        
    }

}