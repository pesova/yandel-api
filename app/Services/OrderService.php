<?php 

namespace App\Services;

use DB;
use Auth;
use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Exceptions\OrderServiceException;
use App\Events\OrderCreated;
use App\Contracts\OrderServiceInterface;



class OrderService extends BaseService implements OrderServiceInterface{


    private $order;

    public function __construct( 
        Order $order
    )
    {
        $this->model = $order;
    }

    public function listOrders(array $params = null){
        $user = Auth::user();
        
             $orders = $user->orders()
                        ->when(isset($params['order_type']), function($query) use ($params){
                            $query->whereOrderType($params);
                        })
                        ->when(isset($params['status']), function($query) use ($params){
                            $query->whereStatus($params);
                        })
                        ->get();

        return $orders;

    }

    public function buy(array $request){
        $user = Auth::user();
            
        DB::beginOrder();
        try {
            $order = new $this->model;
            $coupon = Coupon::where('id', $request['order_id'])->first();

            $volume = $request['volume'];
            $buy_rate = $coupon->buy_rate;
            $buy_margin = $coupon->buy_margin;
            $total_payable = ($buy_margin + $buy_rate) * $volume;

            $order->reference = random_strings();
            $order->user_id = $user->id;
            $order->mode = 'buy';
            $order->status = 'pending';
            $order->total_payable = $total_payable;
            $order->volume = $volume;
            $order->order_type = $request['order_type'];
            $order->order_id = $request['order_id'];
            

            $order->currency = $request['currency'] ?? NULL;
            $order->coupon_type = $request['coupon_type'] ?? NULL;
            $order->rate = $request['rate'] ?? 0;
            $order->unit_price = $request['unit_price'] ?? 0;
            $order->fee = $request['fee']  ?? 0;
            $order->remark = $request['remark'] ?? NULL;
            $order->save();
            DB::commit();
            // trigger Order event
            event( new OrderCreated($order));
            
            return ["order"=> $order];       
            
        } catch(\Throwable $e){
            DB::rollback();
            handleThrowable($e,'order', 'Buy Order');
        }
        
    }

    public function sell(array $request){
        $user = Auth::user();
            
        DB::beginTransaction();
        try {
            $order = new $this->model;
            $coupon = Coupon::where('id', $request['order_id'])->first();

            $volume = $request['volume'];
            $sell_rate = $coupon->sell_rate;
            $sell_margin = $coupon->sell_margin;
            $total_payable = $total_payable = ($sell_margin + $sell_rate) * $volume;

            $order->reference = random_strings();
            $order->user_id = $user->id;
            $order->mode = 'sell';
            $order->status = 'pending';
            $order->total_payable = $total_payable;
            $order->volume = $volume;
            $order->order_type = $request['order_type'];
            $order->order_id = $request['order_id'];
            

            $order->currency = $request['currency'] ?? NULL;
            $order->coupon_type = $request['coupon_type'] ?? NULL;
            $order->rate = $request['rate'] ?? 0;
            $order->unit_price = $request['unit_price'] ?? 0;
            $order->fee = $request['fee']  ?? 0;
            $order->remark = $request['remark'] ?? NULL;

            $order->save();
            DB::commit();
            // trigger Order event
            event( new OrderCreated($order));
            
            return ["order"=> $order];
          
        } catch(\Throwable $e){
            DB::rollback();
            handleThrowable($e,'order', 'sell Order');
        }
        
    }

    public function findOrder($order_id = null){
        $user = Auth::user();

        $order = $this->model::where('order_id', $order_id)->where('user_id', $user->id)->first();
        return $order;        
    }


}

