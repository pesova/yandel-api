<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface as PaymentGateway;
use App\Contracts\BankServiceInterface;
use App\Contracts\AuthServiceInterface;
use App\Exceptions\BankServiceException;
use App\Models\BankUser;
use App\Models\Bank;
use Carbon\Carbon;
use Auth;

/**
 * Inherits basic CRUD functionalities from BaseService
 */
class BankService extends BaseService implements BankServiceInterface
{
    /**
     * Class constants
     */
    const MULTIPLE_BANK_NOT_ALLOWED = 'You can only add one withdrawal bank account.';
    const ACCOUNT_NAME_MISMATCH = 'Oops.. It seems this bank does not belong to you.';
    const INVALID_BANK_ACCOUNT = "Oops.. Seems this account is invalid";

    /**
     * @var App\Models\Bank $bank
     * @var App\Models\BankUser $bankUser
     */
    private $bank, $bankUser, $paymentGateway, $authService;

    /**
     * Inject Dependencies
     */
    public function __construct(
        Bank $bank, BankUser $bankUser, PaymentGateway $paymentGateway, AuthService $authService
    )
    {
        $this->bank = $bank;
        $this->model = $bankUser;
        $this->authService = $authService;
        $this->paymentGateway = $paymentGateway;
    }

    /*
    |--------------------------------------------------------------------------
    | NIGERIAN BANK LISTING
    |--------------------------------------------------------------------------
    */

    /**
     * Updates the list of Nigerian banks
     *
     * @return object
     */
    public function updateBankList(): object
    {
        $banks = $this->paymentGateway->fetchBanks();
        
        foreach($banks['data'] as $bank){
            $logo_url = null;
            if(! empty($bank['bankLogo'])) $logo_url = saveImage($bank['bankLogo'], $bank['nipCode'], 'bank-logos');
            $this->bank->updateOrCreate(
                [ 'cbn_code' => $bank['code'], 'nip_code' => $bank['nipCode'] ?? null ],
                [ 'name' => $bank['name'], "logo_url" => $logo_url ]
            );
        }

        return $this->bank->all();
    }

    /**
     * Enquire on a bank account
     *
     * @return array
     */
    public function recipientEnquiry(string $nuban, string $code): array
    {
        $enquiry = $this->paymentGateway->recipientEnquiry($nuban, $code);
        if(! empty($enquiry['data']['bank_id']) ) unset($enquiry['data']['bank_id']);

        return $enquiry;
    }

    /**
     * List Banks
     *
     * @param string|null $nuban
     *
     * @return object
     */
    public function listBanks(): ?object
    {
        $banks = $this->bank->all();
        if( $banks->isEmpty() ) $banks = $this->updateBankList();

        return $banks;
    }

    /**
     * Find a Banks
     *
     * @param string $bankCode
     *
     * @return object
     */
    public function findBank(string $bankCode = null): ?object
    {
        return $this->bank->whereColumns(['cbn_code', 'nip_code'], $bankCode)->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | USER BANK ACCOUNT MANAGEMENT
    |--------------------------------------------------------------------------
    */

    /**
     * saves a new bank account for user
     *
     * @param array $params
     *
     * @return object
     *
     * @throws BankUserServiceException
     */
    public function addBankAccount( array $params )
    {
        // bounce if only single bank is allowed and user already has a bank
        // TODO: kick in complaince checks if any
        if( ! config('custom.app.ALLOW_MULTI_USER_BANK') && $this->userHasBank() )
            throw new BankServiceException(self::MULTIPLE_BANK_NOT_ALLOWED);

        // check if same bank exists or has been deleted, then restore
        $isSaved = $this->findByNuban( $params['nuban'], true );
        if( $isSaved && $isSaved->restore()) return $isSaved;

        // ensure selected bank/bank code is  valid
        $bank = $this->findBank($params['code']);

        // re-query account details and save returned data
        $isValidAccount = $this->recipientEnquiry( $params['nuban'], $params['code'] );

        if( ! isset($isValidAccount['data']) ) throw new BankServiceException(self::INVALID_BANK_ACCOUNT);

        // ensure bank name is same as name on bvn
        if(
            ! empty(Auth::user()->name)
            && strtolower($isValidAccount['data']['account_name']) !== Auth::user()->name
        ) throw new BankServiceException(self::ACCOUNT_NAME_MISMATCH);

        // save bank if no errors so far
        auth()->user()->name = $isValidAccount['data']['account_name'];
        auth()->user()->save();

        return Auth::user()->bankAccounts()->create(
            array_merge(
                $params, ['name' => $isValidAccount['data']['account_name'], 'bank_id' => $bank['id']]
            )
        );
    }

    /**
     * Delete bank account
     *
     * @param array $params
     *
     * @return object|null
     */
    public function deleteBankAccount( int $bank_id, array $params ): ?object
    {
        return parent::delete($bank_id);
    }

    /**
     * returns a list of banks belonging to a user
     *
     * @return null|object
     */
    public function listBankAccounts(): ?object
    {
        return Auth::user()->bankAccounts()->orderBy('updated_at', 'DESC')->get();
    }

    /**
     * finds a bank account by id
     *
     * @param string $bank_id
     *
     * @return object
     */
    public function findBankAccount( int $bank_id, bool $withTrashed = false ): ?object
    {
        return Auth::user()->bankAccounts()->whereId($bank_id)
                ->when( $withTrashed, function ($query){
                    $query->withTrashed();
                })->firstOrFail();
    }

    /**
     * finds a bank account by account number
     *
     * @param string $account
     * @param bool $withTrashed
     *
     * @return bool
     */
    public function findByNuban( string $account, bool $withTrashed = false ): ?object
    {
        $bank = Auth::user()->bankAccounts()->where('nuban', $account)
                ->when( $withTrashed, function ($query){
                    $query->withTrashed();
                })->first();
        return $bank;
    }

    /**
     * checks if a user has a bank account
     *
     * @param bool $return - determines if model or boolean is returned
     *
     * @return bool|collection
     */
    public function userHasBank( bool $return = false, $user = null ): bool
    {
        $user = $user ?? Auth::user();
        $userHasBank = $user->bankAccounts()->first();
        if( $return ) return $userHasBank->toArray();

        return $userHasBank ? true : false;
    }

}
