<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Currency::updateOrCreate(
            [ 'code' => 'NGN' ],
            [ 
                'name' => 'Naira',
                'symbol' => '₦',
                'unicode' => 'U+20A6',
                'precision' => 2,
            ]
        );
    }
}
