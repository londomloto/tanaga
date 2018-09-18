<?php
namespace App\Kanban\Controllers;

use App\Sales\Models\Sales,
    App\Sales\Models\SalesStatus,
    App\System\Models\Autonumber,
    App\Sales\Models\SalesComment;

class KanbanController extends \Micro\Controller {

    public function findAction() {

        $params = $this->request->getQuery();
        $statuses = isset($params['statuses']) ? json_decode($params['statuses']) : array();
        
        if ( ! empty($statuses)) {

            $summaries = array(
                'items' => 0,
                'amounts' => 0
            );


            $data = SalesStatus::get()
                ->where('tss_status IN ('.implode(',', $statuses).') AND tss_deleted = 0 ')
                ->execute()
                ->filter(function($row) use (&$summaries,$params) {
                    $sales = $row->sales;
                    $stat  = $row->status;

                    $data = array();

                    $data['id'] = NULL;
                    $data['text'] = NULL;
                    $data['status'] = $row->tss_id;
                    $data['status_text'] = $stat ? $stat->label : NULL;
                    $data['status_date'] = $row->tss_created;
                    $data['current'] = $stat ? $stat->source_id : NULL;
                    $data['target'] = $row->tss_target;
                    $data['worker'] = $row->tss_worker;

                    if ($sales) {
                        $data['id'] = $sales->ts_id;

                        $data['ts_expected_date_closed_formatted'] = date('M Y', strtotime($sales->ts_expected_date_closed));

                        foreach($sales->toArray() as $k => $v) {
                            $data[$k] = $v;
                            if ($k == 'ts_amounts') {
                                $summaries['amounts'] += (double)$v;
                            }
                        }

                        $customer = $sales->getCustomer();

                        if ($customer) {
                            $data['text'] = $customer->mc_company_name;
                            foreach($customer->toArray() as $k => $v) {
                                $data[$k] = $v;
                            }
                        }

                        $product = $sales->getProduct();

                        if($product) {
                            $data['ts_products'] = $product->tp_service.' - '.$product->tp_sub_service;
                            foreach($product->toArray() as $pk=>$pv){
                                $data[$pk] = $pv;
                            }
                        }

                        $comments = $sales->getComments();

                        if($comments) {
                            $comments_arr = array();
                            foreach ($comments as $comment) {
                                $com = $comment->toArray();
                                $com['user']  = $comment->getUser()->su_fullname;
                                $comments_arr[] = $com;
                            }
                            $data['comments'] = $comments_arr;
                        } 

                        $now = new \DateTime("now");
                        $interval = $now->diff(new \DateTime($sales->ts_date_opportunity));
                        $data['aging'] = $interval->days;
                        
                        $search = TRUE;
                        if(isset($params['query'])){
                            $search = FALSE;
                            foreach ($data as $f=>$v) {
                                if(!$search && is_string($v) && stripos($v, $params['query'])>-1) $search = TRUE;
                            }
                        }

                        if($search){
                            return $data;
                        } 

                        $summaries = array(
                            'items' => 0,
                            'amounts' => 0
                        );
                    }

                });

            // order by amounts
            usort($data, function($a, $b){
                if(isset($a['ts_amounts']) && isset($b['ts_amounts'])){                    
                    $va = (double) $a['ts_amounts'];
                    $vb = (double) $b['ts_amounts'];

                    if ($va == $vb) return 0;
                    return $va < $vb ? 1 : -1;
                }
                return 1;
            });

            $summaries['items'] = count($data);
            $summaries['amounts_formatted'] = number_format($summaries['amounts'], 0, ',', '.');

            return array(
                'success' => TRUE,
                'data' => $data,
                'summaries' => $summaries
            );
        }

        return array(
            'success' => TRUE,
            'data' => array(),
            'summaries' => array()
        );

    }

     public function findGridAction(){
        $params = $this->request->getQuery();
        $query  = $this->request->getQuery('query');
        $fields = $this->request->getQuery('fields');

        $columns_map = array(
            'company' => 'App\Customers\Models\Customer.mc_company_name',
            'sub_service' => 'App\Products\Models\Product.tp_sub_service',
            'service' => 'App\Products\Models\Product.tp_service',
            'aging' => '(DATEDIFF(ts_date_opportunity, NOW()))'
        );

        $filter = null;

        if ( ! empty($query) && ! empty($fields)) {
            $fields = json_decode($fields);
            $where = array();

            foreach($fields as $name) {
                if (isset($columns_map[$name])) {
                    $where[] =  " $columns_map[$name] LIKE  :q:";
                }else{
                    $where[] = $name . " LIKE  :q:  ";                    
                }
            }            
        }
        return Sales::get()
            ->columns([
                // 'ts_id','ts_ticket_number','ts_tl_id','ts_amounts','ts_products','ts_date_opportunity','ts_drop_status','ts_creator','ts_mc_id','ts_segment','ts_tp_id','ts_revenue_type','ts_date_submitted','ts_date_negotiated','ts_date_po','ts_date_closed','ts_expected_date_closed'
                'App\Customers\Models\Customer.mc_company_name as company'
                ,'App\Products\Models\Product.tp_sub_service as sub_service'
                ,'App\Products\Models\Product.tp_service as service'
                ,'App\Sales\Models\Sales.*'
            ])
            ->join('App\Customers\Models\Customer')
            ->join('App\Products\Models\Product')
            ->where(!empty($where) ? '(' . implode(' OR ', $where) . ')' : null , !empty($where) ? array('q' => '%'.$query.'%') : null)
            ->paginate()
            ->filter(function($item) use($params){                    
                $data = $item->toArray();
                // print_r($data['app\Sales\Models\Sales']);
                foreach ($data['app\Sales\Models\Sales']->toArray() as $key => $value) {
                    $data[$key] = $value;
                }
                unset($data['app\Sales\Models\Sales']);
                return $data;

        //         // $company = $item->getCustomer();
        //         // $product = $item->getProduct();
        //         $statuses = $item->sales->getCurrentStatuses();
        //         foreach ($statuses as $status) {
        //             $st = $status->toArray();
        //             $st['status'] = $status->getStatus()->toArray();
        //             $st['target'] = $status->tss_target;
        //             $st['worker'] = $status->tss_worker;
        //             $data['statuses'][] = $st;
        //         }
        //         // $data['company'] = $company ? $company->mc_company_name : '';
        //         // $data['sub_service'] = $product ? $product->tp_sub_service : '';
        //         // $data['service'] = $product ? $product->tp_service : '';
        //         $now = new \DateTime("now");
        //         $interval = $now->diff(new \DateTime($item->ts_date_opportunity));
        //         $data['aging'] = $interval->days;
        //         // $search = TRUE;
        //         // if(isset($params['query'])){
        //         //     $search = FALSE;
        //         //     foreach ($data as $f=>$v) {
        //         //         if(!$search && is_string($v) && stripos($v, $params['query'])>-1) $search = TRUE;
        //         //     }
        //         // }

        //         // if($search){
        //         //     return $data;
        //         // } 
            });
    }

    // public function createAction() {
    //     $post = $this->request->getJson();
    //     $action = isset($post['action']) ? $post['action'] : 'SEND';
        
    //     if (isset($post['worker'], $post['data'])) {
    //         $worker = $this->bpmn->worker($post['worker']);
            
    //         if ($worker) {
                
    //             $sales = new Sales();

    //             if ($sales->save($post['data'])) {
    //                 $init = $worker->start($post['data']);
    //                 $affected = array();

    //                 if ($init['data']) {
                        
    //                     $status = new SalesStatus();

    //                     $create = array(
    //                         'tss_tsp_id' => $sales->ts_id,
    //                         'tss_status' => $init['data']['id'],
    //                         'tss_target' => $init['data']['target'],
    //                         'tss_worker' => $worker->name(),
    //                         'tss_deleted' => 0,
    //                         'tss_created' => date('Y-m-d H:i:s')
    //                     );
                        
    //                     if ($status->save($create)) {
    //                         $affected[] = $status->tss_status;
    //                     }

    //                 }

    //                 $affected = array_values(array_unique($affected));

    //                 return array(
    //                     'success' => TRUE,
    //                     'data' => array(
    //                         'affected' => $affected
    //                     )
    //                 );
                    
    //             }    
    //         }
    //     }
        
    //     return array(
    //         'success' => FALSE
    //     );
    // }

    public function createAction() {
        $this->db->begin();
        $bpmn = \Micro\App::getDefault()->bpmn;
        $post = $this->request->getJson();
        $products = $post['data']['products'];
        unset($post['products']);
        $ticket_number = Autonumber::generate('SALES_TICKET');
        $alphabet = 'A';
        $affected = [];
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

                    if ($status->save($create)) {
                        $affected[] = $status->tss_status;
                    }

                }

                $affected = array_values(array_unique($affected));
            }

            $alphabet++;
        }

        $this->db->commit();
        return array(
            'success' => TRUE,
            'data' => array(
                'affected' => $affected
            )
        );
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $action = isset($post['action']) ? $post['action'] : 'SEND';

        if ($post['data']) {
                $sales = Sales::findFirst($id);
                $affected = array();
                if ($sales->save($post['data'])) {
                    if($action == 'SEND' && isset($post['worker'])){
                        $worker = $this->bpmn->worker($post['worker']);
                        if ($worker) {
                            $move = array();
                            $curr = $sales->getCurrentStatuses();
                            $data = array_merge($post['data'], $sales->toArray());
                            foreach($curr as $c) {
                                $next = $worker->next($c->tss_status, $data);
                                
                                $affected[] = $c->tss_status;

                                if (count($next['data']) > 0) {

                                    $nextids = array_map(function($n){ return $n['id']; }, $next['data']);
                                    
                                    SalesStatus::find(array(
                                        'tss_id = :id: AND tss_status IN (:statuses:)',
                                        'bind' => array(
                                            'id' => $sales->ts_id,
                                            'statuses' => implode(',', $nextids)
                                        )
                                    ))->delete();

                                    foreach($next['data'] as $n) {

                                        $affected[] = $n['id'];

                                        /*$status = new SiteplanStatus();
                                        $create = array(
                                            'tsps_tsp_id' => $site->tsp_id,
                                            'tsps_status' => $n['id'],
                                            'tsps_target' => $n['target'],
                                            'tsps_worker' => $worker->name(),
                                            'tsps_deleted' => 0,
                                            'tsps_created' => date('Y-m-d H:i:s')
                                        );
                                        
                                        if ($status->save($create)) {
                                            $curr[] = $n['id'];
                                            $prev[] = $s->tsps_status;
                                            $move[] = $s;
                                        }*/

                                        $found = SalesStatus::findFirst(array(
                                            'tss_ts_id = :id: AND tss_status = :status:',
                                            'bind' => array(
                                                'id' => $sales->ts_id,
                                                'status' => $n['id']
                                            )
                                        ));
                                        if ( ! $found) {
                                            $status = new SalesStatus();
                                            $create = array(
                                                'tss_ts_id' => $sales->ts_id,
                                                'tss_status' => $n['id'],
                                                'tss_target' => $n['target'],
                                                'tss_worker' => $worker->name(),
                                                'tss_deleted' => 0,
                                                'tss_created' => date('Y-m-d H:i:s')
                                            );
                                            
                                            if ($status->save($create)) {
                                                $move[] = $c;
                                            }
                                        } else {
                                            $move[] = $c;
                                        }
                                    }
                                }

                            }

                            // nothing changed
                            if (count($move) > 0) {
                                foreach($move as $m) {
                                    $m->save(array('tss_deleted' => 1));
                                }
                            }

                            
                        }
                    }else if($action == 'SAVE'){
                        $curr = $sales->getCurrentStatuses();
                        foreach ($curr as $c) {
                            $affected[] = $c->tss_status;                 
                        }
                    }


                    $affected = array_values(array_unique($affected));

                    return array(
                        'success' => TRUE,
                        'data' => array(
                            'affected' => $affected
                        )
                    ); 
                }    
            
        }
        
        return array(
            'success' => FALSE
        );
    }

    public function deleteAction($id) {
        $query = SalesStatus::get($id);
        $done = FALSE;

        if ($query->data) {
            $done = $query->data->delete();
        }

        return array(
            'success' => $done
        );
    }

    public function commentAction() {
        $post = $this->request->getJson();
        if($post['data']){
            $comment = new SalesComment();
            if($comment->save($post['data'])){
                $com = SalesComment::get($comment->tsc_id);
                $com->user = new \stdClass();
                $com->user->su_fullname = $comment->getUser()->su_fullname;
                return $com;
            }
        }

        return SalesComment::none();
    }


    public function gantiapagituh() {
        
    }

}