<?php
namespace App\Sales\Models;

use App\Sales\Models\SalesProduct;

class Sales extends \Micro\Model {

    public function initialize() {
        $this->hasOne(
            'ts_tl_id',
            'App\Leads\Models\Lead',
            'tl_id',
            array(
                'alias' => 'Lead'
            )
        );

        $this->hasOne(
            'ts_tp_id',
            'App\Products\Models\Product',
            'tp_id',
            array(
                'alias' => 'Product'
            )
        );

        $this->hasOne(
            'ts_mc_id',
            'App\Customers\Models\Customer',
            'mc_id',
            array(
                'alias' => 'Customer'
            )
        );

        $this->hasMany(
            'ts_id',
            'App\Sales\Models\SalesStatus',
            'tss_ts_id',
            array(
                'alias' => 'Statuses'
            )
        );

        $this->hasMany(
            'ts_id',
            'App\Sales\Models\SalesComment',
            'tsc_ts_id',
            array(
                'alias' => 'Comments'
            )
        );
    }

    public function getSource() {
        return 'trx_sales';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        $data['ts_amounts_formatted'] = number_format($data['ts_amounts'], 0, ',', '.');
        $data['ts_statuses'] = array();
        $data['statuses'] = array();

        foreach($this->getCurrentStatuses() as $status) {
            $data['ts_statuses'][] = (int)$status->tss_status;
            $st = $status->toArray();
            $ks = $status->getKanbanStatus();
            
            // $st['panel']  = $status->getKanbanStatus()->getPanel()->toArray();
            $st['panel']  = $ks ? $ks->getPanel()->toArray() : array();
            $st['status'] = $status->getStatus()->toArray();
            $st['target'] = $status->tss_target;
            $st['worker'] = $status->tss_worker;

            $data['statuses'][] = $st;
        }

        $now = new \DateTime("now");
        $interval = $now->diff(new \DateTime($data['ts_date_opportunity']));
        $data['aging'] = $interval->days;

        return $data;
    }

    public function getCurrentStatuses() {
        return $this->getStatuses(array(
            'conditions' => 'tss_deleted = 0',
            'orderBy' => 'tss_created DESC'
        ));
    }

    // public function getCustomer() {
    //     if ($this->cus) {
    //         return $this->lead->customer;
    //     }
    //     return NULL;
    // }

    public function addProduct($products = false){
        if($products){
            foreach ($products as $product) {
                $salesProduct = new SalesProduct();
                $salesProduct->tsp_ts_id = $this->ts_id;
                if(!$salesProduct->save($product)){
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}