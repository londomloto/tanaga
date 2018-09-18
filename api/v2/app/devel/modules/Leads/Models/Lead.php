<?php
namespace App\Leads\Models;

use App\Sales\Models\Sales,
    App\Sales\Models\SalesStatus,
    App\System\Models\Autonumber;

class Lead extends \Micro\Model {

    public function initialize() {
        $this->hasOne(
            'tl_mc_id', 
            'App\Customers\Models\Customer',
            'mc_id',
            array(
                'alias' => 'Customer'
            )
        );

        $this->hasOne(
            'tl_id', 
            'App\Sales\Models\Sales',
            'ts_tl_id',
            array(
                'alias' => 'Sales'
            )
        );
    }

    public function getSource() {
        return 'trx_leads';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);

        $data['tl_mc_company_name'] = NULL;
        $data['tl_mc_contact_name'] = NULL;
        $data['tl_mc_phone'] = NULL;

        if ($this->customer) {
            $data = array_merge($data, $this->customer->toArray());
        }

        if ($this->sales) {
            $data = array_merge($data, $this->sales->toArray());
        }

        return $data;
    }

    public function createSales($data = array()) {
        $bpmn = \Micro\App::getDefault()->bpmn;

        $sales = new Sales();
        $sales->ts_ticket_number = Autonumber::generate('SALES_TICKET');
        $sales->ts_tl_id = $this->tl_id;
        $sales->ts_mc_id = $this->tl_mc_id;
        $sales->ts_date_opportunity = date('Y-m-d');

        foreach($data as $k => $v) {
            $sales->{$k} = $v;
        }

        if ($sales->save()) {
            // todo: add to kanban
            $worker = $bpmn->worker('sales-flow');

            if ($worker) {
                $data = $sales->toArray();
                $init = $worker->start($data);

                if ($init['data']) {
                    $status = new SalesStatus();

                    $create = array(
                        'tss_ts_id'  => $sales->ts_id,
                        'tss_status' => $init['data']['id'],
                        'tss_target' => $init['data']['target'],
                        'tss_worker' => $worker->name(),
                        'tss_deleted' => 0,
                        'tss_created' => date('Y-m-d H:i:s')
                    );

                    $status->save($create);

                }
            }
        }
    }

    public function updateSales($data = array()) {
        $sales = $this->sales;
        
        if (count($data) > 0) {
            $sales->save($data);
        }
    }
}