<?php

namespace App\Services;

use DB;
use Auth;
use App\Models\User;
use App\Models\Transaction;
use App\Events\RunWalletCredit;
use App\Contracts\WithdrawalServiceInterface;
use App\Exceptions\WithdrawalServiceException;
use App\Contracts\BankServiceInterface as BankService;
use App\Contracts\WalletServiceInterface as WalletService;


/**
 * Inherits basic CRUD functionalities from BaseService
 */
class WithdrawalService extends BaseService implements WithdrawalServiceInterface
{
    private $bankService, $walletService;
    private $source, $destination, $amount, $remark, $user, $debitTransaction, $creditTransaction;
    
    const WITHDRAW_TO_SOURCES = ['bank'];
    const WITHDRAW_FROM_SOURCES = ['wallet'];
    const INSUFFICIENT_BALANCE = 'Insufficient balance';
    const UNSUPPORTED_DESTINATION_TYPE = "Unsupported destination type";
    
    /**
     * Inject Dependencies
     */
    public function __construct(
        BankService $bankService,
        WalletService $walletService
    )
    {
        $this->bankService = $bankService;
        $this->walletService = $walletService;
    }

    /**
     * Handle withdrawal transaction processing
     * 
     * @param array $from
     * @param array $to
     * @param float $amount
     * @param ?string $remark
     * 
     * @return object
     */
    public function withdraw( array $from, array $to, float $amount, ?string $remark, User $user = null)
    {
        $this->setSource($from)
            ->setDestination($to)
            ->setAmount($amount)
            ->setRemark($remark)
            ->setUser($user)
            ->debit();
            
        return $this->debitTransaction;
    }

    /**
     * sets the withdrawal source (where money should be taken from)
     * 
     * @param array $from
     * @return self
     */
    public function setSource( array $from )
    {
        // NOTE: withdrawal source can either be wallet nothing else
        $source['type'] = strtolower( $from['type'] );
        $source['id'] = $from['id'] ?? null;

        if( !in_array($source['type'], self::WITHDRAW_FROM_SOURCES) ) 
            throw new WithdrawalServiceException('Invalid withdrawal source');

        if( $source['type'] === 'wallet' ) {
            $source['model'] = $this->walletService->findUserWallet( $source['id'] ?? 'naira' );
        }
        
        if( is_null($source['model']) ) throw new WithdrawalServiceException( $source['type'].' not found.');

        $source['id'] = $source['model']['id'] ?? $source['id'];

        $this->source = $source;
        return $this;
    }
    
    /**
     * sets the withdrawal destination (where money should be credited)
     * 
     * @param array $to
     * @return self
     */
    public function setDestination( array $to )
    {
        // NOTE: destination can either be wallet or bank nothing else
        $destination['type'] = strtolower($to['type']);
        $destination['id'] = $to['id'] ?? null;

        if( !in_array($destination['type'], self::WITHDRAW_TO_SOURCES) ) 
            throw new WithdrawalServiceException('Invalid withdrawal destination specified');

        if(  $destination['type'] === 'bank' ) {
            $destination['model'] = $this->bankService->find( $destination['id'] ?? null );
        }

        if( is_null($destination['model']) ) throw new WithdrawalServiceException( $destination['type'].' not found.');

        $destination['id'] = $destination['model']['id'] ?? $destination['id'];

        $this->destination = $destination;

        return $this;
    }

    /**
     * sets the withdrawal amount
     * 
     * @param float $amount
     * @return self
     */
    public function setAmount( float $amount )
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * sets the withdrawal remark
     * 
     * @param string $remark
     * @return self
     */
    public function setRemark( ?string $remark )
    {
        $this->remark = $remark ?? 'Withdrawal from '.$this->source['type'];

        return $this;
    }

    /**
     * sets the withdrawal user
     * 
     * @param App\Models\User $user
     * @return self
     */
    public function setUser( App\Models\User $user = null )
    {
        $this->user = $user ?? auth()->user();

        return $this;
    }

    /**
     * debits withdrawal source
     */
    public function debit()
    {
        DB::beginTransaction();
        try{
            $fees = 0;
            $balance = 0;
            $isSuccessful = false;
            $serviceName = $this->source['type']."Service";
            $serviceInstance = $this->$serviceName;
    
            $transaction = new Transaction([
                'user_id' => $this->user->id,
                'type' => 'withdrawal',
                'amount' => $this->amount,
                'fees' => $fees,
                'source_type' => $this->source['type'],
                'source_id' =>  $this->source['id'] ?? null,
                'destination_type' => $this->destination['type'],
                'destination_id' => $this->destination['id'] ?? null,
                'fees' => $fees,
                'balance' => $balance,
                'remark' => $this->remark,
                'status' => 'pending'
            ]);
            
            $transaction->source()->associate($this->source['model'])->save();
            $this->debitTransaction = $transaction;
    
            DB::commit();
        }catch(\Throwable $e) {
            DB::rollback();
            handleThrowable($e, 'debit');

            throw $e;
        }

        // TODO: trigger event to notify admin of pending withdrawal request

        return $this;
    }
}