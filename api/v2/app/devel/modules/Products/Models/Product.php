<?php
namespace App\Products\Models;

use App\System\Models\Autonumber;

class Product extends \Micro\Model {

    public function initialize() {
        
    }

    public function getSource() {
        return 'trx_products';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        
        return $data;
    }

}