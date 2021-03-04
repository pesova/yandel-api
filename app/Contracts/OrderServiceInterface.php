<?php 

namespace App\Contracts;


interface OrderServiceInterface{

    public function listOrders(array $params = null);

    public function buy(array $params);

    public function sell(array $params);

    public function findOrder($order_id = null);


}