<?php

namespace App\Http\Controllers\android\driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\android\driver\CustomerOrder;
use App\Models\android\driver\CustomerOrderPayment;
use App\Models\android\driver\CustomerOrderFuel;
use App\Models\android\driver\TruckFuel;
use App\Models\android\driver\Driver;
use Illuminate\Support\Facades\DB;
use App\Models\service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Validator;

class HomeController extends Controller
{
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

            $orders = CustomerOrder::select('customer_orders.id', 'customer_orders.customer_id', 'fuel_station_id', 'customer_orders.status','customer_order_address.address as order_address', 'country_code_id', 'phone', 'customer_order_address.latitude as order_latitude', 'customer_order_address.longitude as order_longitude', 'location', 'total', 'customer_orders.created_at', 'fuel_stations.latitude as station_latitude', 'fuel_stations.longitude as station_longitude', 'delivery_date', 'delivery_time', 'delivered_at')
                ->join('customer_order_address', 'customer_orders.id', '=', 'customer_order_address.order_id')
                ->join('fuel_stations', 'fuel_station_id', '=', 'fuel_stations.id')
                ->descending()
                ->where('customer_orders.driver_id', $auth_user->user_id)
                ->where('customer_orders.status', '!=',0)
                ->status($request->status)
                ->with([
                    'fuels', 'customer' => function ($query) {
                        $query->select('user_id', 'name_en', 'name_so');
                    }
                ]);

            $driver = Driver::select('online')
                        ->where('id', $auth_user->user_id)
                        ->first();
           

            $orders = $orders->paginate($request->limit);

            $data = array(
                'orders' => $orders,
                'online' => $driver->online == 1 ? true:false,
            );

            $res = Response::send(true, $data, '', 200);
        }
        return $res;
    }

    /*************
    Orders Details
    @params: id
    **************/
    public function details(Request $request)
    {
        $auth_user = auth('sanctum')->user();
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:customer_orders,id',
        ]);

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {  

            $order = CustomerOrder::select('customer_orders.*', 'address', 'country_code_id', 'phone', 'latitude', 'longitude', 'location', 'special_instructions', 'payment_type')

                ->leftjoin('customer_order_address', 'customer_orders.id', '=', 'customer_order_address.order_id')
                ->leftjoin('customer_order_payments', 'customer_orders.id', '=', 'customer_order_payments.order_id')

                ->where('customer_orders.id', $request->id)
                ->with([
                   'fuels', 'meter_readings',

                    'customer' => function($query) {
                        $query->select('user_id', 'name_en', 'name_so', 'image');

                    }, 'review'
                ])
                ->first();                   

            $data = array(
                'order'=> $order
            );

            $res = Response::send(true, $data, '', 200);
        }
        return $res;
    }

    /*************
    Accept order
    @params: order_id, lang
    **************/
    public function acceptOrder(Request $request)
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
            if($order->status == 1 || $order->status == 4) {
                $driver = Driver::find($auth_user->user_id);
                
                $order->status = 2;
                $order->driver_id = $auth_user->user_id;
                $order->truck_id = $driver->truck_id;
                $order->accepted_at = date('Y-m-d H:i:s');

                if($order->save()) {

                    $message = __('driver-success.accept_order_en');
                    if($request->lang  == 2) {
                        $message = __('driver-success.accept_order_so');
                    }

                    $res = Response::send(true, [], $message, 200);

                } else {
                    $message = __('driver-error.accept_order_en');
                    if($request->lang  == 2) {
                        $message = __('driver-error.accept_order_so');
                    }

                    $res = Response::send(false, [], $message, 400);
                }
            } else {
                $message = __('driver-error.status_order_en');
                if($request->lang  == 2) {
                    $message = __('driver-error.status_order_so');
                }
                $res = Response::send(false, [], $message, 400);
            }
        }
        return $res;
    }

    /*************
    Start order
    @params: order_id, lang
    **************/
    public function startOrder(Request $request)
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
            if($order->status == 2) {

                if($order->driver_id == $auth_user->user_id) {
                    $order_fuels = CustomerOrderFuel::select('*')
                                        ->where('order_id', $order->id)
                                        ->get();
                    $no_stock = false;
                    foreach($order_fuels as $order_fuel) {
                        $truck_stock = DB::table('truck_fuels')
                                ->select('*')
                                ->where('truck_id', $order->truck_id)
                                ->where('fuel_type_id', $order_fuel->fuel_type_id)
                                ->first();
                        if($truck_stock && $truck_stock->stock >= $order_fuel->quantity) {
                            
                        }  else {
                            $no_stock = true;
                        }
                    }  
                    if(!$no_stock) {
                        $order->status = 3;                
                        $order->started_at = date('Y-m-d H:i:s');

                        if($order->save()) {                       

                            $message = __('driver-success.start_order_en');
                            if($request->lang  == 2) {
                                $message = __('driver-success.start_order_so');
                            }

                            $res = Response::send(true, [], $message, 200);
                    } else {
                        $message = __('driver-error.insufficient_truck_stock_en');
                        if($request->lang  == 2) {
                            $message = __('driver-error.insufficient_truck_stock_so');
                        }

                        $res = Response::send(false, [], $message, 400);
                    }

                    } else {
                        $message = __('driver-error.start_order_en');
                        if($request->lang  == 2) {
                            $message = __('driver-error.start_order_so');
                        }

                        $res = Response::send(false, [], $message, 400);
                    }
                } else {
                    $message = __('driver-error.start_order_driver_en');
                    if($request->lang  == 2) {
                        $message = __('driver-error.start_order_driver_so');
                    }
                }

            } else {
                $message = __('driver-error.status_order_en');
                if($request->lang  == 2) {
                    $message = __('driver-error.status_order_so');
                }
            }
        }
        return $res;
    }

    /*************
    Complete order
    @params: order_id, total_amount, payment_id, lang
    **************/
    public function completeOrder(Request $request)
    {
        $auth_user = auth('sanctum')->user();
        
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:customer_orders,id',
            'total_amount' => 'required|numeric',
            'payment_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {

            $order = CustomerOrder::find($request->order_id);
            if($order->status == 3) {

                if($order->driver_id == $auth_user->user_id) {
                    $order->status = 5;                
                    $order->delivered_at = date('Y-m-d H:i:s');

                    if($order->save()) {

                        $payment = CustomerOrderPayment::select('*')
                        ->where('order_id',$request->order_id)
                        ->first();
                        
                        $payment->payment_id = $request->payment_id;
                        $payment->total_amount = $request->total_amount;
                        $payment->driver_id = $auth_user->user_id;
                        $payment->status = 2;
                        $payment->updated_at = date('Y-m-d H:i:s');

                        $payment->save();

                        // DB::table('customer_order_payments')->where('order_id',$request->order_id)->update(
                        //      array(
                        //             'payment_id' => $request->payment_id,
                        //             'total_amount' => $request->total_amount,
                        //             'driver_id' => $auth_user->user_id,
                        //             'status' => 2,
                        //             'updated_at' => date('Y-m-d H:i:s'),
                        //      )
                        // ); 

                        // $payment = DB::table('customer_order_payments')
                        //             ->select('id', 'payment_type')
                        //             ->where('order_id',$request->order_id)
                        //             ->first();

                        DB::table('driver_payments')->insert(
                            array(
                                'driver_id' => $auth_user->user_id,
                                'type' => 1,
                                'order_id' => $request->order_id,
                                'amount' => $request->total_amount,
                                'payment_type' => $payment->payment_type,
                                'payment_id' => $request->payment_id,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            )
                        ); 

                        $driver = Driver::find($auth_user->user_id);
                        if($payment->payment_type == 1) {
                            $driver->total_mobile_earned = $driver->total_mobile_earned + $request->total_amount;

                        } else if($payment->payment_type == 2) {
                            $driver->total_cash_earned = $driver->total_cash_earned + $request->total_amount;
                        }
                        $driver->save();

                        /***** update truck stock starts ******/
                        $order_fuels = CustomerOrderFuel::select('*')
                                        ->where('order_id', $order->id)
                                        ->get();
                        foreach($order_fuels as $order_fuel) {
                            
                            $truck_stock = TruckFuel::select('*')
                                        ->where('truck_id', $order->truck_id)
                                        ->where('fuel_type_id', $order_fuel->fuel_type_id)
                                        ->first();
                            if($truck_stock) {
                                $truck_stock->stock = $truck_stock->stock - $order_fuel->quantity;
                                $truck_stock->save();
                            }

                            DB::table('truck_stock_logs')->insert(array(
                                    'truck_id' => $order->truck_id,
                                    'fuel_type_id' => $order_fuel->fuel_type_id,
                                    'stock' => $order_fuel->quantity,
                                    'balance_stock' => $truck_stock->stock,
                                    'type' => 2,
                                    'order_id' => $order->id,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                            ));
                        }

                        /***** delete driver's tracking locations ******/

                        DB::table('driver_location')
                            ->where('driver_id', $auth_user->user_id)
                            ->where('date', date('Y-m-d'))
                            ->delete();
                       
                        $message = __('driver-success.complete_order_en');
                        if($request->lang  == 2) {
                            $message = __('driver-success.complete_order_so');
                        }

                        $res = Response::send(true, [], $message, 200);

                    } else {
                        $message = __('driver-error.complete_order_en');
                        if($request->lang  == 2) {
                            $message = __('driver-error.complete_order_so');
                        }

                        $res = Response::send(false, [], $message, 400);
                    }
                } else {
                    $message = __('driver-error.complete_order_driver_en');
                    if($request->lang  == 2) {
                        $message = __('driver-error.complete_order_driver_so');
                    }
                    $res = Response::send(false, [], $message, 400);
                }

            } else {
                $message = __('driver-error.status_order_en');
                if($request->lang  == 2) {
                    $message = __('driver-error.status_order_so');
                }
                $res = Response::send(false, [], $message, 400);
            }
        }
        return $res;
    }

    /*************
    Post pin number
    @params: order_id, pin, lang
    **************/
    public function postPin(Request $request)
    {
        $auth_user = auth('sanctum')->user();
        
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:customer_orders,id',
            'pin' => 'required|digits:4'
        ]);

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {

            $order = CustomerOrder::find($request->order_id);           

            if($order->pin == $request->pin) {

                $fuels = CustomerOrder::find($request->order_id)
                    ->fuels()
                    ->get();

                $data = [
                    'fuels' => $fuels,
                ];
                
                $res = Response::send(true, $data, 'Success', 200);

                
            } else {
                $message = __('driver-error.pin_en');
                if($request->lang  == 2) {
                    $message = __('driver-error.pin_so');
                }
                $res = Response::send(false, [], $message, 422);
            }
        }
        return $res;
    }

    /*************
    Upload meter reading
    @params: order_id, meter_images[], lang
    ************/
    public function addMeterImages(Request $request)
    {
        $lang = [
            'meter_images.required' => __('driver-error.meter_images_required_en'),
        ];

        if($request->lang == 2) {
            $lang = [
            'meter_images.required' => __('customer-error.meter_images_required_so'),
            ];
        }

        $validator  = Validator::make($request->all(), [
                'meter_images'   => 'required',
                'meter_images.*' => 'mimes:png,jpg,jpeg,pdf', 
                'order_id'  => 'required|exists:customer_orders,id'
            ], $lang
        );

        if($validator->fails())
        {   
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {

            $auth_user    = auth('sanctum')->user();
            $order = CustomerOrder::find($request->order_id);  

            foreach($request->file('meter_images') as $file) {

                $image_uploaded_path = $file->store('meter-images','public'); 
                $array[] = [
                    'customer_id' => $order->customer_id,
                    'order_id' => $order->id,
                    'meter_image_url' => $image_uploaded_path,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            
            DB::table('meter_images')->insert($array);
            $message = __('driver-success.add_meter_image_en');
            if($request->lang  == 2) {
                $message = __('driver-success.add_meter_image_en');
            }
            $res = Response::send(true, [], $message, 200);             
        }

        return $res;
    } 

    /*************
    Ongoing order
    @params: 
    **************/
    public function onGoing(Request $request)
    {        
        $auth_user = auth('sanctum')->user();

        $order = CustomerOrder::select('customer_orders.*', 'address', 'country_code_id', 'phone', 'latitude', 'longitude', 'location', 'special_instructions', 'payment_type')

                ->leftjoin('customer_order_address', 'customer_orders.id', '=', 'customer_order_address.order_id')
                ->leftjoin('customer_order_payments', 'customer_orders.id', '=', 'customer_order_payments.order_id')

                ->where('customer_orders.driver_id', $auth_user->user_id)
                ->where('customer_orders.status', 3)
                ->with([
                   'fuels', 'meter_readings',

                    'customer' => function($query) {
                        $query->select('user_id', 'name_en', 'name_so', 'image');
                    },
                ])
                ->first();                   

        $data = array(
            'ongoing'=> $order
        );

        $res = Response::send(true, $data, '', 200);
        
        return $res;
    }
}
