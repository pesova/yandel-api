<?php 

namespace App\Services;

use DB;
use Auth;
use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Exceptions\TransactionServiceException;
use App\Events\TransactionCreated;
use App\Contracts\TransactionServiceInterface;



class TransactionService extends BaseService implements TransactionServiceInterface{


    private $order;

    public function __construct( 
        Order $order
    )
    {
        $this->model = $order;
    }

    public function list_orders(array $params = null){
        $user = Auth::user();

        if (isset($params['order_type'])) {
            return $user->orders->where('order_type', $params['order_type']);
        }

        if (isset($params['status'])) {
            return $user->orders->where('status', $params['status']);
        }

        return $user->orders;
    }

    public function buy(array $request){
        $user = Auth::user();
            
        DB::beginTransaction();
        try {
            $order = new Order();
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

            if ($order->save()) {
                DB::commit();
                // trigger Transaction event
                event( new TransactionCreated($order));
                
                return ["order"=> $order];
            } else {
                DB::rollback();
                throw new TransactionServiceException('Something went wrong, try again later');
            }
            

        } catch(\Throwable $e){
            handleThrowable($e);
        }
        
    }

    public function sell(array $request){
        $user = Auth::user();
            
        DB::beginTransaction();
        try {
            $order = new Order();
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

            if ($order->save()) {
                DB::commit();
                // trigger Transaction event
                event( new TransactionCreated($order));
                
                return ["order"=> $order];
            } else {
                DB::rollback();
                throw new TransactionServiceException('Something went wrong, try again later');
            }
            

        } catch(\Throwable $e){
            handleThrowable($e);
        }
        
    }

    public function find_order($order_id = null){
        $user = Auth::user();

        $order = Order::where('id', $order_id)->where('user_id', $user->id)->first();
            
        // trigger contact support event

        return $order;
        
    }


}

