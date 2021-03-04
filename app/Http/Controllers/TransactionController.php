<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Contracts\AuthServiceInterface as AuthService;
use App\Contracts\DepositServiceInterface as DepositService;
use App\Contracts\WithdrawalServiceInterface as WithdrawalService;
use App\Contracts\TransactionServiceInterface as TransactionService;

class TransactionController extends Controller
{
    /**
     * @var TransactionService $transactionService
     */
    private $authService, $transactionService, $depositService, $withdrawalService;

    /**
     * Inject Dependencies
     */
    public function __construct(
        AuthService $authService,
        DepositService $depositService,
        WithdrawalService $withdrawalService,
        TransactionService $transactionService
    )
    {
        $this->authService = $authService;
        $this->depositService = $depositService;
        $this->withdrawalService = $withdrawalService;
        $this->transactionService = $transactionService;
    }

    /**
     * Deposits funds into user wallet
     * from another user|wallet|card
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\AppException
     */
    public function deposit(Request $request)
    {
        extract( $request->all() );
        
        // ensure users cant perform tx on resticted wallets
        if( $from['type'] === 'wallet' && (!isset($from['id']) || !in_array($from['id'], ['naira'])) )
            $from['id'] = 'naira';
        if( $to['type'] === 'wallet' && (!isset($to['id']) || !in_array($to['id'], ['naira'])) )
            $to['id'] = 'naira';

        $req = $this->depositService->deposit(
            $from, $to, $amount, $remark ?? null
        );

        return success('Deposit successful', $req);
    }

    /**
     * Deposit hook to be used by bank(ussd|transfer) and payment gateways
     * to record successful deposit transactions on respective channel
     */
    public function depositHook(Request $request)
    {
        DB::beginTransaction();
        try{
            extract( $request->all() );

            // verify inflow

            // if depositing into wallet
            $depositable = $this->walletService->deposit(); // record in wallet table and deposit table, then return deposit table relation

            // record transaction
            $req = $this->withdrawalService->deposit( $from, $to, $amount, $remark ?? null );

            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            // TODO: Log this
        }

        return success('Deposit successful');
    }


    /**
     * Withdraws funds from wallet
     * to wallet|bank
     */
    public function withdraw(Request $request)
    {
        extract( $request->all() );
        
        // ensure users cant perform tx on resticted wallets
        if( $from['type'] === 'wallet' && (!isset($from['id']) || !in_array($from['id'], ['naira'])) )
            $from['id'] = 'naira';
        if( $to['type'] === 'wallet' && (!isset($to['id']) || !in_array($to['id'], ['naira'])) )
            $to['id'] = 'naira';

        $req = $this->withdrawalService->withdraw(
            $from, $to, $amount, $remark ?? null
        );

        return success('Withdrawal request submitted. Awaiting processing.', $req);
    }

    /**
     * Check status of a deposit transaction
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\AppException
     * @throws \Throwable
     */
    public function validateTransaction(Request $request )
    {
        DB::beginTransaction();
        try{
            $req = $this->transactionService->validate( $request->reference );
            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }

        return success('Transactions retrieved', $req);
    }

    /**
     * Lists all transactions filterable by transaction type
     */
    public function listTransactions(Request $request)
    {
        $req = $this->transactionService->listTransactions( $request->all() );
        return success('Transactions retrieved', $req);
    }

    /**
     * Find a transaction by reference
     */
    public function findTransaction($reference)
    {
        $req = $this->transactionService->findTransaction( $reference );
        return success('Transactions retrieved', $req);
    }
}
