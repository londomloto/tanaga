<?php
namespace App\Customers\Controllers;

use App\Customers\Models\Customer;

class CustomersController extends \Micro\Controller {

    public function findAction() {
        return Customer::get()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $customer = new Customer();

        if ($customer->save($post)) {
            return Customer::get($customer->mc_id);
        }

        return Customer::none();
    }

    public function updateAction($id) {
        $query = Customer::get($id);
        $post = $this->request->getJson();

        if ($query->data) {
            $query->data->save($post);
        }
        return $query;
    }

    public function deleteAction($id) {
        $query = Customer::get($id);

        if ($query->data) {
            return array(
                'success' => $query->data->delete()
            );
        }
        
        return array('success' => FALSE);
    }
}