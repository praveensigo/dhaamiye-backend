<?php

namespace App\Http\Controllers\android\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\android\customer\FuelStation;
use App\Models\android\customer\FuelStationStock;
use App\Models\android\customer\FuelType;
use App\Models\android\customer\CustomerOrder;
use Illuminate\Support\Facades\DB;
use App\Models\service\ResponseSender as Response;
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
            $coupon = DB::table('coupons')->select('id', 'coupon_code', 'amount', 'type', 'expiry_date', 'count', 'used_count', 'min_order_amount', 'status')
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

                } else if($request->order_amount < $coupon->min_order_amount) {
                    $message = __('customer-error.coupon_min_en',['min' => $coupon->min_order_amount]);
                    if($request->lang  == 2) {
                        $message = __('customer-error.coupon_min_so',['min' => $coupon->min_order_amount]);
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
                        $res = Response::send(true, $data, 'Success', 200);
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
                'fuel_type_ids' => 'required',
                'fuel_type_ids.*' => 'distinct|exists:fuel_types,id|numeric',
                'quantities' => 'required',
                'quantities.*' => 'numeric',
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
            $fuel_quantity_price = 0;
            //$quantities = $request->quantities;

            $fuel_type_ids = json_decode($request->fuel_type_ids, true);
            $quantities = json_decode($request->quantities, true);

            foreach($fuel_type_ids as $fuel_type) {

                $type = DB::table('fuel_station_stocks')
                        ->select('fuel_station_stocks.fuel_type_id', 'fuel_en', 'fuel_so', 'price', 'stock')
                        ->join('fuel_types', 'fuel_station_stocks.fuel_type_id', '=', 'fuel_types.id')
                        ->where('fuel_type_id', $fuel_type)
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
                            'stock_status' => 1,
                            'converted_stock_status' => 'In Stock',
                        ];
                        
                        $fuel_quantity_price = $fuel_quantity_price + ($type->price * $quantities[$i]);
                        
                    } else {
                        
                        $fuels[] = [
                            'id' => $fuel_type,
                            'fuel_en' => $type->fuel_en,
                            'fuel_so' => $type->fuel_so,
                            'quantity' => $quantities[$i],
                            'price' => $type->price,
                            'total' => $type->price * $quantities[$i],
                            'stock_status' => 1,
                            'converted_stock_status' => 'Out of Stock',
                        ];
                    }
                    $i++;
                }               
            }

            $fuel_station = FuelStation::select('fuel_stations.id', 'name_en', 'name_so', 'place', 'latitude', 'longitude',  'address', 'fuel_stations.status', 'fuel_stations.created_at')
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->active()
                ->where('role_id', 5)                
                ->with([                    

                    'favorites' => function ($query) use($auth_user_id) {
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

            $tax = $fuel_quantity_price * $settings->tax / 100;

            /************* coupon starts ***************/
            $promotion_discount = 0;
            if($request->coupon_code) {
                $coupon_code = $request->coupon_code;
                $coupon = DB::table('coupons')->select('id', 'coupon_code', 'amount', 'type', 'expiry_date', 'count', 'used_count', 'status')
                        ->where('status', 1)
                        ->where('coupon_code', $request->coupon_code)
                        ->whereRaw('used_count < count')
                        ->where('expiry_date', '>=', date('Y-m-d'))
                        // ->whereNotIn('coupon_code', function ($query) use($auth_user_id, $coupon_code) {
                        //         $query->select('coupon_code')
                        //         ->from('customer_orders')
                        //         ->where('customer_id', $auth_user_id)
                        //         ->where('coupon_code', $coupon_code);
                        //     })
                        ->first();
                if($coupon) {

                    $used = DB::table('customer_orders')
                            ->select('coupon_code')
                            ->where('customer_id', $auth_user_id)
                            ->where('coupon_code', $coupon_code)
                            ->first();
                    if($used) {
                    } else {

                        if($coupon->type == 1) {
                            if($coupon->amount > $fuel_quantity_price) {
                                $promotion_discount = $fuel_quantity_price;
                            } else {
                                $promotion_discount = $coupon->amount;
                            }

                        } else if($coupon->type == 2) {
                            $promotion_discount = $fuel_quantity_price * $coupon->amount/100;
                        }
                    }
                }
            }
            /************* coupon ends ***************/

            $other_charges = 0;

            $grand_total = $fuel_quantity_price - $promotion_discount + $delivery_charge + $tax + $other_charges;

            $data = [
                'fuel_station' => $fuel_station,
                'fuels' => $fuels,
                'total_price' => $fuel_quantity_price,
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

    /*************
    Book Now & Schedule
    @params: fuel_station_id, fuel_type_ids[], quantities[], latitude, longitude, lang
    **************/
    public function bookNowSchedule(Request $request)
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
                'fuel_type_ids' => 'required',
                'fuel_type_ids.*' => 'distinct|exists:fuel_types,id|numeric',
                'quantities' => 'required',
                'quantities.*' => 'numeric',
                'coupon_code' => 'nullable|exists:coupons,coupon_code',
                'latitude' => 'required',
                'longitude' => 'required',
                'order_type' => 'required|in:1,2',
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {

            $fuels = [];
            $order_fuels = [];
            $i = 0;
            $fuel_quantity_price = 0;
            //$quantities = $request->quantities;

            $fuel_type_ids = json_decode($request->fuel_type_ids, true);
            $quantities = json_decode($request->quantities, true);

            foreach($fuel_type_ids as $fuel_type) {

                $type = DB::table('fuel_station_stocks')
                        ->select('fuel_station_stocks.fuel_type_id', 'fuel_en', 'fuel_so', 'price', 'stock')
                        ->join('fuel_types', 'fuel_station_stocks.fuel_type_id', '=', 'fuel_types.id')
                        ->where('fuel_type_id', $fuel_type)
                        ->first();

                if($type) {                   

                    $order_fuels[] = (object)array(
                        'customer_id' => $auth_user_id,
                        'order_id' => 0,
                        'fuel_type_id' => $type->fuel_type_id,
                        'quantity' => $quantities[$i],
                        'price' => $type->price,
                        'amount' => $type->price * $quantities[$i],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    );
                        
                    $fuel_quantity_price = $fuel_quantity_price + ($type->price * $quantities[$i]);                       

                    $i++;
                }               
            }

            $fuel_station = FuelStation::select('fuel_stations.id', 'name_en', 'name_so', 'place', 'latitude', 'longitude',  'address', 'fuel_stations.status', 'fuel_stations.created_at')
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->active()
                ->where('role_id', 5)                
                ->first();

            $settings = DB::table('settings')
                        ->select('fuel_delivery_range', 'tax')
                        ->where('id', 1)
                        ->first();

            $delivery_charge = $fuel_station->distance * $settings->fuel_delivery_range;

            $tax = $fuel_quantity_price * $settings->tax / 100;

            /************* coupon starts ***************/
            $promotion_discount = 0;
            if($request->coupon_code) {

                $coupon_code = $request->coupon_code;

                $coupon = DB::table('coupons')->select('id', 'coupon_code', 'amount', 'type', 'expiry_date', 'count', 'used_count', 'status')
                        ->where('status', 1)
                        ->where('coupon_code', $request->coupon_code)
                        ->whereRaw('used_count < count')
                        ->where('expiry_date', '>=', date('Y-m-d'))
                        // ->whereNotIn('coupon_code', function ($query) use($auth_user_id, $coupon_code) {
                        //         $query->select('coupon_code')
                        //         ->from('customer_orders')
                        //         ->where('customer_id', $auth_user_id)
                        //         ->where('coupon_code', $coupon_code);
                        //     })
                        ->first();

                if($coupon) {

                    $used = DB::table('customer_orders')
                            ->select('coupon_code')
                            ->where('customer_id', $auth_user_id)
                            ->where('coupon_code', $coupon_code)
                            ->first();

                    if($used) {
                    } else {

                        if($coupon->type == 1) {
                            if($coupon->amount > $fuel_quantity_price) {
                                $promotion_discount = $fuel_quantity_price;
                            } else {
                                $promotion_discount = $coupon->amount;
                            }

                        } else if($coupon->type == 2) {
                            $promotion_discount = $fuel_quantity_price * $coupon->amount/100;
                        }
                    }
                }
            }
            /************* coupon ends ***************/

            $other_charges = 0;

            $grand_total = $fuel_quantity_price - $promotion_discount + $delivery_charge + $tax + $other_charges;

            $order = new CustomerOrder;
            $order->customer_id = $auth_user_id;
            $order->fuel_station_id = $request->fuel_station_id;
            $order->order_type = $request->order_type;
            $order->fuel_quantity_price = $fuel_quantity_price;
            $order->tax = $tax;
            $order->delivery_charge = $delivery_charge;            
            $order->promotion_discount = $promotion_discount;
            $order->other_charges = $other_charges;
            $order->total = $grand_total;
            $order->created_at = date('Y-m-d H:i:s');
            $order->updated_at = date('Y-m-d H:i:s');

            if($promotion_discount) {
                $order->coupon_code = $request->coupon_code;
            }


            if($order->save()) {

                foreach($order_fuels as $order_fuel) {
                    DB::table('customer_order_fuels')->insert(array(
                        'customer_id' => $order_fuel->customer_id,
                        'order_id' => $order->id,
                        'fuel_type_id' => $order_fuel->fuel_type_id,
                        'quantity' => $order_fuel->quantity,
                        'price' => $order_fuel->price,
                        'amount' => $order_fuel->amount,
                        'created_at' => $order_fuel->created_at,
                        'updated_at' => $order_fuel->updated_at,
                    ));
                }     

                DB::table('customer_order_address')->insert(array(
                        'customer_id' => $auth_user->user_id,
                        'order_id' => $order->id,  
                        'country_code_id' => $auth_user->country_code_id,
                        'phone' => $auth_user->mobile,
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                ));

                $data = [
                    'order' => $this->getOrder($order->id),
                ];

                $res = Response::send(true, $data, '', 200);
            } else {
                $res = Response::send(false, [], '', 400);
            }
        }

        return $res;
    }


    /*************
    Confirm Order
    @params: order_id, latitude, longitude, address, payment_type, lang
    **************/
    public function confirmOrder(Request $request)
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
                'order_id' => 'required|exists:customer_orders,id',
                //'latitude' => 'required',
                //'longitude' => 'required',
                'order_type' => 'required|in:1,2',
                'address' => 'required',
                'payment_type' => 'required',
                'delivery_date' => 'required_if:order_type,=,2|nullable|date',
                'delivery_time' => 'required_if:order_type,=,2|nullable',

            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $order = CustomerOrder::find($request->order_id);

            if($order->status == 0) {
                $order->status = 1;
                $order->pin = $this->generatePIN();
                if($request->order_type == 2) {
                    $order->status = 4;
                    $order->delivery_date = $request->delivery_date;
                    $order->delivery_time = $request->delivery_time;
                }
                $order->save();

                // DB::table('customer_order_address')->insert(array(
                //         'customer_id' => $order->customer_id,
                //         'order_id' => $order->id,
                //         'address' => $request->address,
                //         'latitude' => $request->latitude,
                //         'longitude' => $request->longitude,
                //         'special_instructions' => $request->special_instructions,
                //         'created_at' => date('Y-m-d H:i:s'),
                //         'updated_at' => date('Y-m-d H:i:s'),
                // ));

                DB::table('customer_order_address')->where('order_id',$request->order_id)->update(
                     array(
                            'address' => $request->address,
                            'special_instructions' => $request->special_instructions,
                     )
                ); 
                DB::table('customer_order_payments')->insert(array(
                        'customer_id' => $order->customer_id,
                        'order_id' => $order->id,
                        'payment_type' => $request->payment_type,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                ));

                $title_en = 'Order Placed';
                $title_so = 'Order Placed';
                $description_en = 'Your order with ID #' . $order->id  . ' has been placed successfully';
                $description_so = 'Your order with ID #' . $order->id  . ' has been placed successfully';

                DB::table('notifications')->insert(array(
                    'title_en' => $title_en,
                    'title_so' => $title_so,
                    'description_en' => $description_en,
                    'description_so' => $description_so,
                    'type' => 3,
                    'user_id' => $auth_user->id,
                    'order_id' => $order->id,
                    'date' => date('Y-m-d'),
                    'time' => date('H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ));

                $message = __('customer-success.confirm_order_en');
                if($request->lang == 2) {
                    $message = __('customer-success.confirm_order_so');
                }    

                $res = Response::send(true, [], $message, 200);

            } else {                

                $message = __('customer-error.confirm_order_en');
                if($request->lang == 2) {
                    $message = __('customer-error.confirm_order_so');
                }                 
                $res = Response::send(false, [], $message, 400);
            }

            
        }
        return $res;
    }

    /*************
    Orders listing
    @params: limit, status
    **************/
    public function index(Request $request)
    {
        $auth_user = auth('sanctum')->user();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'status' => 'nullable|numeric|in:1,2,3,4,5,6,7'
        ]);

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {

            $orders = CustomerOrder::select('customer_orders.id', 'customer_id', 'fuel_station_id', 'status', 'created_at', 'order_type', 'delivery_date', 'delivery_time', 'delivered_at')
                ->descending()
                ->where('customer_orders.customer_id', $auth_user->user_id)
                ->where('status', '!=',0)
                ->status($request->status)
                ->with([
                    // 'fuel_station', 'fuels', 
                    'fuel_station'=> function($query) use($request) {
                        $query->select('fuel_stations.id', 'name_en', 'name_so', 'place', 'latitude', 'longitude',  'address', 'fuel_stations.status', 'fuel_stations.created_at')
                        ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                        ->where('role_id', 5);

                    }, 'fuels'

                ]);
           

            $orders = $orders->paginate($request->limit);

            $data = array(
                'orders' => $orders,
            );

            $res = Response::send(true, $data, '', 200);
        }
        return $res;
    }

    /*************
    Orders Details
    @params: id, latitude, longitude
    **************/
    public function details(Request $request)
    {
        $auth_user = auth('sanctum')->user();
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:customer_orders,id',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {            

            $data = array(
                'order'=> $this->getOrder($request->id)
            );

            $res = Response::send(true, $data, '', 200);
        }
        return $res;
    }

    public function getOrder($order_id) {
        $order = CustomerOrder::select('customer_orders.*', 'address', 'country_code_id', 'phone', 'latitude', 'longitude', 'location', 'special_instructions', 'payment_type')

                ->leftjoin('customer_order_address', 'customer_orders.id', '=', 'customer_order_address.order_id')
                ->leftjoin('customer_order_payments', 'customer_orders.id', '=', 'customer_order_payments.order_id')

                ->where('customer_orders.id', $order_id)
                ->with([
                    // 'fuel_station', 'fuels', 
                    'fuel_station'=> function($query) {
                        $query->select('fuel_stations.id', 'name_en', 'name_so', 'place', 'latitude', 'longitude',  'address', 'fuel_stations.status', 'fuel_stations.created_at')
                        ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                        ->where('role_id', 5);

                    }, 'fuel_station.favorites', 'fuels', 'meter_readings',

                    'customer' => function($query) {
                        $query->join('users', 'customers.id', '=', 'users.user_id')
                        ->select('customers.id', 'name_en', 'name_so', 'email', 'mobile', 'country_code_id', 'country_code', 'customers.created_at', 'customers.status')
                        ->join('country_codes', 'users.country_code_id', '=', 'country_codes.id')
                        ->where('role_id', 3);
                    }, 
                    'driver' => function ($query) {
                        $query->select('user_id', 'name_en', 'name_so');

                    }, 'coupon'
                ])
                ->first();         

        
        return $order;
    }

    /*************
    Cancel order
    @params: id, reason, lang
    **************/
    public function cancel(Request $request)
    {
        $auth_user = auth('sanctum')->user();
        $lang =   [
                'reason.required' => __('customer-error.reason_required_en'),
        ];

        if($request->lang == 2) {
            $lang =   [
                'reason.required' => __('customer-error.reason_required_so'),
            ];
        }
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:customer_orders,id',
            'reason' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $order = CustomerOrder::find($request->id);
            
            $order->status = 6;
            $order->cancelled_at = date('Y-m-d H:i:s');
            $order->cancel_reason = $request->reason;
            if($order->save()) {

                $message = __('customer-success.cancel_order_en');

                if($request->lang  == 2) {
                    $message = __('customer-success.cancel_order_so');
                }

                $res = Response::send(true, [], $message, 200);

            } else {
                $message = __('customer-error.cancel_order_en');
                if($request->lang  == 2) {
                    $message = __('customer-error.cancel_order_so');
                }

                $res = Response::send(false, [], $message, 400);
            }
        }
        return $res;
    }

    /****** GENERATE PIN *****/
    function generatePIN() 
    {
        $chars = '0123456789';
        $len = strlen($chars);
        $pin = '';
        for ($i = 0; $i < 4; $i++) {
            $pin .= $chars[rand(0, $len - 1)];
        }
        return $pin;
    }

    /*************
    Track Driver
    @params: order_id
    **************/
    public function trackDriver(Request $request)
    {
        $auth_user = auth('sanctum')->user();
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:customer_orders,id',
        ]);

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else { 

            $order = CustomerOrder::find($request->order_id);

            if($order->status == 3) {

                if($order->driver_id) {

                    $locations = DB::table('driver_location')
                                ->select('latitude', 'longitude')
                                ->where('driver_id', $order->driver_id)
                                ->where('date', date('Y-m-d'))
                                ->orderBy('created_at')
                                ->get();

                    $distance = null;
                    $latitude = '';
                    $longitude = '';

                    $n = $locations->count();
                    if($n > 0) {
                        $order_location = DB::table('customer_order_address')->select('latitude', 'longitude')
                            ->where('order_id', $order->id)    
                            ->first();
                        $latitude = $locations[$n-1]->latitude;
                        $longitude = $locations[$n-1]->longitude;
                        $distance = $this->GetDrivingDistance($order_location->latitude, $latitude, $order->longitude, $longitude);
                    }

                    $data = array(
                        'locations'=> $locations,
                        'count' => $n,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'distance' => $distance,
                        'pin' => $order->pin,
                    );
                    $res = Response::send(true, $data, '', 200);

                } else {
                    $message = __('customer-error.not_accepted_en');
                    if($request->lang  == 2) {
                        $message = __('customer-error.not_accepted_so');
                    }

                    $res = Response::send(false, [], $message, 400);
                }
            } else {
                $message = __('customer-error.track_status_en');
                if($request->lang  == 2) {
                    $message = __('customer-error.track_status_so');
                }

                $res = Response::send(false, [], $message, 400);
            }                
        }
        return $res;
    }

    function GetDrivingDistance($lat1, $lat2, $long1,$long2)
    {
        $key = config('constants.google_map_key');
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2."%2C".$long2."&mode=driving&language=pl-PL&key=" . $key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response, true);        
    
        //return array('distance' => $dist, 'time' => $time);

        if(array_key_exists('distance', $response_a['rows'][0]['elements'][0]) ) {

            //$dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
            $dist = $response_a['rows'][0]['elements'][0]['distance']['value'];
            $time = $response_a['rows'][0]['elements'][0]['duration']['text'];
        
            //$array = array('distance' => $dist, 'time' => $time);
            //$exploded = explode(' ', $array['distance']);
            //$distance   = intval($exploded[0]);
            $distance = round($dist/1000, 2);

            return $distance;
        } else {
            return null;
        }
    }  
}
