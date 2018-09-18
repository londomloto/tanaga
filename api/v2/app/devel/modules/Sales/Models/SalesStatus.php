<?php
namespace App\Sales\Models;

class SalesStatus extends \Micro\Model {

    public function initialize() {
        $this->belongsTo(
            'tss_status',
            'App\Bpmn\Models\Link',
            'id',
            array(
                'alias' => 'Status'
            )
        );

        $this->belongsTo(
            'tss_ts_id',
            'App\Sales\Models\Sales',
            'ts_id',
            array(
                'alias' => 'Sales'
            )
        );

        $this->belongsTo(
            'tss_status',
            'App\Kanban\Models\KanbanStatus',
            'kst_status',
            array(
                'alias' => 'KanbanStatus'
            )
        );
    }

    public function getSource() {
        return 'trx_sales_statuses';
    }

}