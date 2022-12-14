<?php

namespace App\Http\Controllers\fuelstation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\fuelstation\FuelStationPaymentLog;
use App\Models\Service\ResponseSender as Response;
use Validator;


class PaymentController extends Controller
{
    public function payments(Request $request)
    {
    	$fields    = $request->input();
        $validator = Validator::make($request->all(), [
            'limit'   => 'required|numeric',
        ]);
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else 
        {
            $user_id = auth('sanctum')->user()->user_id;
            $payments = FuelStationPaymentLog::select('fuel_station_payment_logs.id','fuel_station_payment_logs.amount','fuel_station_payment_logs.type','fuel_station_payment_logs.order_id','fuel_station_payment_logs.balance','fuel_station_payment_logs.created_at')
                                     ->where('fuel_station_payment_logs.fuel_station_id', $user_id)
                                    ->orderBy('fuel_station_payment_logs.id','desc');
            
            $payments = $payments->paginate($fields['limit']);

            $data = array(
                'payments' => $payments,
            );

            $res    = Response::send('true', 
                               $data, 
                               $message ='Success', 
                               $code = 200);  
        }
        return $res;
    } 
}
