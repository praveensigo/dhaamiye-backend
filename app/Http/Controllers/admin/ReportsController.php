<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service\ResponseSender as Response;
use App\Models\admin\CustomerOrder;
use Validator;

class ReportsController extends Controller
{
    public function salesReport(Request $request)
    {
    	$fields    = $request->input();
        $validator = Validator::make($request->all(), [
            'limit'   => 'required|numeric',
            'keyword' => 'nullable',
            'fuel_station' => 'nullable|numeric|exists:fuel_stations,id',
            'from_date' => 'nullable|date', 
            'to_date' => 'nullable|date', 

        ]);
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else 
        {
            $orders = CustomerOrder::select('customer_orders.id as order_id')
                                    ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
                                    ->with([
                                        'fuels'
                                           ,
                                    //    'fuel',
                                       'fuel_station'
                                        ])
                                     ->where('customer_order_payments.status','2')   
                                    ->orderBy('customer_orders.id');
            if ($fields['keyword']) 
                    {
                        $orders->where('customer_orders.id', 'LIKE', $fields['keyword'] . '%');
                    }
            if ($fields['fuel_station'])
                    {
                        $orders->where('customer_orders.fuel_station_id',$fields['fuel_station']);

                    }
            if ($fields['from_date'] && $fields['from_date']) 
                    {
                        $orders->whereBetween('customer_orders.created_at', [$fields['from_date'], $fields['from_date']])
                                ->orWhere('customer_orders.created_at',$fields['from_date']); 
                    }        
            // if ($fields['status'] != '' && $fields['status'] != null) {
            //     $fuels->where('status',$fields['status']);
            // }

            $orders = $orders->paginate($fields['limit']);

            $data = array(
                'orders' => $orders,
            );

            $res    = Response::send('true', 
                               $data, 
                               $message ='Success', 
                               $code = 200);  
        }
        return $res;
    }  

}
