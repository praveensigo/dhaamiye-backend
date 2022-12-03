<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\service\ResponseSender as Response;
use App\Models\admin\CustomerOrder;
use Illuminate\Support\Facades\DB;
use PDF;
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
                                       'fuel_station'

                                        ])
                                     ->where('customer_order_payments.status','2')
                                     ->where('customer_orders.status','5')   
                                    ->orderBy('customer_orders.id');
            if ($fields['keyword']) 
                    {

                        $search_text=$fields['keyword'];
                        $orders->where('customer_orders.fuel_quantity_price', 'Like', '%' . $search_text . '%')
                                ->orWhere('customer_orders.id', 'Like', '%' . $search_text . '%')
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

            // if ($fields['from_date'] && $fields['to_date']) 
            //         {
            //             $orders
            //             //->whereDateBetween('customer_orders.created_at', [$fields['from_date'], $fields['from_date']])
            //             ->whereDate('customer_orders.created_at','>=', $fields['from_date']) 
            //             ->orWhereDate('customer_orders.created_at','<=',$fields['to_date']); 
            //         }        
           
            if (isset($fields['from_date'])) {
                $orders = $orders->where('customer_orders.created_at', '>=', $fields['from_date'] . ' 00:00:00');
            }
            if (isset($fields['to_date'])) {
                $orders = $orders->where('customer_orders.created_at', '<=', $fields['to_date'] . ' 23:59:59');
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
    public function earningReport(Request $request)
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

            $orders = CustomerOrder::select('customer_orders.id','customer_orders.fuel_station_id','customer_orders.amount_commision','customer_orders.delivery_charge_commision','customer_orders.total_commision',)
                                    ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
                                    ->with([
                                       'fuel_station'
                                        ])
                                    ->where('customer_orders.status','5')   
                                     ->where('customer_order_payments.status','2')   
                                    ->orderBy('customer_orders.id');
            if ($fields['keyword']) 
                    {

                        $search_text=$fields['keyword'];
                        $orders->where('customer_orders.amount_commision', 'Like', '%' . $search_text . '%')
                                ->orWhere('customer_orders.delivery_charge_commision', 'Like', '%' . $search_text . '%')
                                ->orWhere('customer_orders.total_commision', 'Like', '%' . $search_text . '%')
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

            // if ($fields['from_date'] && $fields['to_date']) 
            //         {
            //             $orders
            //             //->whereDateBetween('customer_orders.created_at', [$fields['from_date'], $fields['from_date']])
            //             ->whereDate('customer_orders.created_at','>=', $fields['from_date']) 
            //             ->orWhereDate('customer_orders.created_at','<=',$fields['to_date']); 
            //         }        
           
            if (isset($fields['from_date'])) {
                $orders = $orders->where('customer_orders.created_at', '>=', $fields['from_date'] . ' 00:00:00');
            }
            if (isset($fields['to_date'])) {
                $orders = $orders->where('customer_orders.created_at', '<=', $fields['to_date'] . ' 23:59:59');
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

    public function earningReportDownload(Request $request)
    {   
        header('Access-Control-Allow-Origin: *');
    	$fields    = $request->input();
        $validator = Validator::make($request->all(), [
            'fuel_station' => 'nullable|numeric|exists:fuel_stations,id',
            'from_date'   => 'required|date_format:Y-m-d',
            'to_date'     => 'required|date_format:Y-m-d',

        ]);
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else 
        {   
            $start = $fields['from_date'] . ' 00:00:00';
            $end = $fields['to_date'] . ' 23:59:59';

            if ($fields['fuel_station'])
            {
                $orders = CustomerOrder::select('customer_orders.id')
                                    ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
                                     ->where('customer_order_payments.status','2')
                                     ->where('customer_orders.status','5') 
                                     ->where('customer_orders.created_at', '>=', $start) 
                                     ->where('customer_orders.created_at', '<=', $end)
                                     ->where('customer_orders.fuel_station_id',$fields['fuel_station'])
                                    ->orderBy('customer_orders.id')->get();
           
                    }
                    else{
                        $orders = CustomerOrder::select('customer_orders.id','customer_orders.created_at')
                                        ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
                                        ->where('customer_order_payments.status','2')
                                        ->where('customer_orders.status','5') 
                                        ->where('customer_orders.created_at', '>=', $start) 
                                        ->where('customer_orders.created_at', '<=', $end)
                                        ->orderBy('customer_orders.id')->get();  
                        }
        $i=1;        
        foreach($orders as $order)
            {
                $order->sl_no = $i;
                $order->order_id = $order->id;
                $name_en = DB::table('users')->select('users.name_en')
                                    ->join('customer_orders','customer_orders.fuel_station_id','=','users.user_id')
                                    ->where('users.role_id','5') 
                                    ->where('customer_orders.id',$order->id)->first()->name_en;
                    if ($name_en != '' && $name_en != null)
                        {
                            $order->fuel_station_name = $name_en;
                        }else{
                            $order->fuel_station_name =DB::table('users')->select('users.name_so')
                                    ->join('customer_orders','customer_orders.fuel_station_id','=','users.user_id')
                                    ->where('users.role_id','5') 
                                    ->where('customer_orders.id',$order->id)->first()->name_so;
                        }

                // $order->fuel_station_name_en =DB::table('users')->select('users.name_en')
                //                     ->join('customer_orders','customer_orders.fuel_station_id','=','users.user_id')
                //                     ->where('users.role_id','5') 
                //                     ->where('customer_orders.id',$order->id)->first()->name_en;
                // $order->fuel_station_name_so =DB::table('users')->select('users.name_so')
                //                     ->join('customer_orders','customer_orders.fuel_station_id','=','users.user_id')
                //                     ->where('users.role_id','5') 
                //                     ->where('customer_orders.id',$order->id)->first()->name_so;
                $order->amount_commision =DB::table('customer_orders')->select('amount_commision')
                                    ->where('customer_orders.id',$order->id)->first()->amount_commision;
                $order->delivery_charge_commision =DB::table('customer_orders')->select('delivery_charge_commision')
                                    ->where('customer_orders.id',$order->id)->first()->delivery_charge_commision;
                $order->total_commision =DB::table('customer_orders')->select('total_commision')
                                    ->where('customer_orders.id',$order->id)->first()->total_commision;
                 $order->date =DB::table('customer_orders')->select('created_at')
                                    ->where('customer_orders.id',$order->id)->first()->created_at;
                        $i++;  
                        }
            $data = array(
                'from_date' => $start,
                'to_date' => $end,
                'orders' => $orders,
            );
            $pdf   = PDF::loadView('admin/EarningReport', $data);
            return $pdf->download('EarningReport.pdf');

            $res    = Response::send('true', 
                               $data, 
                               $message ='Success', 
                               $code = 200);  
        }
        return $res;
    } 
  
    public function salesReportDownload(Request $request)
    {   
        header('Access-Control-Allow-Origin: *');
    	$fields    = $request->input();
        $validator = Validator::make($request->all(), [
            'fuel_station' => 'nullable|numeric|exists:fuel_stations,id',
            'from_date'   => 'required|date_format:Y-m-d',
            'to_date'     => 'required|date_format:Y-m-d',

        ]);
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else 
        {   
            $start = $fields['from_date'] . ' 00:00:00';
            $end = $fields['to_date'] . ' 23:59:59';

            if ($fields['fuel_station'])
            {
                $orders = CustomerOrder::select('customer_orders.id')
                                    ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
                                     ->where('customer_order_payments.status','2')
                                     ->where('customer_orders.status','5') 
                                     ->where('customer_orders.created_at', '>=', $start) 
                                     ->where('customer_orders.created_at', '<=', $end)
                                     ->where('customer_orders.fuel_station_id',$fields['fuel_station'])
                                    ->orderBy('customer_orders.id')->get();
           
                    }
                    else{
                        $orders = CustomerOrder::select('customer_orders.id','customer_orders.created_at')
                                        ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
                                        ->where('customer_order_payments.status','2')
                                        ->where('customer_orders.status','5') 
                                        ->where('customer_orders.created_at', '>=', $start) 
                                        ->where('customer_orders.created_at', '<=', $end)
                                        ->orderBy('customer_orders.id')->get();  
                        }
        $i=1;        
        foreach($orders as $order)
            {
                $order->sl_no = $i;
                $order->order_id = $order->id;
                $name_en = DB::table('users')->select('users.name_en')
                                    ->join('customer_orders','customer_orders.fuel_station_id','=','users.user_id')
                                    ->where('users.role_id','5') 
                                    ->where('customer_orders.id',$order->id)->first()->name_en;
                    if ($name_en != '' && $name_en != null)
                        {
                            $order->fuel_station_name = $name_en;
                        }else{
                            $order->fuel_station_name =DB::table('users')->select('users.name_so')
                                    ->join('customer_orders','customer_orders.fuel_station_id','=','users.user_id')
                                    ->where('users.role_id','5') 
                                    ->where('customer_orders.id',$order->id)->first()->name_so;
                        }

                // $order->fuel_station_name_en =DB::table('users')->select('users.name_en')
                //                     ->join('customer_orders','customer_orders.fuel_station_id','=','users.user_id')
                //                     ->where('users.role_id','5') 
                //                     ->where('customer_orders.id',$order->id)->first()->name_en;
                // $order->fuel_station_name_so =DB::table('users')->select('users.name_so')
                //                     ->join('customer_orders','customer_orders.fuel_station_id','=','users.user_id')
                //                     ->where('users.role_id','5') 
                //                     ->where('customer_orders.id',$order->id)->first()->name_so;
                $order->fuel_quantity_price =DB::table('customer_orders')->select('fuel_quantity_price')
                                    ->where('customer_orders.id',$order->id)->first()->fuel_quantity_price;
                $order->delivery_charge =DB::table('customer_orders')->select('delivery_charge')
                                    ->where('customer_orders.id',$order->id)->first()->delivery_charge;
                $order->tax =DB::table('customer_orders')->select('tax')
                                    ->where('customer_orders.id',$order->id)->first()->tax;
                $order->other_charges =DB::table('customer_orders')->select('other_charges')
                                    ->where('customer_orders.id',$order->id)->first()->other_charges;  
                $order->discount =DB::table('customer_orders')->select('promotion_discount')
                                    ->where('customer_orders.id',$order->id)->first()->promotion_discount;
                $order->total =DB::table('customer_orders')->select('total')
                                    ->where('customer_orders.id',$order->id)->first()->total;
                 $order->date =DB::table('customer_orders')->select('created_at')
                                    ->where('customer_orders.id',$order->id)->first()->created_at;
                        $i++;  
                        }
            $data = array(
                'from_date' => $start,
                'to_date' => $end,
                'orders' => $orders,
            );
            $pdf   = PDF::loadView('admin/SalesReport', $data);
            return $pdf->download('SalesReport.pdf');

            $res    = Response::send('true', 
                               $data, 
                               $message ='Success', 
                               $code = 200);  
        }
        return $res;
    } 
    // public function salesReport(Request $request)
    // {
    // 	$fields    = $request->input();
    //     $validator = Validator::make($request->all(), [
    //         'limit'   => 'required|numeric',
    //         'keyword' => 'nullable',
    //         'fuel_station' => 'nullable|numeric|exists:fuel_stations,id',
    //         'from_date'   => 'nullable|date_format:Y-m-d',
    //         'to_date'     => 'nullable|date_format:Y-m-d',


    //     ]);
    //     if ($validator->fails()) 
    //     {
    //         $errors = collect($validator->errors());
    //         $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
    //     } else 
    //     {

    //         $orders = CustomerOrder::select('customer_orders.*')
    //                                 ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
    //                                 ->with([
    //                                    'fuels','fuel_station','fuel'

    //                                     ])
    //                                  ->where('customer_order_payments.status','2')
    //                                  ->where('customer_orders.status','5')   
    //                                 ->orderBy('customer_orders.id');
    //         if ($fields['keyword']) 
    //                 {

    //                     $search_text=$fields['keyword'];
    //                     $orders->where('customer_orders.fuel_quantity_price', 'Like', '%' . $search_text . '%')
    //                             ->orWhere('customer_orders.id', 'Like', '%' . $search_text . '%')
    //                             ->orWhereHas('customer', function ($query)use($search_text) 
    //                                                 {
    //                                                     $query->where('users.email', 'Like', '%' . $search_text . '%')
    //                                                         ->orWhere('users.mobile', 'Like', '%' . $search_text . '%')
    //                                                         ->orWhere('users.name_en', 'Like', '%' . $search_text . '%')
    //                                                         ->orWhere('users.name_so', 'Like', '%' . $search_text . '%');
                                                            
    //                                                     return $query;       
    //                                                 },)
    //                              ->orWhereHas('fuel', function ($query)use($search_text) 
    //                                                 {
    //                                                     $query->where('fuel_types.fuel_en', 'Like', '%' . $search_text . '%')
    //                                                         ->orWhere('fuel_types.fuel_so', 'Like', '%' . $search_text . '%');
    //                                                     return $query;       
    //                                                 },)
    //                             ->orWhereHas('fuel_station', function ($query)use($search_text) 
    //                                                 {
    //                                                     $query->where('users.name_en', 'Like', '%' . $search_text . '%')
    //                                                         ->orWhere('users.name_so', 'Like', '%' . $search_text . '%');
    //                                                     return $query;       
    //                                                 },)
    //                             ;

    //                 }
    //         if ($fields['fuel_station'])
    //                 {
    //                     $orders->where('customer_orders.fuel_station_id',$fields['fuel_station']);

    //                 }

    //         // if ($fields['from_date'] && $fields['to_date']) 
    //         //         {
    //         //             $orders
    //         //             //->whereDateBetween('customer_orders.created_at', [$fields['from_date'], $fields['from_date']])
    //         //             ->whereDate('customer_orders.created_at','>=', $fields['from_date']) 
    //         //             ->orWhereDate('customer_orders.created_at','<=',$fields['to_date']); 
    //         //         }        
           
    //         if (isset($fields['from_date'])) {
    //             $orders = $orders->where('customer_orders.created_at', '>=', $fields['from_date'] . ' 00:00:00');
    //         }
    //         if (isset($fields['to_date'])) {
    //             $orders = $orders->where('customer_orders.created_at', '<=', $fields['to_date'] . ' 23:59:59');
    //         }

    //         $orders = $orders->paginate($fields['limit']);

    //         $data = array(
    //             'orders' => $orders,
    //         );

    //         $res    = Response::send('true', 
    //                            $data, 
    //                            $message ='Success', 
    //                            $code = 200);  
    //     }
    //     return $res;
    // }  
    // public function earningReport(Request $request)
    // {
    // 	$fields    = $request->input();
    //     $validator = Validator::make($request->all(), [
    //         'limit'   => 'required|numeric',
    //         'keyword' => 'nullable',
    //         'fuel_station' => 'nullable|numeric|exists:fuel_stations,id',
    //         'from_date'   => 'nullable|date_format:Y-m-d',
    //         'to_date'     => 'nullable|date_format:Y-m-d',


    //     ]);
    //     if ($validator->fails()) 
    //     {
    //         $errors = collect($validator->errors());
    //         $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
    //     } else 
    //     {

    //         $orders = CustomerOrder::select('customer_orders.id','customer_orders.fuel_station_id','customer_orders.amount_commision','customer_orders.delivery_charge_commision','customer_orders.total_commision',)
    //                                 ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
    //                                 ->with([
    //                                    'fuel_station'
    //                                     ])
    //                                 ->where('customer_orders.status','5')   
    //                                  ->where('customer_order_payments.status','2')   
    //                                 ->orderBy('customer_orders.id');
    //         if ($fields['keyword']) 
    //                 {

    //                     $search_text=$fields['keyword'];
    //                     $orders->where('customer_orders.amount_commision', 'Like', '%' . $search_text . '%')
    //                             ->orWhere('customer_orders.delivery_charge_commision', 'Like', '%' . $search_text . '%')
    //                             ->orWhere('customer_orders.total_commision', 'Like', '%' . $search_text . '%')
    //                             ->orWhereHas('fuel_station', function ($query)use($search_text) 
    //                                                 {
    //                                                     $query->where('users.name_en', 'Like', '%' . $search_text . '%')
    //                                                         ->orWhere('users.name_so', 'Like', '%' . $search_text . '%');
    //                                                     return $query;       
    //                                                 },)
    //                             ;

    //                 }
    //         if ($fields['fuel_station'])
    //                 {
    //                     $orders->where('customer_orders.fuel_station_id',$fields['fuel_station']);

    //                 }

    //         // if ($fields['from_date'] && $fields['to_date']) 
    //         //         {
    //         //             $orders
    //         //             //->whereDateBetween('customer_orders.created_at', [$fields['from_date'], $fields['from_date']])
    //         //             ->whereDate('customer_orders.created_at','>=', $fields['from_date']) 
    //         //             ->orWhereDate('customer_orders.created_at','<=',$fields['to_date']); 
    //         //         }        
           
    //         if (isset($fields['from_date'])) {
    //             $orders = $orders->where('customer_orders.created_at', '>=', $fields['from_date'] . ' 00:00:00');
    //         }
    //         if (isset($fields['to_date'])) {
    //             $orders = $orders->where('customer_orders.created_at', '<=', $fields['to_date'] . ' 23:59:59');
    //         }

    //         $orders = $orders->paginate($fields['limit']);

    //         $data = array(
    //             'orders' => $orders,
    //         );

    //         $res    = Response::send('true', 
    //                            $data, 
    //                            $message ='Success', 
    //                            $code = 200);  
    //     }
    //     return $res;
    // }   
}
