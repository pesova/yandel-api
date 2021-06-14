<?php 

namespace App\Services;

use DB;
use Auth;
use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Events\OrderCreated;
use App\Contracts\OrderServiceInterface;
use App\Exceptions\OrderServiceException;



class OrderService extends BaseService implements OrderServiceInterface{


    private $order;

    public function __construct( 
        Order $order,
        Coupon $coupon
    )
    {
        $this->model = $order;
        $this->coupon = $coupon;
    }

    public function listOrders(array $params = null){
        $user = Auth::user();
        
        $orders = $user->orders()
                ->when(isset($params['mode']), function($query) use ($params){
                    $query->whereMode($params['mode']);
                })
                ->when(isset($params['status']), function($query) use ($params){
                    $query->whereStatus($params['status']);
                })
                ->get();

        return $orders;

    }

    public function findOrder($order = null){
        $user = Auth::user();

        $order = $this->model::when(is_numeric($order), function($query) use ($order){
                    $query->whereId($order);
;                })
                ->when( ! is_numeric($order) && is_string($order), function($query) use ($order){
                    $query->whereReference($order);
                })
                ->where('user_id', $user->id)
                ->firstOrFail();

        return $order;        
    }


}

