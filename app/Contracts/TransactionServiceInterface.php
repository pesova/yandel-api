<?php 

namespace App\Contracts;


interface TransactionServiceInterface{

    public function list_orders(array $params = null);

    public function buy(array $params);

    public function sell(array $params);

    public function find_order($order_id = null);


}