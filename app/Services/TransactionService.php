<?php

namespace App\Services;

use App\Contracts\TransactionServiceInterface;

use App\Contracts\WithdrawalServiceInterface as WithdrawalService;
use App\Contracts\DepositServiceInterface as DepositService;
use App\Models\Transaction;
use App\Models\User;
use Auth;

/**
 * Inherits basic CRUD functionalities from BaseService
 */
class TransactionService extends BaseService implements TransactionServiceInterface
{
    private $transaction, $depositService, $withdrawalService, 
            $cardService, $bankService, $walletService;

    /**
     * Inject Dependencies
     */
    public function __construct( 
        Transaction $transaction,
        DepositService $depositService,
        WithdrawalService $withdrawalService
    )
    {
        $this->model = $transaction;
        $this->depositService = $depositService;
        $this->withdrawalService = $withdrawalService;
    }
    
    /**
     * To handle deposit into wallet, as well as topup into wallet
     * 
     * @param int|Wallet|Card $from
     * @param int|Bank|Wallet $to
     * @param float $amount
     * @param string $remark
     */
    public function deposit( ?array $from, ?array $to, float $amount, string $remark =  null )
    {
        return $this->depositService->deposit( $from, $to, $amount, $remark );
    }

    /**
     * To handle withdrawal into wallet
     * 
     * @param int|Wallet $from
     * @param int|Bank|Wallet|User $to
     * @param float $amount
     * @param string $remark
     */
    public function withdraw( ?array $from, ?array $to, float $amount, string $remark =  null)
    {
        return $this->withdrawalService->withdraw( $from, $to, $amount, $remark );
    }

    public function validate( $transaction )
    {
        if( is_numeric($transaction) ) $transaction = (int) $transaction;
        if( gettype($transaction) !== 'object' ) $transaction = $this->findTransaction( $transaction );

        if( ! in_array($transaction->status, ['pending', 'abandoned', 'failed']) ) return $transaction;
        
        if($transaction->type === 'deposit')
            return $this->depositService->validate( $transaction );

        return $transaction;
    }

    /**
     * Lists all transactions performed by a user
     * filterable by reference, transaction date,
     * type (transactionable type), status (pending, success, failed), 
     * 
     * @param array $params
     */
    public function listTransactions( array $params )
    {
        return auth()->user()->transactions()
                ->when($params['category'] ?? false, function($query, $category){
                    $query->where(function ($query) use ($category){
                        $query->where('source_type', $category)
                        ->orWhere('destination_type', $category);
                    });
                })
                ->when($params['category_id'] ?? false, function($query, $id){
                    $query->where(function ($query) use ($id){
                        $query->where('source_id', $id)
                        ->orWhere('destination_id', $id);
                    });
                })
                ->when($params['type'] ?? false, function($query, $type) use ($params){
                    $query->where('type', $type);
                })
                ->paginate( $params['limit'] ?? config('custom.app.PAGE_LIMIT'));
    }

    /**
     * Finds a transaction performed by a user
     * 
     * @param  string|int $transaction
     */
    public function findTransaction( $transaction )
    {
        return auth()->user()->transactions()
                ->when(!is_numeric($transaction) && is_string($transaction), function($query) use ($transaction){
                    $query->where('reference', $transaction);
                })
                ->when(is_numeric($transaction), function($query) use ($transaction){
                    $query->where('id', (int) $transaction);
                })
                ->firstOrFail();
    }
}