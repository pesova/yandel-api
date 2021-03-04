<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Contracts\TransactionServiceInterface as TransactionService;


class TransactionController extends Controller
{
    /**
     * @var TransactionService
     */
    private $orderService;

    /**
     * Inject Dependencies
     */
    public function __construct(TransactionService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function list_orders(Request $request){
        $req = $this->orderService->list_orders($request->all());

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

        $message = 'Transaction Sent';
        
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

        $message = 'Transaction Sent';
        
        return success( $req['messages'] ?? $message, $req['data'] ?? $req );
        
    }

    public function find_order($order_id){
        $req = $this->orderService->find_order($order_id);

        return success( 'success', $req );
    }
}
