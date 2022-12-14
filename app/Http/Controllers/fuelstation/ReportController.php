<?php

namespace App\Http\Controllers\fuelstation;

use App\Http\Controllers\Controller;
use App\Models\Service\ResponseSender as Response;
use Illuminate\Http\Request;
use App\Models\admin\CustomerOrder;
use Illuminate\Support\Facades\DB;
use Validator;
use PDF;

class ReportController extends Controller
{
    public function salesReport(Request $request)
    {
    	$fields    = $request->input();
        $validator = Validator::make($request->all(), [
            'limit'   => 'required|numeric',
            'keyword' => 'nullable',
            'from_date'   => 'nullable|date_format:Y-m-d',
            'to_date'     => 'nullable|date_format:Y-m-d',


        ]);
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else 
        {
            $user_id = auth('sanctum')->user()->user_id;
            $orders = CustomerOrder::select('customer_orders.*')
                                    ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
                                    ->with([
                                       'fuel_station'

                                        ])
                                     ->where('customer_order_payments.status','2')
                                     ->where('customer_orders.status','5') 
                                     ->where('customer_orders.fuel_station_id', $user_id)
                                    ->orderBy('customer_orders.id');
            if ($fields['keyword']) 
                    {

                        $search_text=$fields['keyword'];
                        $orders->where('customer_orders.fuel_quantity_price', 'Like', '%' . $search_text . '%')
                                ->orWhere('customer_orders.id', 'Like', '%' . $search_text . '%')
                                ;

                    }
           
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
    public function salesReportDownload(Request $request)
    {   
        header('Access-Control-Allow-Origin: *');
    	$fields    = $request->input();
        $validator = Validator::make($request->all(), [
            'from_date'   => 'required|date_format:Y-m-d',
            'to_date'     => 'required|date_format:Y-m-d',

        ]);
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else 
        {   
            $user_id = auth('sanctum')->user()->user_id;
            $start = $fields['from_date'] . ' 00:00:00';
            $end = $fields['to_date'] . ' 23:59:59';
            $name_en = DB::table('users')->select('users.name_en')
                            ->where('users.role_id','5') 
                            ->where('users.user_id',$user_id)->first()->name_en;
                if ($name_en != '' && $name_en != null)
                {
                    $fuel_station_name = $name_en;
                }else{
                    $fuel_station_name =DB::table('users')->select('users.name_en')
                    ->where('users.role_id','5') 
                    ->where('users.user_id',$user_id)->first()->name_so;
                }
           
                $orders = CustomerOrder::select('customer_orders.id')
                                    ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
                                     ->where('customer_order_payments.status','2')
                                     ->where('customer_orders.status','5') 
                                     ->where('customer_orders.created_at', '>=', $start) 
                                     ->where('customer_orders.created_at', '<=', $end)
                                     ->where('customer_orders.fuel_station_id',$user_id)
                                    ->orderBy('customer_orders.id')->get();
                   
        $i=1;        
        foreach($orders as $order)
            {
                $order->sl_no = $i;
                $order->order_id = $order->id;
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
                'fuel_station_name' => $fuel_station_name,
                'orders' => $orders,
            );
            $pdf   = PDF::loadView('fuelStation/SalesReport', $data);
            return $pdf->download('SalesReport.pdf');

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
            'from_date'   => 'nullable|date_format:Y-m-d',
            'to_date'     => 'nullable|date_format:Y-m-d',


        ]);
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else 
        {
            $user_id = auth('sanctum')->user()->user_id;
            $orders = CustomerOrder::select('customer_orders.id','customer_orders.fuel_station_id','customer_orders.tax','customer_orders.promotion_discount','customer_orders.total','customer_orders.amount_commission','customer_orders.delivery_charge_commission',DB::raw('(total - total_commission)as total_earning'))
                                    ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
                                    ->with([
                                       'fuel_station'
                                        ])
                                    ->where('customer_orders.status','5')   
                                     ->where('customer_order_payments.status','2') 
                                     ->where('customer_orders.fuel_station_id', $user_id)
                                    ->orderBy('customer_orders.id');
            if ($fields['keyword']) 
                    {

                        $search_text=$fields['keyword'];
                        $orders->where('customer_orders.amount_commission', 'Like', '%' . $search_text . '%')
                                ->orWhere('customer_orders.delivery_charge_commission', 'Like', '%' . $search_text . '%')
                                ->orWhere('customer_orders.total_commission', 'Like', '%' . $search_text . '%')
                                ->orWhereHas('fuel_station', function ($query)use($search_text) 
                                                    {
                                                        $query->where('users.name_en', 'Like', '%' . $search_text . '%')
                                                            ->orWhere('users.name_so', 'Like', '%' . $search_text . '%');
                                                        return $query;       
                                                    },)
                                ;

                    }
           
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
            'from_date'   => 'required|date_format:Y-m-d',
            'to_date'     => 'required|date_format:Y-m-d',

        ]);
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else 
        {   
            $user_id = auth('sanctum')->user()->user_id;
            $start = $fields['from_date'] . ' 00:00:00';
            $end = $fields['to_date'] . ' 23:59:59';
            $name_en = DB::table('users')->select('users.name_en')
                            ->where('users.role_id','5') 
                            ->where('users.user_id',$user_id)->first()->name_en;
            if ($name_en != '' && $name_en != null)
            {
                $fuel_station_name = $name_en;
            }else{
                $fuel_station_name =DB::table('users')->select('users.name_en')
                ->where('users.role_id','5') 
                ->where('users.user_id',$user_id)->first()->name_so;
            }
        $orders = CustomerOrder::select('customer_orders.id','customer_orders.created_at')
                             ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
                             ->where('customer_order_payments.status','2')
                            ->where('customer_orders.status','5') 
                            ->where('customer_orders.created_at', '>=', $start) 
                            ->where('customer_orders.created_at', '<=', $end)
                             ->where('customer_orders.fuel_station_id',$user_id)
                              ->orderBy('customer_orders.id')->get();
           
        $i=1;        
        foreach($orders as $order)
            {
                $order->sl_no = $i;
                $order->order_id = $order->id;
                $order->price =DB::table('customer_orders')->select('fuel_quantity_price')
                                        ->where('customer_orders.id',$order->id)->first()->fuel_quantity_price;
                $order->delivery_charge =DB::table('customer_orders')->select('delivery_charge')
                                        ->where('customer_orders.id',$order->id)->first()->delivery_charge;
                $order->tax =DB::table('customer_orders')->select('tax')
                                        ->where('customer_orders.id',$order->id)->first()->tax;
                $order->discount =DB::table('customer_orders')->select('promotion_discount')
                                        ->where('customer_orders.id',$order->id)->first()->promotion_discount;
                $order->total =DB::table('customer_orders')->select('total')
                                        ->where('customer_orders.id',$order->id)->first()->total;
                $order->amount_commission =DB::table('customer_orders')->select('amount_commission')
                                    ->where('customer_orders.id',$order->id)->first()->amount_commission;
                $order->delivery_charge_commission =DB::table('customer_orders')->select('delivery_charge_commission')
                                    ->where('customer_orders.id',$order->id)->first()->delivery_charge_commission;
                $order->total_earnings =DB::table('customer_orders')->select(DB::raw('(total - total_commission)as total_earning'))
                                    ->where('customer_orders.id',$order->id)->first()->total_earning;
                 $order->date =DB::table('customer_orders')->select('created_at')
                                    ->where('customer_orders.id',$order->id)->first()->created_at;
                        $i++;  
                        }
            $data = array(
                'from_date' => $start,
                'to_date' => $end,
                'orders' => $orders,
                'fuel_station_name' => $fuel_station_name,

            );
            $pdf   = PDF::loadView('fuelStation/EarningReport', $data);
            return $pdf->download('EarningReport.pdf');

            $res    = Response::send('true', 
                               $data, 
                               $message ='Success', 
                               $code = 200);  
        }
        return $res;
    } 
    public function commissionReport(Request $request)
    {
    	$fields    = $request->input();
        $validator = Validator::make($request->all(), [
            'limit'   => 'required|numeric',
            'keyword' => 'nullable',
            'from_date'   => 'nullable|date_format:Y-m-d',
            'to_date'     => 'nullable|date_format:Y-m-d',


        ]);
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else 
        {
            $user_id = auth('sanctum')->user()->user_id;
            $orders = CustomerOrder::select('customer_orders.id','customer_orders.fuel_station_id','customer_orders.delivery_charge','customer_orders.tax','customer_orders.promotion_discount','customer_orders.total','customer_orders.amount_commission','customer_orders.delivery_charge_commission', 'total_commission','customer_orders.created_at')
                                    ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
                                    ->with([
                                       'fuel_station'
                                        ])
                                    ->where('customer_orders.status','5')   
                                     ->where('customer_order_payments.status','2') 
                                     ->where('customer_orders.fuel_station_id', $user_id)
                                    ->orderBy('customer_orders.id');
            if ($fields['keyword']) 
                    {

                        $search_text=$fields['keyword'];
                        $orders->where('customer_orders.amount_commission', 'Like', '%' . $search_text . '%')
                                ->orWhere('customer_orders.delivery_charge_commission', 'Like', '%' . $search_text . '%')
                                ->orWhere('customer_orders.total_commission', 'Like', '%' . $search_text . '%')
                                ->orWhereHas('fuel_station', function ($query)use($search_text) 
                                                    {
                                                        $query->where('users.name_en', 'Like', '%' . $search_text . '%')
                                                            ->orWhere('users.name_so', 'Like', '%' . $search_text . '%');
                                                        return $query;       
                                                    },)
                                ;

                    }
           
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
    public function commissionReportDownload(Request $request)
    {   
        header('Access-Control-Allow-Origin: *');
    	$fields    = $request->input();
        $validator = Validator::make($request->all(), [
            'from_date'   => 'required|date_format:Y-m-d',
            'to_date'     => 'required|date_format:Y-m-d',

        ]);
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else 
        {   
            $user_id = auth('sanctum')->user()->user_id;
            $start = $fields['from_date'] . ' 00:00:00';
            $end = $fields['to_date'] . ' 23:59:59';
            $name_en = DB::table('users')->select('users.name_en')
                            ->where('users.role_id','5') 
                            ->where('users.user_id',$user_id)->first()->name_en;
            if ($name_en != '' && $name_en != null)
            {
                $fuel_station_name = $name_en;
            }else{
                $fuel_station_name =DB::table('users')->select('users.name_en')
                ->where('users.role_id','5') 
                ->where('users.user_id',$user_id)->first()->name_so;
            }
        $orders = CustomerOrder::select('customer_orders.id','customer_orders.created_at')
                             ->join('customer_order_payments','customer_order_payments.order_id','=','customer_orders.id')
                             ->where('customer_order_payments.status','2')
                            ->where('customer_orders.status','5') 
                            ->where('customer_orders.created_at', '>=', $start) 
                            ->where('customer_orders.created_at', '<=', $end)
                             ->where('customer_orders.fuel_station_id',$user_id)
                              ->orderBy('customer_orders.id')->get();
           
        $i=1;        
        foreach($orders as $order)
            {
                $order->sl_no = $i;
                $order->order_id = $order->id;
                $order->price =DB::table('customer_orders')->select('fuel_quantity_price')
                                        ->where('customer_orders.id',$order->id)->first()->fuel_quantity_price;
                $order->delivery_charge =DB::table('customer_orders')->select('delivery_charge')
                                        ->where('customer_orders.id',$order->id)->first()->delivery_charge;
                $order->tax =DB::table('customer_orders')->select('tax')
                                        ->where('customer_orders.id',$order->id)->first()->tax;
                $order->discount =DB::table('customer_orders')->select('promotion_discount')
                                        ->where('customer_orders.id',$order->id)->first()->promotion_discount;
                $order->total =DB::table('customer_orders')->select('total')
                                        ->where('customer_orders.id',$order->id)->first()->total;
                $order->amount_commission =DB::table('customer_orders')->select('amount_commission')
                                    ->where('customer_orders.id',$order->id)->first()->amount_commission;
                $order->delivery_charge_commission =DB::table('customer_orders')->select('delivery_charge_commission')
                                    ->where('customer_orders.id',$order->id)->first()->delivery_charge_commission;
                $order->total_commission =DB::table('customer_orders')->select('total_commission')
                                    ->where('customer_orders.id',$order->id)->first()->total_commission;
                 $order->date =DB::table('customer_orders')->select('created_at')
                                    ->where('customer_orders.id',$order->id)->first()->created_at;
                        $i++;  
                        }
            $data = array(
                'from_date' => $start,
                'to_date' => $end,
                'orders' => $orders,
                'fuel_station_name' => $fuel_station_name,

            );
            $pdf   = PDF::loadView('fuelStation/CommissionReport', $data);
            return $pdf->download('CommissionReport.pdf');

            $res    = Response::send('true', 
                               $data, 
                               $message ='Success', 
                               $code = 200);  
        }
        return $res;
    } 
}
