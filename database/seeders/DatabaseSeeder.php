<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CurrencySeeder::class);
        $this->call(WalletSeeder::class);
        // $this->call(BankSeeder::class);

        if(in_array( app()->environment(), ['local', 'develop'] )) {
            $this->call(CouponSeeder::class);
            $this->call(CouponCurrencyTypeSeeder::class);
        }
        
    }
}
