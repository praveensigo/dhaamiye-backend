<?php

namespace App\Http\Controllers\android\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\android\customer\FuelStation;
use App\Models\android\customer\FuelStationStock;
use App\Models\android\customer\FuelType;
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
                    ->whereRaw('used_count < count')
                    ->first();

            if ($coupon) {

                if($coupon->expiry_date < date('Y-m-d')){
                    $message = __('customer-error.coupon_expired_en');
                    if($request->lang  == 2) {
                        $message = __('customer-error.coupon_expired_so');
                    }
                    $res = Response::send(false, [], $message = $message, 422);
                } else {

                    $order = DB::table('customer_orders')
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


    /*************
    Request Fuel
    @params: fuel_station_id, fuel_type_ids[], quantities[], latitude, longitude, lang
    **************/
    public function requestFuel(Request $request)
    {        
        $auth_user = auth('sanctum')->user();
        $auth_user_id = $auth_user->user_id;
        $lang = [
            'fuel_station_id.exists' => __('customer-error.exists_en'),
            'quantities.required' => __('customer-error.quantity_required_en'),
        ];
        if($request->lang == 2) {
            $lang = [
                'fuel_station_id.exists' => __('customer-error.exists_so'),
                'quantities.required' => __('customer-error.quantity_required_so'),
            ];
        }
        $validator = Validator::make($request->all(),
            [
                'fuel_station_id' => 'required|exists:fuel_stations,id',
                'fuel_type_ids' => 'required|array',
                'fuel_type_ids.*' => 'distinct|exists:fuel_types,id|numeric',
                'quantities' => 'required|array',
                'coupon_code' => 'nullable|exists:coupons,coupon_code',
                'latitude' => 'required',
                'longitude' => 'required',
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {

            $fuels = [];
            $i = 0;
            $total = 0;
            $quantities = $request->quantities;
            foreach($request->fuel_type_ids as $fuel_type) {
                $type = DB::table('fuel_station_stocks')
                        ->select('fuel_station_stocks.fuel_type_id', 'fuel_en', 'fuel_so', 'price', 'stock')
                        ->join('fuel_types', 'fuel_station_stocks.fuel_type_id', '=', 'fuel_types.id')
                        ->first();
                if($type) {
                    if($quantities[$i] <= $type->stock) {

                        $fuels[] = [
                            'id' => $fuel_type,
                            'fuel_en' => $type->fuel_en,
                            'fuel_so' => $type->fuel_so,
                            'quantity' => $quantities[$i],
                            'price' => $type->price,
                            'total' => $type->price * $quantities[$i],
                        ];
                        
                        $total = $total + ($type->price * $quantities[$i]);
                        $i++;
                    }
                }               
            }

            $fuel_station = FuelStation::select('fuel_stations.id', 'name_en', 'name_so', 'place', 'latitude', 'longitude',  'address', 'fuel_stations.status', 'fuel_stations.created_at', DB::raw("ROUND(6371 * acos(cos(radians(" . floatval($request->latitude) . ")) 
                * cos(radians(fuel_stations.latitude)) 
                * cos(radians(fuel_stations.longitude) - radians(" . floatval($request->longitude) . ")) 
                + sin(radians(" .$request->latitude. ")) 
                * sin(radians(fuel_stations.latitude))), 2) AS distance"))
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->active()
                ->where('role_id', 5)                
                ->with([                    

                    'favourites' => function ($query) use($auth_user_id) {
                        $query->select('customers.id', 'name_en', 'name_so', 'customers.created_at', 'customers.status')
                        ->where('customer_favorite_stations.customer_id', '=', $auth_user_id);
                    },
                ])
                ->first();

            $settings = DB::table('settings')
                        ->select('fuel_delivery_range', 'tax')
                        ->where('id', 1)
                        ->first();

            $delivery_charge = $fuel_station->distance * $settings->fuel_delivery_range;

            $tax = $total * $settings->tax / 100;

            /************* coupon starts ***************/
            $promotion_discount = 0;
            if($request->coupon_code) {
                $coupon = DB::table('coupons')->select('id', 'coupon_code', 'amount', 'type', 'expiry_date', 'count', 'used_count', 'status')
                        ->where('status', 1)
                        ->where('coupon_code', $request->coupon_code)
                        ->whereRaw('used_count < count')
                        ->where('expiry_date', '>=', date('Y-m-d'))
                        ->whereNotIn('coupon_code', function ($query) use($auth_user_id) {
                                $query->select('coupon_code')
                                ->from('customer_orders')
                                ->where('customer_id', $auth_user_id);
                            })
                        ->first();
                if($coupon) {

                    if($coupon->type == 1) {
                        if($coupon->amount > $total) {
                            $promotion_discount = $total;
                        } else {
                            $promotion_discount = $coupon->amount;
                        }

                    } else if($coupon->type == 2) {
                        $promotion_discount = $total * $coupon->amount/100;
                    }
                }
            }
            /************* coupon ends ***************/

            $other_charges = 0;

            $grand_total = $total - $promotion_discount + $delivery_charge + $tax + $other_charges;

            $data = [
                'fuel_station' => $fuel_station,
                'fuels' => $fuels,
                'total_price' => $total,
                'coupon_code' => $request->coupon_code,
                'promotion_discount' => $promotion_discount,
                'delivery_charge' => $delivery_charge,
                'tax' => $tax,
                'other_charges' => $other_charges,
                'grand_total' => $grand_total,
            ];

            $res = Response::send(true, $data, '', 200);
        }

        return $res;
    }
}
