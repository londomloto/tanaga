<?php
namespace App\Sales\Models;

class SalesProduct extends \Micro\Model {

    public function initialize() {

        $this->belongsTo(
            'tsp_ts_id',
            'App\Sales\Models\Sales',
            'ts_id',
            array(
                'alias' => 'Sales'
            )
        );
    }

    public function getSource() {
        return 'trx_sales_products';
    }

}