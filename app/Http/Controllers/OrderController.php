<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Contracts\OrderServiceInterface as OrderService;


class OrderController extends Controller
{
    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * Inject Dependencies
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function listOrders(Request $request){
        $req = $this->orderService->listOrders($request->all());

        return success( 'success', $req );
    }

    public function buy(Request $request){
        $validator = Validator::make($request->all(), [
            'order_type' => 'required',
            'order_id' => 'required',
            'volume' => 'required'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                return error($message, 422);
            }            
        }

        
        $req = $this->orderService->buy(
            $request->all(),
        );

        $message = 'Order Sent';
        
        return success( $req['messages'] ?? $message, $req['data'] ?? $req );
        
    }

    public function sell(Request $request){
        $validator = Validator::make($request->all(), [
            'order_type' => 'required',
            'order_id' => 'required',
            'volume' => 'required'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                return error($message, 422);
            }            
        }

        
        $req = $this->orderService->sell(
            $request->all(),
        );

        $message = 'Order Sent';
        
        return success( $req['messages'] ?? $message, $req['data'] ?? $req );
        
    }

    public function findOrder($order_id){
        $req = $this->orderService->findOrder($order_id);

        return success( 'success', $req );
    }
}
