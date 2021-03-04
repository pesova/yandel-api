<?php 

namespace App\Services;

use App\Models\Coupon;
use App\Contracts\CouponServiceInterface;



class CouponService extends BaseService implements CouponServiceInterface{


    private $coupon;

    public function __construct( 
        Coupon $coupon
    )
    {
        $this->model = $coupon;
    }

    public function getCoupons(){
        $coupon = Coupon::where('is_available', true)->where('is_visible', true)->get();

        return $coupon;
    }




}

