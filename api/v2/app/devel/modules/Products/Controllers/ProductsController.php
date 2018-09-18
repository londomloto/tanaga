<?php
namespace App\Products\Controllers;

use App\Products\Models\Product;

class ProductsController extends \Micro\Controller {

    public function findAction() {
        return Product::get()->filterable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $product = new Product();

        if ($product->save($post)) {
            return Product::get($product->tp_id);
        }

        return Product::none();
    }

    public function updateAction($id) {
        $query = Product::get($id);
        $post = $this->request->getJson();

        if ($query->data) {
            $product = $query->data;
            $product->save($post);
        }
        return $query;
    }

    public function deleteAction($id) {
        $query = Product::get($id);

        if ($query->data) {
            return array(
                'success' => $query->data->delete()
            );
        }
        
        return array('success' => FALSE);
    }

    public function servicesAction() {
        return Product::get()->columns("tp_service")->groupBy("tp_service")->paginate();
    }
}