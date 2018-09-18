<?php
namespace App\Sales\Controllers;

use App\Sales\Models\Sales,
    App\System\Models\Autonumber,
	App\Sales\Models\SalesStatus;

class SalesController extends \Micro\Controller {

    public function findAction() {}

    public function createAction() {
    	$this->db->begin();
    	$bpmn = \Micro\App::getDefault()->bpmn;
    	$post = $this->request->getJson();
    	$products = $post['products'];
    	unset($post['products']);
    	$ticket_number = Autonumber::generate('SALES_TICKET');
    	$alphabet = 'A';
    	foreach ($products as $product) {
        	$sales = new Sales();
        	$data = array_merge($post,$product);
        	$data['ts_ticket_number'] = $ticket_number.'-'.$alphabet;
        	$data['ts_date_opportunity'] = date('Y-m-d');
        	if(!$sales->save($data)){
        		$this->db->rollback();
        		return Sales::none();        
        	}
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

	        $alphabet++;
    	}

    	$this->db->commit();
       	return Sales::get($sales->ts_id);
    }

}