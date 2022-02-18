<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Contracts\AuthServiceInterface as AuthService;
use App\Contracts\CardServiceInterface as CardService;
use App\Contracts\DepositServiceInterface as DepositService;
use App\Contracts\TransactionServiceInterface as TransactionService;

class CardController extends Controller
{
    /**
     * @var TransactionService $transactionService
     */
    private $authService, $cardService, $transactionService;

    /**
     * Inject Dependencies
     */
    public function __construct(
        AuthService $authService,
        CardService $cardService,
        DepositService $depositService,
        TransactionService $transactionService
    )
    {
        $this->authService = $authService;
        $this->cardService = $cardService;
        $this->depositService = $depositService;
        $this->transactionService = $transactionService;
    }

    /**
     * chaging / enrolling a a new card
     *
     * @param
     *
     * @return
     */
    public function addCard( Request $request )
    {
        DB::beginTransaction();
        try{
            $from = ['type' => 'card'];
            $to  = ['type' => 'wallet'];
            $remark  = 'New card tokenization';
            $amount = $request->amount ?? config('custom.app.MIN_DEPOSIT');

            $transaction = $this->depositService
                        ->setSource($from)
                        ->setDestination($to)
                        ->setAmount($amount)
                        ->setRemark($remark)
                        ->setUser(auth()->user())
                        ->createCreditTransaction()
                        ->getCreditTransaction();

            $response = $this->cardService->initInlineCharge( $transaction->amount, $transaction->reference );

            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }

        return isset($response['status']) && in_array($response['status'],  ['success', true])
                ? success($response['message'] ?? $response['message'], $response['data'] ?? $response)
                : error($response['message'] ?? $response['status'] ?? 'failed', $response);
    }
    
    public function verifyCharge( Request $request )
    {
        DB::beginTransaction();
        try{
            $charge = $this->cardService->validateCharge( $request->reference, $request->gateway ?? null );
            $response  = $this->transactionService->updateDeposit($request->reference, $charge);

            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }

        return success($response->status, $response);
    }

    public function findCard( int $card_id )
    {
        DB::beginTransaction();
        try{
            $response = $this->cardService->find($card_id);
            
            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }

        return success('Card found', $response);
    }

    public function listCards( Request $request )
    {
        DB::beginTransaction();
        try{
            $response = $this->cardService->listCards($request->all());
            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }

        return success('Cards  retrieved', $response);
    }

    public function deleteCard(int $card_id)
    {
        DB::beginTransaction();
        try{
            $response = $this->cardService->deleteCard($card_id);

            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }

        return success('Card deleted successfully');
    }
}
