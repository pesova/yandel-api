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
        \App\Models\Coupon::updateOrCreate(
            [
                'name' => 'amazon',
                'slug' => 'amazon'
            ],
            [
                'code' => null,
                'image_url' => null,
                'is_available' => true,
                'is_visible' => true,
            ]
        );

        \App\Models\Coupon::updateOrCreate(
            [
                'name' => 'wallmart',
                'slug' => 'wallmart'
            ],
            [
                'code' => null,
                'image_url' => null,
                'is_available' => true,
                'is_visible' => true,
            ]
        );

        \App\Models\Coupon::updateOrCreate(
            [
                'name' => 'stripe',
                'slug' => 'stripe'
            ],
            [
                'code' => null,
                'image_url' => null,
                'is_available' => true,
                'is_visible' => true,
            ]
        );
    }
}
