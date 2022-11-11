<?php

namespace App\Http\Controllers\android\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\android\customer\FuelStation;
use App\Models\android\customer\FuelStationStock;
use Illuminate\Support\Facades\DB;
use App\Models\Service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Validator;

class OrderController extends Controller
{
    /*************
    Check if quantity exceeds stock
    @params: fuel_station_id, fuel_type_id, quantity, lang
    **************/
    public function isQuantityInStock(Request $request)
    {        
        
        $validator = Validator::make($request->all(),
            [
                'fuel_station_id' => 'required|exists:fuel_stations,id',
                'fuel_type_id' => 'required|exists:fuel_types,id',
                'quantity' => 'required|numeric'
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $fuel = FuelStationStock::select('fuel_station_id', 'fuel_type_id', 'price', 'stock')
                    ->where('fuel_station_id', $request->fuel_station_id)
                    ->where('fuel_type_id', $request->fuel_type_id)
                    ->first();

            if ($fuel) {

                if($request->quantity <= $fuel->stock){
                    $message = __('customer-success.in_stock_en');
                    if($request->lang  == 2) {
                        $message = __('customer-success.in_stock_so');
                    }
                    $res = Response::send(true, [], $message = $message, 200);
                } else {
                
                    $message = __('customer-error.out_of_stock_en');
                    if($request->lang  == 2) {
                        $message = __('customer-error.out_of_stock_so');
                    }
                    $res = Response::send(false, [], $message = $message, 200);
                }

            } else {
                
                $message = __('customer-error.exists_en');
                if($request->lang  == 2) {
                    $message = __('customer-error.exists_so');
                }
                $res = Response::send(false, [], $message = $message, 422);
                    
            }
        }
        return $res;
    }


    /*************
    Check if coupon is valid
    @params: coupon_code, order_amount, lang
    **************/
    public function applyCoupon(Request $request)
    {        
        $auth_user = auth('sanctum')->user();
        $lang = [
                    'coupon_code.exists' => __('customer-error.coupon_exists_en'),
                ];
        if($request->lang == 2) {
            $lang = [
                    'coupon_code.exists' => __('customer-error.coupon_exists_so'),
                ];
        }
        $validator = Validator::make($request->all(),
            [
                'coupon_code' => 'required|exists:coupons,coupon_code',
                'order_amount' => 'required|numeric'
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $coupon = DB::table('coupons')->select('id', 'coupon_code', 'amount', 'type', 'expiry_date', 'count', 'used_count', 'status')
                    ->where('status', 1)
                    ->where('coupon_code', $request->coupon_code)
                    ->where('used_count < ', 'count')
                    ->first();

            if ($coupon) {

                if($coupon->expiry_date < date('Y-m-d')){
                    $message = __('customer-error.coupon_expired_en');
                    if($request->lang  == 2) {
                        $message = __('customer-error.coupon_expired_so');
                    }
                    $res = Response::send(false, [], $message = $message, 422);
                } else {

                    $order = DB:table('customer_orders')
                            ->select('id')
                            ->where('coupon_code', $request->coupon_code)
                            ->where('customer_id', $auth_user->user_id)
                            ->first();

                    if($order) {
                        $message = __('customer-error.coupon_used_en');
                        if($request->lang  == 2) {
                            $message = __('customer-error.coupon_used_so');
                        }
                        $res = Response::send(false, [], $message = $message, 422);
                    } else {
                        $promotion_discount = 0;
                        if($coupon->type == 1) {
                            if($coupon->amount > $request->order_amount) {
                                $promotion_discount = $request->order_amount;
                            } else {
                                $promotion_discount = $coupon->amount;
                            }

                        } else if($coupon->type == 2) {
                            $promotion_discount = $request->order_amount * $coupon->amount/100;
                        }

                        $data = [
                            'coupon_code' => $request->coupon_code,
                            'order_amount' => $request->order_amount,
                            'promotion_discount' => $promotion_discount
                        ];
                        $res = Response::send(false, $data, '', 422);
                    }               
                }

            } else {
                
                $message = __('customer-error.coupon_exists_en');
                if($request->lang  == 2) {
                    $message = __('customer-error.coupon_exists_so');
                }
                $res = Response::send(false, [], $message = $message, 422);
                    
            }
        }
        return $res;
    }


}
