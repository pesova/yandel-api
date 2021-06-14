<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CouponCurrencyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Coupon::all()->each(function($model, $position){
            \App\Models\CouponCurrencyType::updateOrCreate(
                [
                    'coupon_id' => $model->id,
                    'name' => 'dollar'
                ],
                [
                    'buy_rate' => 510,
                    'sell_rate' => 500,
                    'precision' => 2,
                    'is_available' => true,
                ]
            );

            \App\Models\CouponCurrencyType::updateOrCreate(
                [
                    'coupon_id' => $model->id,
                    'name' => 'euro'
                ],
                [
                    'buy_rate' => 610,
                    'sell_rate' => 600,
                    'buy_margin' => 2,
                    'sell_margin' => 2,
                    'precision' => 2,
                    'is_available' => true,
                ]
            );
        });
    }
}
