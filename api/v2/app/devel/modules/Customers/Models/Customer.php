<?php
namespace App\Customers\Models;

class Customer extends \Micro\Model {

    public function getSource() {
        return 'mst_customers';
    }

}