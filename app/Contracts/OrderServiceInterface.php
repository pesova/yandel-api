<?php 

namespace App\Contracts;


interface OrderServiceInterface{
    public function listOrders(array $params = null);

    public function findOrder($order = null);
}