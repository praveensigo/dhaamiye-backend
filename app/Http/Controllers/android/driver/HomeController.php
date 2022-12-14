<?php

namespace App\Http\Controllers\android\driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\android\driver\CustomerOrder;
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

            $orders = CustomerOrder::select('customer_orders.id', 'customer_orders.customer_id', 'fuel_station_id', 'customer_orders.status','customer_order_address.address as order_address', 'country_code_id', 'phone', 'customer_order_address.latitude as order_latitude', 'customer_order_address.longitude as order_longitude', 'location', 'total', 'customer_orders.created_at', 'fuel_stations.latitude as station_latitude', 'fuel_stations.longitude as station_longitude')
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
                        $query->select('user_id', 'name_en', 'name_so');
                    },
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
    public function accept(Request $request)
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
            $driver = Driver::find($auth_user->user_id);
            
            $order->status = 2;
            $order->driver_id = $auth_user->user_id;
            $order->truck_id = $driver->truck_id;
            $order->accepted_at = date('Y-m-d H:i:s');

            if($order->save()) {

                $message = __('customer-success.accept_order_en');
                if($request->lang  == 2) {
                    $message = __('customer-success.accept_order_en');
                }

                $res = Response::send(true, [], $message, 200);

            } else {
                $message = __('customer-error.accept_order_en');
                if($request->lang  == 2) {
                    $message = __('customer-error.accept_order_en');
                }

                $res = Response::send(false, [], $message, 400);
            }
        }
        return $res;
    }
}
