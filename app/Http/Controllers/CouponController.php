<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Coupon\SellCouponRequest;
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
    
    public function listCoupons(){
        $req = $this->couponService->listCoupons();

        return success( 'success', $req );
    }

    public function sellCoupon(SellCouponRequest $request)
    {
        try{
            $req = $this->couponService->sellCoupon($request->all());
        }
        catch(\Throwable $e){
            return error($e->getMessage() ?? 'Coupon sell request was not successful');
        }

        return success( 'success', $req );
    }

}
