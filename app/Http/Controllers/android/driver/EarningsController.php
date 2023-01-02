<?php

namespace App\Http\Controllers\android\driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\android\driver\Driver;
use Illuminate\Support\Facades\DB;
use App\Models\service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Validator;

class EarningsController extends Controller
{
    public function index(Request $request) 
    {
        
        $auth_user = auth('sanctum')->user();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {

            $driver = Driver::find($auth_user->user_id);

            $logs = DB::table('driver_payments')->select('*')           
                ->where('driver_id', $auth_user->user_id)
                ->orderBy('created_at','desc')
                ->paginate($request->limit); 

                      
           

            $data = array(
                'total_earned' => number_format($driver->total_mobile_earned + $driver->total_cash_earned, 2, '.', ''),
                'total_mobile_earned' => $driver->total_mobile_earned,
                'total_cash_earned' => $driver->total_cash_earned,
                'cash_in_hand' => number_format($driver->total_mobile_earned + $driver->total_cash_earned - $driver->total_paid, 2, '.', ''),

                'logs' => $logs,
            );

            $res = Response::send(true, $data, '', 200);
        }
        return $res;
    }
}
