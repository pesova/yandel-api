<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $availableCoupons = [
            'amazon', 'apple', 'ebay', 'google',
            'itunes', 'nike', 'steam', 'xbox'
        ];

        foreach($availableCoupons as $coupon){
            \App\Models\Coupon::updateOrCreate(
                [
                    'name' => $coupon,
                    'slug' => $coupon
                ],
                [
                    'code' => $coupon,
                    'image_url' => $coupon.'.jpeg',
                    'is_available' => true,
                    'is_visible' => true,
                ]
            );
        }
    }
}
