<?php

namespace App\Http\Controllers\android\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    /*************
    Confirm Order
    @params: order_id, latitude, longitude, address, payment_type, lang
    **************/
    public function add(Request $request)
    {        
        $auth_user = auth('sanctum')->user();
        $auth_user_id = $auth_user->user_id;
        
        $validator = Validator::make($request->all(),
            [
                'customer_id' => 'required|exists:customers,id',
                'fuel_station_id' => 'required|exists:fuel_stations,id',
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $order = CustomerOrder::find($request->order_id);

            if($order->status == 0) {
                $order->status = 1;
                $order->save();

                

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

            return $res;
        }
    }
}
