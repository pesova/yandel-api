<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'Itunes',
            'slug' => 'itunes',
            'buy_rate' => 100,
            'sell_rate' => 120,
            'buy_margin' => 1,
            'sell_margin' => 2
        ];
    }
}
