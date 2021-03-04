<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currency = \App\Models\Currency::all();
        if(!$currency || count($currency) < 1) throw new Exception('No currency found');

        \App\Models\Wallet::updateOrCreate(
            [ 'slug' => 'naira' ],
            [ 
                'name' => 'Naira',
                'description' => 'Naira Wallet',
                'currency' => $currency->where('code', 'NGN')->first()->id,
                'code' => 'NGN',
                'buy_rate' => 1,
                'sell_rate' => 1,
                'buy_margin' => 0,
                'sell_margin' => 0,
                'deposit_fee' => 0,
                'withdrawal_fee' => 0,
                'is_available' => true,
                'is_visible' => true
            ]
        );
        
        // initialize wallets for existing users
        $walletService = (resolve('App\Contracts\WalletServiceInterface'));
        foreach( \App\Models\User::all() as $user) $walletService->initializeWallets( $user );
    }
}
