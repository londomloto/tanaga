<?php
namespace App\Leads\Controllers;

use App\Leads\Models\Lead;

class LeadsController extends \Micro\Controller {

    public function findAction() {

        return Lead::get()
            ->alias('a')
            ->join('App\Customers\Models\Customer', 'b.mc_id = a.tl_mc_id', 'b', 'left')
            ->filterable()
            ->sortable()
            ->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $lead = new Lead();

        if ($lead->save($post)) {
            if ($post['tl_status'] == 'HOT') {
                $lead->createSales($post);
            }
            return Lead::get($lead->tl_id);
        }

        return Lead::none();
    }

    public function updateAction($id) {
        $query = Lead::get($id);
        $post = $this->request->getJson();

        if ($query->data) {
            $lead = $query->data;
            if ($lead->save($post)) {
                if ($post['tl_status'] == 'HOT') {
                    if ( ! $lead->sales) {
                        $lead->createSales($post);
                    } else {
                        $lead->updateSales($post);
                    }
                }
            }
        }
        return $query;
    }

    public function deleteAction($id) {
        $query = Lead::get($id);

        if ($query->data) {
            return array(
                'success' => $query->data->delete()
            );
        }
        
        return array('success' => FALSE);
    }
}