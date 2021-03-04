<?php

namespace App\Services;

use App\Exceptions\WalletServiceException;
use App\Contracts\WalletServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Events\WalletCredited;
use App\Events\WalletDebited;
use App\Models\WalletUser;
use App\Models\Wallet;
use App\Models\User;
use Carbon\Carbon;
use Auth;
use DB;

/**
 * Wallet Service focus on retrieving, crediting and debiting wallet 
 * (no edit or  delete allowed here)
 */
class WalletService extends BaseService implements WalletServiceInterface
{
    /**
     * Class constants
     */
    const INVALID_WALLET_TYPE = 'Invalid wallet type';
    const UNAUTHORISED_WALLET = 'Unauthorised wallet';
    const INSUFFICIENT_BALANCE = 'Insufficient balance in selected wallet';

    /**
     * @var App\Models\WalletUser $wallet
     * @var UserServiceInterface $userService
     */
    private $wallet, $userService;

    /**
     * Inject Dependencies
     */
    public function __construct( Wallet $wallet, WalletUser $walletUser, UserServiceInterface $userService )
    {
        $this->model = $wallet;
        $this->wallet = $wallet;
        $this->walletUser = $walletUser;
        $this->userService = $userService;
    }

    /**
     * Sets up current default wallets for a new user
     * 
     * @param string|int|App\Model\User
     * @return bool
     */
    public function initializeWallets(User $user)
    {
        DB::beginTransaction();
        try{
            foreach($this->wallet->all() as $wallet){
                if($user->userWallets()->whereWalletId($wallet->id)->exists()) continue;
                $wallet->walletUsers()->create(['user_id' => $user->id]);
            }

            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            handleThrowable($e, 'auth', 'Wallet Initialization', $user);
            // TODO: notify dev
        }

        return true;
    }

    /**
     * Returns all wallets of a user
     */
    public function listUserWallets($user = null)
    {
        if($user) $user = $this->userService->find($user);
        $user = $user ?? Auth::user();

        return $user->userWallets()->join('wallets', 'wallets.id', 'wallet_users.wallet_id')
                    ->where('wallets.is_public', true)
                    ->with('currency:id,name,code,symbol,unicode')
                    ->select('wallets.*', 'wallet_users.*')
                    ->get();
    }

    /**
     * Returns a selected wallet of a user
     * 
     * @param int|string $wallet
     */
    public function findUserWallet($wallet, $user = null): WalletUser
    {
        if($user) $user = $this->userService->find($user);
        $user = $user ?? Auth::user();

        if(is_numeric($wallet)) $wallet = (int) $wallet;

        if(is_int($wallet) || is_string($wallet)){
            $wallet = $user->userWallets()->join('wallets', 'wallets.id', 'wallet_users.wallet_id')
                    ->when(!is_int($wallet) && is_string($wallet), function($query) use ($wallet){
                        $query->whereColumns(['name', 'slug'], $wallet);
                    })
                    ->when(is_int($wallet), function($query) use ($wallet){
                        $query->where('wallet_users.id', $wallet);
                    })
                    ->with('currency:id,name,code,symbol,unicode')
                    ->select('wallets.*', 'wallet_users.*')
                    ->firstOrFail();
        }
        
        if(!$wallet instanceof WalletUser) throw new WalletServiceException(self::INVALID_WALLET_TYPE);

        return $wallet;
    }

    /**
     * checks if a specified card belongs to specified user
     * 
     * @return bool
     */
    public function belongsToUser( int $wallet_id, $user = null)
    {
        $user = $user ?? Auth::user();
        try{
            return $this->findUserWallet($wallet_id, $user) ? true : false;
        }catch(\Throwable $e){
            return false;
        }
    }

    /**
     * Funds a selected wallet
     * 
     * @param int|WalletUser $wallet
     * @param float $amount
     * @param string $remark
     * 
     * @return Wallet
     */
    public function credit(WalletUser $wallet, float $amount)
    {
        $response = [
            'amount' => $amount, 
            'fees' => 0,
            'status' => 'pending',
            'gateway_response' => ''
        ];

        DB::beginTransaction();
        try{
            // TODO: how do we ensure intended credit currency matches wallets currency
            
            $wallet->available_balance += $amount;
            $wallet->book_balance += $amount;
            $wallet->save();

            $response['status'] = 'success';
            $response['gateway_response'] = 'successful';

            DB::commit();
        }catch( \Throwable $e ){
            DB::rollback();
            handleThrowable($e, 'credit');
            throw $e;
        }
        
        if($wallet->wallet->is_public) event( new WalletCredited($wallet, $amount) );

        return array_merge($response, $wallet->toArray() );
    }

    /**
     * Debits a selected wallet
     * 
     * @param string|int|WalletUser $wallet
     * @param float $amount
     * @param string $remark
     * 
     * @return Wallet
     */
    public function debit(WalletUser $wallet, float $amount)
    {
        $response = [
            'amount' => $amount, 
            'fees' => 0,
            'status' => 'pending',
            'gateway_response' => ''
        ];

        DB::beginTransaction();
        try{
            // TODO: handle case of withdraewal from referral earnings wallet
            // ensure amount meets withdrawable referral bonus balance
             
            if($wallet->available_balance < $amount) 
                throw new WalletServiceException(self::INSUFFICIENT_BALANCE);

            $wallet->available_balance -= $amount;
            $wallet->save();
            
            // TODO: deterine when and where below action should take place
            // push to cba then credit naira wallet if successful
            // Note: not all wallet credits / debits should be pushed to mifos

            $response['status'] = 'success';
            $response['gateway_response'] = 'successful';

            DB::commit();
        }catch( \Throwable $e ){
            DB::rollback();
            handleThrowable($e, 'debit');
            throw $e;
        }

        if($wallet->wallet->is_public) event( new WalletDebited($wallet, $amount) );

        return array_merge($response, $wallet->toArray() );
    }

    /**
     * Liquidate a selected wallet
     * 
     * @param string|int|Wallet $wallet
     * 
     * @return float|array
     */
    public function getTotalWithdrawableAmount($wallet)
    {
        $wallet = $this->getOwnWallet($wallet);

        // TODO: analyze actual totoal withdrawable amount

        return $wallet->available_balance;
    }

    /**
     * Gets the balance on a selected wallet
     * 
     * @param int|WalletUser $wallet
     * 
     * @return Wallet
     */
    public function balance(WalletUser $wallet, string $type='available_balance'): float
    {
        return $wallet->$type;
    }

    /**
     * Returns the total wallet balance
     * 
     * @param \App\Models\User $user
     * 
     * @return object
     */
    public function totalBalance($user = null)
    {
        $user = $user ?? auth()->user();
        return []; // TODO
    }

}