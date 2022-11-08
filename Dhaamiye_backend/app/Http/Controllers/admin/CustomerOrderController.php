<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\admin\CustomerOrder;
use App\Models\admin\CustomerOrderAddress;
use App\Models\admin\CustomerFuelSelection;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Models\Service\ResponseSender as Response;
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
            'driver_id' => 'nullable|numeric|exists:drivers,id',
            'order_type' => 'required|in:1,2',
            //'payment_type' => 'required|in:1,2',
           // 'fuel_type_id' => 'required|numeric|exists:fuel_types,id',
            //'quantity' => 'required|numeric',
            'address' => 'required|min:3|max:50',
            //'address' => 'required|min:3|max:50|contains_alphabets|starts_with_alphanumeric',
            'location' => 'required|min:3|max:100',
            'latitude' => 'required|min:3|max:100',
            'longitude' => 'required|min:3|max:100',
            'special_instructions' => 'nullable|min:3|max:1000',
            'mobile' => 'nullable|numeric|starts_with:6,7,8,9',
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
            'delivery_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:today',
            'delivery_time' => 'nullable',

        ], [
            'customer_id.required' => __('error.customer_id_required'),
            'customer_id.exists' => __('error.customer_not_found'),
            'fuel_station_id.required' => __('error.fuel_station_id_required'),
            'fuel_station_id.exists' => __('error.fuel_station_id_not_found'),
            'driver_id.exists' => __('error.driver_id_not_found'),
            'quantity.required' => __('error.quantity_required'),
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
            'latitude.required' => __('error.latitude_required'),
            'latitude.min' => __('error.latitude_min'),
            'latitude.max' => __('error.latitude_max'),
            'longitude.required' => __('error.longitude_required'),
            'logitude.min' => __('error.logitude_min'),
            'logitude.max' => __('error.logitude_max'),

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
            $order = new CustomerOrder;
            $order->customer_id = $fields['customer_id'];
            $order->fuel_station_id = $fields['fuel_station_id'];
           // $order->driver_id = $fields['driver_id'];
           // $order->truck_id = $fields['truck_id'];
            $order->order_type = $fields['order_type'];
           // $order->payment_type = $fields['payment_type'];
           // $a = $order->fuel_quantity_price = $fuel_quantity_price;
            $delivery = $this->delivery_charge($latitude, $longitude);
            $b = $order->delivery_charge = $delivery['delivery_charge'];
            if ($fields['coupon_code']) {

                if (($coupons->type == 1   or $coupons->type == 3)) {
                    $c = $order->discount = $d;

                } else {
                    $c = $order->discount = $a * $d / 100;
                }$order->total = $a + $b - $c;
                $order->coupon_code = $fields['coupon_code'];
            } else { $order->total = $a + $b;}
            $order->delivery_date =  date('D, d M Y ',strtotime($fields['delivery_date']));
            $order->delivery_time = date('H:i:s', strtotime($fields['delivery_time']));
            $order->status = 2;
            $order->created_at = date('Y-m-d H:i:s');
            $order->updated_at = date('Y-m-d H:i:s');
            $result = $order->save();
            if ($result) {
                $fueltypes=fuelType::all();
                         foreach ($fueltypes as $key => $type) {
                                 $fuelselections = new CustomerFuelSelection();
                                 $fuelselections ->order_id = $order->id;
                                 $fuelselections ->fuel_type_id =  $type->id;
                                 $fuelselections ->quantity =$fields['quantity'];
                                 $fuelselections->save();
                                }
                $users = DB::table('users')->select('users.email as customer_email', 'customers.name as customer_name', 'users.country_code_id as customer_country_code_id')
                    ->join('customers', 'users.user_id', '=', 'customers.id')
                    ->where('customers.id', $fields['customer_id'])
                    ->where('users.role_id', '3')
                    ->first();
                DB::table('customer_order_address')->insert(
                    array(
                        'customer_id' => $fields['customer_id'],
                        'order_id' => $order->id,
                        'name' => $users->customer_name,
                        'email' => $users->customer_email,
                        'country_code' => $users->customer_country_code_id,
                        'phone' => $fields['mobile'],
                        'location' => $fields['location'],
                        'address' => $fields['address'],
                        'latitude' => $fields['latitude'],
                        'longitude' => $fields['longitude'],
                        'special_insrtruction' => $fields['special_insrtruction'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ));
                $fcm = DB::table('users')->select('fcm')
                    ->where('users.user_id', $fields['customer_id'])
                    ->where('users.role_id', '3')
                    ->get();

                $title = 'Order Placed.';
                $content = 'Your order has been placed succesfully.';
                if ($this->sendPatientNotification($fcm,
                    $title, $content)) {
                    DB::table('notifications')->insert(
                        array(
                            'title' => $title,
                            'description' => $content,
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
                    $data = [],
                    $message = 'Order placed succesfully.',
                    $code = 200);
            } else {
                $res =  Response::send('false',
                    $data = [],
                    $message = 'Data not found.',
                    $code = 404);
            }
        }
        return $res;
    }


/*GET DELIVERY CHARGE*/
public function delivery_charge($latitude, $longitude)
{
    $settings = DB::table('settings')->select('latitude', 'longitude', 'fuel_delivery_range	')->first();
    $distance1 = $this->GetDrivingDistance($latitude, $settings->latitude, $longitude, $settings->longitude);
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

    $delivery_charge = $distance * $settings->fuel_delivery_range	;
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





}
