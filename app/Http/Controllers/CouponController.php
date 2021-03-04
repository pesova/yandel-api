<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\CouponServiceInterface as CouponService;


class CouponController extends Controller
{
    /**
     * @var CouponService
     */
    private $couponService;

    /**
     * Inject Dependencies
     */

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }
    public function getCoupons(){
        $req = $this->couponService->getCoupons();

        return success( 'success', $req );
    }
}
