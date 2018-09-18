<?php
namespace App\Siteplan\Models;

class SiteplanStatus extends \Micro\Model {

    public function initialize() {
        $this->belongsTo(
            'tsps_status',
            'App\Bpmn\Models\Link',
            'id',
            array(
                'alias' => 'Status'
            )
        );

        $this->belongsTo(
            'tsps_tsp_id',
            'App\Siteplan\Models\Siteplan',
            'tsp_id',
            array(
                'alias' => 'Siteplan'
            )
        );
    }

    public function getSource() {
        return 'trx_siteplan_statuses';
    }

}