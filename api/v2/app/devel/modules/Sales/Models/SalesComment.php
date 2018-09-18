<?php
namespace App\Sales\Models;

class SalesComment extends \Micro\Model {
    public function initialize() {
        $this->belongsTo(
            'tsc_ts_id',
            'App\Sales\Models\Sales',
            'ts_id',
            array(
                'alias' => 'Sales'
            )
        );

        $this->hasOne(
            'tsc_sender',
            'App\Users\Models\User',
            'su_id',
            array(
                'alias' => 'User'
            )
        );
    }

    public function getSource() {
        return 'trx_sales_comments';
    }

}