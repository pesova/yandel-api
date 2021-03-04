<?php

namespace App\Services;

use DB;
use Auth;
use App\Models\User;
use App\Contracts\DepositServiceInterface;
use App\Exceptions\DepositServiceException;
use App\Contracts\CardServiceInterface as CardService;
use App\Contracts\UserServiceInterface as UserService;
use App\Contracts\WalletServiceInterface as WalletService;

/**
 * Inherits basic CRUD functionalities from BaseService
 */
class DepositService extends BaseService implements DepositServiceInterface
{
    private $cardService, $userService, $walletService;
    private $source, $destination, $amount, $remark, $user;
    private $debitTransaction, $creditTransaction;
    
    const DEPOSIT_TO_SOURCES = ['wallet'];
    const DEPOSIT_FROM_SOURCES = ['wallet', 'card'];
    const INSUFFICIENT_BALANCE = 'Insufficient balance';
    
    /**
     * Inject Dependencies
     */
    public function __construct(
        CardService $cardService,
        UserService $userService,
        WalletService $walletService
    )
    {
        $this->cardService = $cardService;
        $this->userService = $userService;
        $this->walletService = $walletService;
    }

    /**
     * Handle deposit transaction processing
     * 
     * @param array $from
     * @param array $to
     * @param float $amount
     * @param ?string $remark
     * 
     * @return object
     */
    public function deposit( array $from, array $to, float $amount, ?string $remark, User $user = null)
    {
        $this->setSource($from)
            ->setDestination($to)
            ->setAmount($amount)
            ->setRemark($remark)
            ->setUser($user)
            ->createCreditTransaction()
            ->attemptDebit();
            
        return $this->getCreditTransaction();
    }

    /**
     * sets the deposit source (where money should be taken from)
     * 
     * @param array $from
     * @return self
     */
    public function setSource( array $from )
    {
        // NOTE: deposit source can either be wallet or card nothing else
        $source['type'] = strtolower( $from['type'] );
        $source['id'] = $from['id'] ?? null;

        if( ! in_array($source['type'], self::DEPOSIT_FROM_SOURCES) ) 
            throw new DepositServiceException('Invalid deposit source');

        if( $source['type'] === 'wallet' ) {
            $source['model'] = $this->walletService->findUserWallet( $source['id'] ?? 'naira' );
        }
        
        if(  $source['type'] === 'card' ) {
            if($source['id']) $source['model'] = $this->cardService->find( $source['id'] ?? null );
        }
        
        if( empty($source['model']) && $source['type'] !== 'card') 
            throw new DepositServiceException( $source['type'].' not found.');

        $source['id'] = $source['model']['id'] ?? $source['id'];

        $this->source = $source;

        return $this;
    }
    
    /**
     * sets the deposit destination (where money should deposited)
     * 
     * @param array $to
     * @return self
     */
    public function setDestination( array $to )
    {
        // NOTE: destination can either be wallet nothing else
        $destination['type'] = strtolower($to['type']);
        $destination['id'] = $to['id'] ?? null;

        if( ! in_array($destination['type'], self::DEPOSIT_TO_SOURCES) ) 
            throw new DepositServiceException('Invalid deposit destination specified');

        if( $destination['type'] === 'wallet' ) {
            $destination['model'] = $this->walletService->findUserWallet( $destination['id'] ?? 'naira' );
        }

        if( is_null($destination['model']) ) throw new DepositServiceException( $destination['type'].' not found.');

        $destination['id'] = $destination['model']['id'] ?? $destination['id'];

        $this->destination = $destination;

        return $this;
    }

    /**
     * sets the deposit amount
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
     * sets the deposit remark
     * 
     * @param string $remark
     * @return self
     */
    public function setRemark( ?string $remark )
    {
        $this->remark = $remark ?? 'Deposit from '.$this->source['type'];

        return $this;
    }

    /**
     * sets the deposit user
     * 
     * @param App\Models\User $user
     * @return self
     */
    public function setUser( User $user = null )
    {
        $this->user = $user ?? auth()->user();

        return $this;
    }
    
    /**
     * creates a pending deposit transaction
     *
     * @param array $params
     * @return self
     */
    public function createCreditTransaction()
    {
        DB::beginTransaction();
        try{
            $this->creditTransaction = $this->destination['model']->deposits()->create([
                'user_id' => $this->user->id,
                'type' => 'deposit',
                'amount' => $this->amount,
                'fees' => $fees ?? 0,
                'source_type' => $this->source['type'],
                'source_id' =>  $this->source['model']['id'] ?? null,
                'balance' => ($this->destination['model']['available_balance']
                            ?? $this->destination['model']['amount_saved']
                            ?? $this->destination['model']['balance']) + (float) $this->amount,
                'remark' => $this->remark,
                'status' => 'pending'
            ]);

            DB::commit();
        }catch(\Throwable $e) {
            DB::rollback();
            handleThrowable($e, 'deposit');

            throw $e;
        }

        return $this;
    }

    public function attemptDebit()
    {
        $fees;
        $isSuccessful = false;
        $gatewayResponse = null;
        $sourceServiceName = $this->source['type']."Service";
        $sourceServiceInstance = $this->$sourceServiceName;

        try{
            // debit source
            $debit = $sourceServiceInstance->debit( 
                $this->source['model'], $this->amount, $this->creditTransaction->reference 
            );

            // bounce if debit fails
            if( isset($debit["status"]) && $debit["status"] !== "success"){
                throw new DepositServiceException($debit["gateway_response"] ?? "debit failed");
            }

            $isSuccessful = true;
            $fees = $debit["fees"] ?? 0;
            $gatewayResponse = $debit["gateway_response"] ?? "debit successful";
            $this->debitTransaction = $debit;
            
        }catch(\Throwable $e){
            $isSuccessful = false;
            $gatewayResponse = $e->getMessage() ?? "debit failed";
        }
        
        if($this->source['type'] === 'wallet'){
            $transaction = $this->source['model']->withdrawals()->create([
                'user_id' => $this->user->id,
                'type' => 'withdrawal',
                'destination_type' => $this->destination['type'],
                'destination_id' => $this->destination['id'] ?? null,
                'amount' => $this->amount,
                'fees' => $fees ?? 0,
                'balance' => $balance ?? 0,
                'remark' => $this->remark,
                'status' => $isSuccessful ? 'success' : 'failed',
                'gateway_response' => $gatewayResponse
            ]);
        }
        
        $this->creditTransaction->update([
            'fees' => $fees ?? 0,
            'status' => $isSuccessful ? 'success' : 'failed',
            'gateway_response' => $gatewayResponse
        ]);

        if(! $isSuccessful) throw new DepositServiceException($gatewayResponse ?? 'Deposit failed');

        // debit is successfull at this point so credit desination
        $destinationServiceName = $this->destination['type']."Service";
        $destinationServiceInstance = $this->$destinationServiceName;
        $credit = $destinationServiceInstance->credit( 
            $this->destination['model'], $this->amount, $this->creditTransaction->reference 
        );

        return $this;
    }

    public function getDebitTransaction()
    {
        return $this->debitTransaction;
    }

    public function getCreditTransaction()
    {
        return $this->creditTransaction;
    }
    
    /**
     * Checks to confirm status of a deposit transaction
     * usually will be used in card-related transactions
     */
    // TODO: systemTransaction can be integer or model
    public function validate( $systemTransaction )
    {
        if($systemTransaction->source_type === 'card'){
            $gatewayTransaction = $this->cardService->verifyTransaction( $systemTransaction->reference );
            if($gatewayTransaction['status'] ===  'success') {
                // save card
                $card = $this->cardService->saveCard( $gatewayTransaction );
                
                // update deposit source
                // TODO: Change to $systemTransaction->source()->attach($card);
                $systemTransaction->source_type = 'card';
                $systemTransaction->source_id = $card->id;

            }
        }else{
            $gatewayTransaction = $systemTransaction; // TODO
        }

        // if same with transaction status on system, return transaction on system
        if( $systemTransaction->status ===  $gatewayTransaction['status'] ) return $systemTransaction;
        
        // else update transaction on system with gateway and return system transaction
        if($gatewayTransaction['status'] ===  'success'){
            // credit destination (wallet)
            $destinationType = $systemTransaction->destination_type;
            $destinationId = $systemTransaction->destination_id;
            $destinationService = $destinationType.'Service';
            $destination = $destinationType == 'wallet'
                        ? $this->$destinationService->findUserWallet($destinationId)
                        : $this->$destinationService->find($destinationId);
            $destination = $this->$destinationService->credit($destination, $gatewayTransaction['amount']);

            $systemTransaction->amount = $gatewayTransaction['amount'];
            $systemTransaction->fees = $gatewayTransaction['fees'];
            $systemTransaction->balance = $destination['available_balance'] 
                                        ?? $destination['amount_saved'] 
                                        ?? $destination['balance'];
        }
            
        $systemTransaction->status = $gatewayTransaction['status'];
        $systemTransaction->gateway_response = $gatewayTransaction['gateway_response'];
        $systemTransaction->save();
        
        return $systemTransaction;
    }

}