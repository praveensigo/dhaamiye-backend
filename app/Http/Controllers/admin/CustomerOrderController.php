<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\admin\CustomerOrder;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Models\service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

use Validator;

class CustomerOrderController extends Controller
{
    public function add(Request $request)
    {
        $fields = $request->input();
        $customer_id = $request->customer_id;
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|numeric|exists:customers,id',
            'fuel_station_id' => 'required|numeric|exists:fuel_stations,id',
            'order_type' => 'required|in:1,2',
            'payment_type' => 'required|in:1,2',
            'fuel_type_ids' => 'required|array',
            'fuel_type_ids.*' => 'distinct|exists:fuel_types,id|numeric',
            'quantities' => 'required|array',
            'address' => 'required|min:3|max:50',
            //'address' => 'required|min:3|max:50|contains_alphabets|starts_with_alphanumeric',
            'location' => 'required|min:3|max:100',
            'latitude' => 'required|min:3|max:100',
            'longitude' => 'required|min:3|max:100',
            'special_instructions' => 'nullable|min:3|max:1000',
            'mobile' => 'required|numeric|starts_with:6,7,8,9',
             'coupon_code' => [
                'nullable',
                Rule::exists('coupons', 'coupon_code')->where(function ($query) use ($customer_id) {
                    $today = Carbon::today();
                    $query->where('status', 1);
                    $query->whereDate('expiry_date', '>=', $today);
                    $query->whereNotIn('coupon_code', function ($q) use ($customer_id) {
                        $q->select('coupon_code')
                            ->from('customer_orders')
                            ->where('customer_id', '=', $customer_id)
                            ->whereNotNull('coupon_code');

                    });
                    return $query;
                }),
            ],  
            'delivery_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'delivery_time' => 'required',

        ], [
            'customer_id.required' => __('error.customer_id_required'),
            'customer_id.exists' => __('error.customer_not_found'),
            'fuel_station_id.required' => __('error.fuel_station_id_required'),
            'fuel_station_id.exists' => __('error.fuel_station_id_not_found'),
            'quantities.required' => __('error.quantity_required'),
            'coupon_code.min' => __('error.coupon_code_min'),
            'coupon_code.max' => __('error.coupon_code_max'),
            'delivery_date.date' => __('error.delivery_date_date'),
            'delivery_date.date_format' => __('error.delivery_date_format'),
            'address.required' => __('error.address_required'),
            'address.min' => __('error.address_min'),
            'address.max' => __('error.address_max'),
            //'address.contains_alphabets' => __('error.address_contains_alphabets'),
            //'address.starts_with_alphabet' => __('error.address_starts_with_alphabet'),
            'location.required' => __('error.location_required'),
            'location.min' => __('error.location_min'),
            'location.max' => __('error.location_max'),
            'latitude.required' => __('error.latitude_required'),
            'latitude.min' => __('error.latitude_min'),
            'latitude.max' => __('error.latitude_max'),
            'longitude.required' => __('error.longitude_required'),
            'logitude.min' => __('error.logitude_min'),
            'logitude.max' => __('error.logitude_max'),
            'mobile.required' => __('error.mobile_required'),
            'fuel_type_ids.required' => __('error.fuel_type_required'),
            'coupon_code.exists' => __('error.coupon_exists'),
            'order_type.required' => 'Please select the order type,Book now or Schedule delivery.',
            'payment_type.required' => 'Please select the payment type,Mobile or Cash.',
            'delivery_date.required' => __('error.delivery_date_required'),
            'delivery_time.required' => __('error.delivery_time_required'),
            
        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res =  Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $latitude = $fields['latitude'];
            $longitude = $fields['longitude'];
           
            if ($fields['coupon_code']) {
                  $coupons = DB::table('coupons')->select('id', 'coupon_name',  'type', 'coupon_code', 'amount', 'count', 'used_count', 'expiry_date', 'status', 'created_at')
                    ->where('coupon_code', $fields['coupon_code'])
                    ->first();
               $d = $coupons->amount;}
                $fuels = [];
                $i = 0;
                $fuel_quantity_price = 0;
                $quantities =  $fields['quantities'];
                foreach( $fields['fuel_type_ids']  as $fuel_type) {
    
                    $type = DB::table('fuel_station_stocks')
                            ->select('fuel_station_stocks.fuel_type_id', 'fuel_en', 'fuel_so', 'price', 'stock')
                           ->where('fuel_station_stocks.fuel_type_id', $fuel_type)
                           ->where('fuel_station_stocks.fuel_station_id', $fields['fuel_station_id'])
                           ->join('fuel_types', 'fuel_station_stocks.fuel_type_id', '=', 'fuel_types.id')
                            ->first();
                        if($type && $quantities[$i] <= $type->stock) {
    
                            $fuels[] = [
                                'id' => $fuel_type,
                                'fuel_en' => $type->fuel_en,
                                'fuel_so' => $type->fuel_so,
                                'quantity' => $quantities[$i],
                                'price' => $type->price,
                                'total' => $type->price * $quantities[$i],
                                'stock_status' => 1,
                                'converted_stock_status' => 'In Stock',
                                'message'=>'Order placed Successfully'
                            ];

                            $order_fuels[] = (object)array(
                                'customer_id' => $fields['customer_id'],
                                'order_id' => 0,
                                'fuel_type_id' => $type->fuel_type_id,
                                'quantity' => $quantities[$i],
                                'price' => $type->price,
                                'amount' => $type->price * $quantities[$i],
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            );
                           
                            $fuel_quantity_price = $fuel_quantity_price + ($type->price * $quantities[$i]);
                            
                        } else {
                            
                            $fuels[] = [
                                'id' => $fuel_type,
                                'stock_status' => 0,
                                'converted_stock_status' => 'Out of Stock',
                               'message'=>'Failed to place the Order'
                            ];

                            $order_fuels[] = (object)array(
                                'customer_id' => $fields['customer_id'],
                                'order_id' => 0,
                                'fuel_type_id' =>$fuel_type,

                                'quantity' => 0,
                                'price' => 0,
                                'amount' => 0,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            );
                           
                        }
                        $i++;
                    }               
                
                   
            $order = new CustomerOrder;
            $order->customer_id = $fields['customer_id'];
            $order->fuel_station_id = $fields['fuel_station_id'];
            $order->order_type = $fields['order_type'];
            $a= $order->fuel_quantity_price = $fuel_quantity_price;
            $delivery =$this->delivery_charge($latitude, $longitude,$fields['fuel_station_id']);
            $b=$order->delivery_charge = $delivery['delivery_charge'];
            $order->delivery_charge_commission = $b/2;
            $settings = DB::table('settings')->select('tax','commission')->first();
            $ac=$order->amount_commission =$fuel_quantity_price*$settings->commission/100;$order->delivery_charge_commission = $b/2;
            $order->total_commission = $ac+$b/2;
            $oth=$order->other_charges = '0';
            $tax = $fuel_quantity_price * $settings->tax / 100;
            $order->tax =$tax;
            $order->coupon_code = $fields['coupon_code'];
            $order->pin= $this->generateCode();
            if ($fields['coupon_code']) {

                if (($coupons->type == 1   or $coupons->type == 3)) {
                    $c = $order->promotion_discount = $d;

                } else {
                    $c = $order->promotion_discount = $a * $d / 100;
                }
                $order->total = $a + $b - $c +$tax +$oth;
            } 
            else { $order->total = $a + $b +$tax +$oth;}
            $order->delivery_date =$fields['delivery_date'];
            $order->delivery_time = date('H:i:s', strtotime($fields['delivery_time']));
            $order->status = 1;
            $role_id = auth('sanctum')->user()->role_id;
            $user_id = auth('sanctum')->user()->id;
            $order->added_by =$role_id;
            $order->added_user=$user_id;
            $order->created_at = date('Y-m-d H:i:s');
            $order->updated_at = date('Y-m-d H:i:s');
            $result = $order->save();

            if ($result) {if($order_fuels){
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
                DB::table('customer_order_payments')->insert(array(
                    'customer_id' => $order->customer_id,
                    'order_id' => $order->id,
                    'total_amount'=>$order->total,
                    'payment_type' =>$fields['payment_type'],
                    'created_at' => $order_fuel->created_at,
                    'updated_at' => $order_fuel->updated_at,
                    ));

    $users = DB::table('users')->select('users.country_code_id as customer_country_code_id')
                    ->join('customers', 'users.user_id', '=', 'customers.id')
                    ->where('customers.id', $fields['customer_id'])
                    ->where('users.role_id', '3')
                    ->first();
                DB::table('customer_order_address')->insert(
                    array(
                        'customer_id' => $fields['customer_id'],
                        'order_id' => $order->id,
                        'country_code_id' => $users->customer_country_code_id,
                        'phone' => $fields['mobile'],
                        'location' => $fields['location'],
                        'address' => $fields['address'],
                        'latitude' => $fields['latitude'],
                        'longitude' => $fields['longitude'],
                        'special_instructions' => $fields['special_instructions'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ));
                $fcm = DB::table('users')->select('fcm')
                    ->where('users.user_id', $fields['customer_id'])
                    ->where('users.role_id', '3')
                    ->get();

                $title_en = 'Order placed.';
                $title_so = '.';

                $content_en = 'Your order has been placed succesfully.';
                $content_so= '.';

                if ($this->sendCustomerNotification($fcm,
                    $title_en, $content_en)) {
                    DB::table('notifications')->insert(
                        array(
                            'title_en' => $title_en,
                            'title_so' => $title_so,
                            'description_en' => $content_en,
                            'description_so' => $content_so,
                            'type' => '3',
                            'user_id' => $fields['customer_id'],
                            'order_id' => $order->id,
                            'status' => 1,
                            'date' => date('Y-m-d'),
                            'time' => date('H:i:s'),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ));
                }

                $res =  Response::send('true',
                    $data = [
                           'fuels'=>$fuels

                    ],
                  //  $message = 'Order placed succesfully.',
                    $code = 200);
           } } else {
                $res =  Response::send('false',
                    $data = [],
                    $message = 'Data not found.',
                    $code = 404);
            }
        }
        return $res;
    }


/*GET DELIVERY CHARGE*/
public function delivery_charge($latitude, $longitude, $fuel_station_id)
{
    $settings = DB::table('settings')->select('fuel_delivery_range')->first();
    $fuel = DB::table('fuel_stations')->select('latitude', 'longitude')->where('id', $fuel_station_id)->first();
    $distance1 = $this->GetDrivingDistance($latitude, $fuel->latitude, $longitude, $fuel->longitude);

    $distance2 = $distance1['distance'];
    $test = explode(' ', $distance2);
    $distance4 = $test[1];
    if ($distance4 == 'km') {
        $distance3 = chop($distance2, " km");
    } elseif ($distance4 == 'm') {
        $distance5 = chop($distance2, " m");
        $distance3 = $distance5 / 1000;
    }
    $distance4 = str_replace(',', '.', $distance3);
    $distance = (float) $distance4;
    $delivery_charge = $distance * $settings->fuel_delivery_range;
    return array('delivery_charge' => $delivery_charge);

}

public function GetDrivingDistance($lat1, $lat2, $long1, $long2)
{
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $lat1 . "," . $long1 . "&destinations=" . $lat2 . "%2C" . $long2 . "&mode=driving&language=pl-PL&key=AIzaSyDmehs_u8H6kgD9d9aVV38RuAS-GSZT598";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    $response_a = json_decode($response, true);
    $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
    $time = $response_a['rows'][0]['elements'][0]['duration']['text'];

    return array('distance' => $dist, 'time' => $time);
}

/* SEND NOTIFICATION */
public function sendCustomerNotification($fcm, $title, $body)
{
    $SERVER_API_KEY = "";
    $header = [
        'Authorization: key=' . $SERVER_API_KEY,
        'Content-Type: Application/json',
    ];
    $msg = [
        'title' => $title,
        'body' => $body,
    ];

    $notification = [
        'title' => $title,
        'body' => $body,
        'content_available' => true,
    ];

    $payload = [
        'data' => $msg,
        'notification' => $notification,
        'to' => $fcm,
        'priority' => 10,
    ];
    $url = 'https://fcm.googleapis.com/fcm/send';

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => $header,
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    return true;
}
/****** GENERATE CODE *****/
function generateCode() 
{
    $chars = '0123456789';
    $len = strlen($chars);
    $code = '';
    for ($i = 0; $i < 4; $i++) {
        $code .= $chars[rand(0, $len - 1)];
    }
    return $code;
}
//CANCEL ORDER
public function cancelOrder(Request $request)
{
    $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|numeric|exists:customer_orders,id',
            'reason' => 'required',

        ],
            [   'reason.required' => __('error.reason_cancel_required'),

            ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res =  Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
             $order = CustomerOrder::find($request->order_id);
        
        $order->status = 6;
        $order->cancelled_at = date('Y-m-d H:i:s');
        $order->cancel_reason = $request->reason;
        if($order->save()) {

            $res =  Response::send('true',
            $data = [],
            $message = 'Order cancelled successfully.',
            $code = 200);
    } else {
        $res =  Response::send('false',
            $data = [],
            $message = 'Failed to cancel order.',
            $code = 400);
    }
}
return $res;
}

//ASSIGN DRIVER

public function assignDriver(Request $request)
{
    
    $fields = $request->input();
    $validator = Validator::make($request->all(),
        [
            'order_id' => 'required|numeric|exists:customer_orders,id',
            'driver_id' => 'required|numeric|exists:drivers,id',
            
        ],
        [
            'order_id.exists' => __('error.customer_order_not_found'),
            'driver_id.exists' => __('error.driver_not_found'),

        ]

    );
    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res = Response::send('false', $data = [], $message = $errors, $code = 422);

    } else {
       
        $order = CustomerOrder::find($fields['order_id']);
        $order->driver_id = $fields['driver_id'];
        $driver = DB::table('drivers')->select('drivers.*')
        ->where('id', $fields['driver_id'])
        ->first();
        $truck_id=$driver->truck_id;
        $order->truck_id = $truck_id;
        $order->status = 2;
        $order->accepted_at = date('Y-m-d H:i:s');
        $order->updated_at = date('Y-m-d H:i:s');
        $result = $order->save();
        if ($result) {
            DB::table('customer_order_payments')
            ->where('order_id', $fields['order_id'])
            ->update(['driver_id' =>$order->driver_id]);
            $res = Response::send('true',
                [],
                $message = __('success.driver_assign'),
                $code = 200);
        } else {
            $res = Response::send('false',
                [],
                $message = __('error_driver_assign_failed'),
                $code = 400);
        }
    }

    return $res;
}

    /*GET ORDERS */
public function index(Request $request)
{

    $fields = $request->input();
    $validator = Validator::make($request->all(),
        ['limit' => 'required',
            'keyword' => 'nullable',
            'status' => 'nullable|numeric|in:0,1,2,3,4,5,6',

        ]);

    if ($validator->fails()) {$errors = collect($validator->errors());
        $res = Response::send('false', $data = [], $message = $errors, $code = 422);
    } else {

         $orders = CustomerOrder::select('customer_orders.id as customer_order_id','customer_orders.customer_id','customer_orders.fuel_station_id','trucks.truck_no' ,'trucks.color as truck_color' ,'customer_orders.driver_id','customer_orders.truck_id','customer_orders.order_type','customer_orders.fuel_quantity_price','customer_orders.tax','customer_orders.delivery_charge','customer_orders.coupon_code','customer_orders.promotion_discount', 'customer_orders.other_charges','customer_orders.total','customer_orders.amount_commission','customer_orders.delivery_charge_commission','customer_orders.total_commission','customer_orders.delivery_date','customer_orders.delivery_time','customer_orders.pin','customer_orders.accepted_at','customer_orders.started_at','customer_orders.delivered_at','customer_orders.cancelled_at','customer_orders.cancel_reason',
         'p1.name_en as customer_name_en', 'p1.name_so as customer_name_so', 'p2.name_en as driver_name_en', 'p2.name_so as driver_name_so', 'p1.mobile as customer_mobile','p2.mobile as driver_mobile' ,'p1.country_code_id',  'p3.name_en as fuel_station_name_en', 'p3.name_so as fuel_station_name_so', 'p3.mobile as fuel_station_mobile','customer_orders.status', 'customer_orders.total', 'customer_orders.created_at','customer_orders.added_by as customer_order_added_by','customer_orders.added_user as customer_order_added_user')
         ->leftjoin('trucks', 'customer_orders.truck_id', '=', 'trucks.id')

            ->join('users as p1', function ($join) {
                $join->on('p1.user_id', '=', 'customer_orders.customer_id')
                    ->where('p1.role_id', 3);
            })
            ->leftjoin('users as p2', function ($join) {
                $join->on('p2.user_id', '=', 'customer_orders.driver_id')
                    ->where('p2.role_id', 4);
            })
            ->join('users as p3', function ($join) {
                $join->on('p3.user_id', '=', 'customer_orders.fuel_station_id')
                    ->where('p3.role_id', 5);
            })

            ->leftjoin('country_codes', 'country_codes.id', '=', 'p1.country_code_id')
            ->with([
                'customers', 'drivers', 'fuel_stations','trucks'
            ])
            ->orderBy('customer_orders.id','desc');

        if ($request->keyword) {
            $orders->where(function ($query) use ($request) {
                $query->where('customer_orders.order_type', 'LIKE', '%' . $request->keyword . '%')
                   ->orWhere('p1.name_en', 'LIKE', '%' . $request->keyword . '%')
                   ->orWhere('p1.name_so', 'LIKE', '%' . $request->keyword . '%')
                   ->orWhere('p1.mobile', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('p2.name_en', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('p2.name_so', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('p2.mobile', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('p3.mobile', 'LIKE', '%' . $request->keyword . '%')
                     ->orWhere('p3.name_en', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('p3.name_so', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('trucks.truck_no', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('trucks.color', 'LIKE', '%' . $request->keyword . '%');

            });}

        if ($fields['status'] != '' && $fields['status'] != null) {
            
            if ($fields['status'] == 1) {

                $orders->where('customer_orders.status', $fields['status']);
            }
            if ($fields['status'] == 2) {

                $orders->where('customer_orders.status', $fields['status']);
            }

            if ($fields['status'] == 3) {

                $orders->where('customer_orders.status', $fields['status']);
            }
            if ($fields['status'] == 4) {

                $orders->where('customer_orders.status', $fields['status']);
            }if ($fields['status'] == 5) {

                $orders->where('customer_orders.status', $fields['status']);
            }
            if ($fields['status'] == 6) {

                $orders->where('customer_orders.status', $fields['status']);
            }
                    }
        $orders = $orders->paginate($fields['limit']);

        $res = Response::send('true',
            $data = [
                'Orders' => $orders,
            ],
            $message = 'Success',
            $code = 200);}
    return $res;
  }
/*GET ORDERS */
public function details(Request $request)
{

  $fields = $request->input();
  $validator = Validator::make($request->all(),
      [
        'id' => 'required|numeric|exists:customer_orders,id',
      ]);

  if ($validator->fails()) {$errors = collect($validator->errors());
      $res = Response::send('false', $data = [], $message = $errors, $code = 422);
  } else {
   
    

       $order =CustomerOrder::select('customer_orders.id as customer_order_id','customer_orders.customer_id','customer_orders.fuel_station_id','trucks.truck_no' ,'trucks.color as truck_color' ,'customer_orders.driver_id','customer_orders.truck_id','customer_orders.order_type','customer_orders.fuel_quantity_price','customer_orders.tax','customer_orders.delivery_charge','customer_orders.coupon_code','customer_orders.promotion_discount', 'customer_orders.other_charges','customer_orders.total','customer_orders.amount_commission','customer_orders.delivery_charge_commission','customer_orders.total_commission as Total_earnings','customer_orders.delivery_date','customer_orders.delivery_time','customer_orders.pin','customer_orders.accepted_at as driver_accepted_at','customer_orders.started_at','customer_orders.delivered_at','customer_orders.cancelled_at','customer_orders.cancel_reason','p1.name_en as customer_name_en', 'p1.name_so as customer_name_so', 'p2.name_en as driver_name_en', 'p2.name_so as driver_name_so', 'p1.mobile as customer_mobile','p2.mobile as driver_mobile' ,'p1.country_code_id',  'p3.name_en as fuel_station_name_en', 'p3.name_so as fuel_station_name_so', 'p3.mobile as fuel_station_mobile','customer_orders.status', 'customer_orders.total', 'customer_orders.created_at as customer_order_created_on','customer_orders.added_by as customer_order_added_by','customer_orders.added_user as customer_order_added_user', 'customer_order_payments.*','customer_order_address.*')
       ->leftjoin('trucks', 'customer_orders.truck_id', '=', 'trucks.id')
       ->leftjoin('customer_order_payments', 'customer_order_payments.order_id', '=', 'customer_orders.id')
       ->leftjoin('customer_order_address', 'customer_order_address.order_id', '=', 'customer_orders.id')

          ->join('users as p1', function ($join) {
              $join->on('p1.user_id', '=', 'customer_orders.customer_id')
                  ->where('p1.role_id', 3);
          })
          ->leftjoin('users as p2', function ($join) {
              $join->on('p2.user_id', '=', 'customer_orders.driver_id')
                  ->where('p2.role_id', 4);
          })
          ->join('users as p3', function ($join) {
              $join->on('p3.user_id', '=', 'customer_orders.fuel_station_id')
                  ->where('p3.role_id', 5);
          })

          ->leftjoin('country_codes', 'country_codes.id', '=', 'p1.country_code_id')
          ->with([
              'customers', 'drivers', 'fuel_stations','trucks'
          ])
          ->where('customer_orders.id', $request->id)
          ->first();
          $fuels = DB::table('customer_order_fuels')->select('customer_order_fuels.*','fuel_types.fuel_en','fuel_types.fuel_so')
          ->join('fuel_types', 'fuel_types.id', '=', 'customer_order_fuels.fuel_type_id')
          ->where('customer_order_fuels.order_id', $request->id)
          ->get();
          
          $meter_images =DB::table('meter_images')->select('meter_images.*')
          ->where('meter_images.order_id', $request->id)
          ->get();
         
          $ratings =DB::table('ratings')->select('ratings.*')
          ->where('ratings.order_id', $request->id)
          ->get();
         
      $res = Response::send('true',
          $data = [
            'Order_details' => $order,
            'fuels' => $fuels,
            'meter_images' => $meter_images,
         'ratings' => $ratings,

          ],
          $message = 'Success',
          $code = 200);}
  return $res;
}

}
