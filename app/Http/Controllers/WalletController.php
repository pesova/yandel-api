<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\WalletServiceInterface as WalletService;

class WalletController extends Controller
{
    /**
     * @var WalletService
     */
    private $walletService;

    /**
     * Inject Dependencies
     */

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }
    
    public function listUserWallets(){
        $req = $this->walletService->listUserWallets();

        return success( 'success', $req );
    }

    public function findWallet(int $wallie_id){
        $req = $this->walletService->findUserWallet($wallie_id);

        return success( 'success', $req );
    }

    public function creditUserWallet(){
        $req = $this->walletService->credit();

        return success( 'success', $req );
    }
}
