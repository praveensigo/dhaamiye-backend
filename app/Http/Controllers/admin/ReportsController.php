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
            'from_date'   => 'nullable|date_format:Y-m-d',
            'to_date'     => 'nullable|date_format:Y-m-d',

        ]);
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else 
        {
            $orders = CustomerOrder::select('customer_orders.*')
                                    ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
                                    ->with([
                                       'fuels','fuel_station','fuel'
                                        ])
                                     ->where('customer_order_payments.status','2')   
                                    ->orderBy('customer_orders.id');
            if ($fields['keyword']) 
                    {
                        $search_text=$fields['keyword'];
                        $orders->where('customer_orders.fuel_quantity_price', 'Like', '%' . $search_text . '%')
                                ->orWhere('customer_orders.id', 'Like', '%' . $search_text . '%')
                                ->orWhereHas('customer', function ($query)use($search_text) 
                                                    {
                                                        $query->where('users.email', 'Like', '%' . $search_text . '%')
                                                            ->orWhere('users.mobile', 'Like', '%' . $search_text . '%')
                                                            ->orWhere('users.name_en', 'Like', '%' . $search_text . '%')
                                                            ->orWhere('users.name_so', 'Like', '%' . $search_text . '%');
                                                            
                                                        return $query;       
                                                    },)
                                 ->orWhereHas('fuel', function ($query)use($search_text) 
                                                    {
                                                        $query->where('fuel_types.fuel_en', 'Like', '%' . $search_text . '%')
                                                            ->orWhere('fuel_types.fuel_so', 'Like', '%' . $search_text . '%');
                                                        return $query;       
                                                    },)
                                ->orWhereHas('fuel_station', function ($query)use($search_text) 
                                                    {
                                                        $query->where('users.name_en', 'Like', '%' . $search_text . '%')
                                                            ->orWhere('users.name_so', 'Like', '%' . $search_text . '%');
                                                        return $query;       
                                                    },)
                                ;
                    }
            if ($fields['fuel_station'])
                    {
                        $orders->where('customer_orders.fuel_station_id',$fields['fuel_station']);

                    }
            if ($fields['from_date'] && $fields['to_date']) 
                    {
                        $orders
                        //->whereDateBetween('customer_orders.created_at', [$fields['from_date'], $fields['from_date']])
                        ->whereDate('customer_orders.created_at','>=', $fields['from_date']) 
                        ->orWhereDate('customer_orders.created_at','<=',$fields['to_date']); 
                    }        
           

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
