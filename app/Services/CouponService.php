<?php 

namespace App\Services;

use App\Models\Order;
use App\Models\Coupon;
use App\Models\CouponCurrencyType;
use App\Events\OrderCreated;
use App\Contracts\CouponServiceInterface;
use App\Exceptions\CouponServiceException;

class CouponService extends BaseService implements CouponServiceInterface{


    private $coupon, $order, $couponCurrencyType;

    public function __construct( Coupon $coupon, Order $order, CouponCurrencyType $couponCurrencyType )
    {
        $this->model = $coupon;
        $this->order = $order;
        $this->couponCurrencyType = $couponCurrencyType;
    }

    public function listCoupons()
    {
        $coupons = $this->model
                ->where('is_available', true)
                ->where('is_visible', true)
                ->get();

        return $coupons;
    }

    public function sellCoupon(array $params)
    {
        $user = auth()->user();
        $coupon = $this->model->whereId($params['coupon_id'])->firstOrFail();

        $couponCurrencyType = $coupon->currencyTypes()
                            ->whereId($params['currency_type_id'])
                            ->firstOrFail();

        $fee = 0;
        $margin = $couponCurrencyType->sell_margin/100;
        $rate = $couponCurrencyType->sell_rate - ($margin * $couponCurrencyType->sell_rate);

        // assumption is that payment will always be in naira
        $totalPayable = $rate * $params['units'];

        $reference = random_strings();
        
        if( (float) $rate !== (float) $params['rate']){
            throw new CouponServiceException("Rate has changed");
        }

        if($params['coupon_front']) {
            $couponFront = saveImage($params['coupon_front'], $user->id.'-'.$reference.'-front', 'coupons');
        }
        if($params['coupon_back']) {
            $couponBack = saveImage($params['coupon_back'], $user->id.'-'.$reference.'-back', 'coupons');
        }
        
        $order = $user->orders()->create([
            'reference'=> $reference,
            'mode'=> 'sell',
            'coupon_id'=> $coupon->id,
            'coupon_type'=> $params['coupon_type'],
            'coupon_currency_type_id'=> $couponCurrencyType->id,
            'ecode'=> $params['ecode'] ?? null,
            'coupon_front'=> $couponFront ?? null,
            'coupon_back'=> $couponBack ?? null,
            'units'=> $params['units'] ?? null,
            'rate'=> $rate,
            'total_payable'=> $totalPayable,
            'fee'=> $fee,
            'remark'=>$param['remark'] ?? null,
            'status'=>'pending'
        ]);

        event( new OrderCreated($order));
        
        return $order;
    }
}

