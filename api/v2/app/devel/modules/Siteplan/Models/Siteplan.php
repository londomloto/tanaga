<?php
namespace App\Siteplan\Models;

class SitePlan extends \Micro\Model {

    public function initialize() {
        $this->hasMany(
            'tsp_id',
            'App\Siteplan\Models\SiteplanStatus',
            'tsps_tsp_id',
            array(
                'alias' => 'Statuses'
            )
        );
    }

    public function getSource() {
        return 'trx_siteplan';
    }

    public function getCurrentStatuses() {
        return $this->getStatuses(array(
            'conditions' => 'tsps_deleted = 0',
            'orderBy' => 'tsps_created DESC'
        ));
    }

}