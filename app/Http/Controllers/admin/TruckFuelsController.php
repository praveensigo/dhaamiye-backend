<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\admin\TruckFuel;
use App\Models\admin\TruckStockLog;
use App\Models\admin\FuelStationStock;
use App\Models\admin\FuelStationStockLog;

use App\Models\service\ResponseSender as Response;
use Validator;

class TruckFuelsController extends Controller
{
    public function Index(Request $request)
    {
       
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'truck_id' => 'required|numeric|exists:trucks,id',

        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
        $truck_fuels = DB::table('truck_fuels')
                         ->leftjoin('fuel_types', 'fuel_types.id', '=', 'truck_fuels.fuel_type_id')
                            ->select('truck_fuels.*','fuel_types.fuel_en','fuel_so')
                            ->where('truck_id',$fields['truck_id'])
                            ->get();
            $data = array(
                'truck_fuels' => $truck_fuels,
            );
            $res = Response::send(true, $data, 'Fuels found', 200);
        
    
      
    }
    return $res;
}
public function add(Request $request)
{   
    $fields = $request->input();
    $validator = Validator::make($request->all(), [
        'truck_id' => 'required|exists:trucks,id',
        'fuel_type_id' => 'required|exists:fuel_types,id',
        'stock' => 'required|numeric',
        'capacity' => 'required|numeric',
    ],
        [
            'stock.required' => 'Please Enter the Stock',
            'capacity.required' => 'Please Enter the Capacity',
            'fuel_type_id.required' => 'Please select the fuel',

        ]
    );
    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res = Response::send('false', $data = [], $message = $errors, $code = 422);
    } else {
        if($fields['stock'] <= $fields['capacity'])
        {
        $truck_details = DB::table('trucks')->select('trucks.*')->where('id',$fields['truck_id'])->first();
        $fuel_station_stock = DB::table('fuel_station_stocks')
                        ->select('fuel_station_stocks.stock')
                        ->where('fuel_station_id',$truck_details->fuel_station_id)
                        ->where('fuel_type_id',$fields['fuel_type_id'])
                        ->first();
        if($fields['stock'] <= $fuel_station_stock->stock)
        {
        
        $truck_fuel = new TruckFuel;
        $truck_fuel->truck_id = $fields['truck_id'];
        $truck_fuel->fuel_type_id = $fields['fuel_type_id'];
        $truck_fuel->capacity = $fields['capacity'];
        $truck_fuel->stock = $fields['stock'];
        $truck_fuel->created_at = date('Y-m-d H:i:s');
        $truck_fuel->updated_at = date('Y-m-d H:i:s');
        $result = $truck_fuel->save();
        if ($result) {
        $truck_stock = new TruckStockLog;
        $truck_stock->truck_id = $fields['truck_id'];
        $truck_stock->fuel_type_id = $fields['fuel_type_id'];
        $truck_stock->stock = $fields['stock'];
        $truck_stock->balance_stock = $fields['stock'];
        $truck_stock->type = 1;
        $truck_stock->created_at = date('Y-m-d H:i:s');
        $truck_stock->updated_at = date('Y-m-d H:i:s');
        $result1 = $truck_stock->save();
        if ($result1) {
            $truck = DB::table('trucks')->select('trucks.*')->where('id',$fields['truck_id'])->first();
                $fuel_stock = FuelStationStock::where('fuel_station_id', $truck->fuel_station_id)->where('fuel_type_id', $fields['fuel_type_id'])->first();
                $fuel_stock->stock = $fuel_stock->stock - $fields['stock'] ;
                $fuel_stock->updated_at = date('Y-m-d H:i:s');
                $result2 = $fuel_stock->save();
                   if($result2) {
                    $truck = DB::table('trucks')->select('trucks.*')->where('id',$fields['truck_id'])->first();
                        $fuel_log = new FuelStationStockLog;
                        $fuel_log->fuel_station_id = $truck->fuel_station_id;
                        $fuel_log->fuel_type_id = $fields['fuel_type_id'];
                        $fuel_log->stock = $fields['stock'] ;
                        $fuel_log->balance_stock = $fuel_stock->stock;
                        $fuel_log->type = 2 ;
                        $fuel_log->truck_id = $fields['truck_id'] ;
                        $fuel_log->added_by = 1;
                        $fuel_log->created_at = date('Y-m-d H:i:s');
                        $fuel_log->updated_at = date('Y-m-d H:i:s');
                        $result3 = $fuel_log->save();

                $res = Response::send('true',
                    $data = [],
                    $message = 'Stock added successfully',
                    $code = 200);
            }}
        } else {
            $res = Response::send('false',
                $data = [],
                $message = 'Failed to add stock.',
                $code = 400);
        }}
        else {
            $res = Response::send('false',
                $data = [],
                $message = 'Sorry.There is no enough stock.',
                $code = 400);
        }}
        else {
            $res = Response::send('false',
                $data = [],
                $message = 'Please make sure that the stock is less than or equal to the capacity.',
                $code = 400);

    }
        
    }
    return $res;
}
public function updateStock(Request $request)
    {   
        $fields = $request->input();
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|numeric|exists:truck_fuels,id',
                'stock' => 'required|numeric',
            ],
            [
                'stock.required' => 'Please enter the stock(in litres).',
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $truck_stock_details = DB::table('truck_fuels')->select('truck_fuels.*')->where('id',$fields['id'])->first();

            $truck_details = DB::table('trucks')->select('trucks.*')->where('id',$truck_stock_details->truck_id)->first();

            $fuel_station_stock = DB::table('fuel_station_stocks')
                        ->select('fuel_station_stocks.stock')
                        ->where('fuel_station_id',$truck_details->fuel_station_id)
                        ->where('fuel_type_id',$truck_stock_details->fuel_type_id)
                        ->first();
            if($fields['stock'] <= $fuel_station_stock->stock)
            {
            $truck_stock_details2 = DB::table('truck_fuels')->select('truck_fuels.*')->where('id',$fields['id'])->first();
            $current_stock = $truck_stock_details2->stock;
            $capacity = $truck_stock_details2->capacity;
            $check = $current_stock + $fields['stock'];
            if($check < $capacity)
            {
            $truck_fuel = TruckFuel::find($fields['id']);
            $truck_fuel->stock = $truck_fuel->stock + $fields['stock'];
            $truck_fuel->updated_at = date('Y-m-d H:i:s');
            $result = $truck_fuel->save();
            if ($result) {
                $truck_stock = new TruckStockLog;
                $truck_stock->truck_id = $truck_fuel->truck_id;
                $truck_stock->fuel_type_id = $truck_fuel->fuel_type_id;
                $truck_stock->stock = $fields['stock'];
                $truck_stock->balance_stock = $truck_fuel->stock;
                $truck_stock->type = 1;
                $truck_stock->created_at = date('Y-m-d H:i:s');
                $truck_stock->updated_at = date('Y-m-d H:i:s');
                $result1 = $truck_stock->save();
                if ($result1) {
                    $truck = DB::table('trucks')->select('trucks.*')->where('id',$truck_fuel->truck_id)->first();
                        $fuel_stock = FuelStationStock::where('fuel_station_id', $truck->fuel_station_id)->where('fuel_type_id', $truck_fuel->fuel_type_id)->first();
                        $fuel_stock->stock = $fuel_stock->stock - $fields['stock'] ;
                        $fuel_stock->updated_at = date('Y-m-d H:i:s');
                        $result2 = $fuel_stock->save();
                           if($result2) {
                            $truck = DB::table('trucks')->select('trucks.*')->where('id',$truck_fuel->truck_id)->first();
                                $fuel_log = new FuelStationStockLog;
                                $fuel_log->fuel_station_id = $truck->fuel_station_id;
                                $fuel_log->fuel_type_id = $truck_fuel->fuel_type_id;
                                $fuel_log->stock = $fields['stock'] ;
                                $fuel_log->balance_stock = $fuel_stock->stock;
                                $fuel_log->type = 2 ;
                                $fuel_log->truck_id = $truck_fuel->truck_id;
                                $fuel_log->added_by = 1;
                                $fuel_log->created_at = date('Y-m-d H:i:s');
                                $fuel_log->updated_at = date('Y-m-d H:i:s');
                                $result3 = $fuel_log->save();
                $res = Response::send(true, [], __('success.update_stock'), 200);
            } }}else {
                $res = Response::send(false, [], __('error.update_stock'), 400);

            }}
            else {
                $res = Response::send(false, [], 'Please make sure that the stock is less than or equal to the capacity', 400);

            }
        }
        else {
            $res = Response::send('false',
                $data = [],
                $message = 'Sorry.There is no enough stock.',
                $code = 400);
        }
        }
        return $res;
    }
    public function StockLogs(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'fuel_type_id' => 'required|numeric|exists:fuel_types,id',
            'truck_id' => 'required|numeric|exists:trucks,id',


        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {

            $truck_stock = TruckStockLog::select('truck_stock_logs.*', 'fuel_types.fuel_en', 'fuel_types.fuel_so','trucks.*','users.name_en','users.name_so' )
               ->join('fuel_types', 'fuel_types.id', '=', 'truck_stock_logs.fuel_type_id')
               ->join('trucks', 'trucks.id', '=', 'truck_stock_logs.truck_id')
               ->join('users', 'users.user_id', '=', 'trucks.fuel_station_id')
               ->where('truck_stock_logs.truck_id', $fields['truck_id'])
                ->where('truck_stock_logs.fuel_type_id', $fields['fuel_type_id'])
                ->where('users.role_id', 5)
                ->orderBy('truck_stock_logs.id', 'desc');

            // SEARCH BY KEYWORD
            if ($request->keyword) {
                $truck_stock->where(function ($query) use ($request) {
                    $query->where('truck_stock_logs.stock', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('truck_stock_logs.created_at', 'LIKE', '%' . $request->keyword . '%');});
            }

            $truck_stock = $truck_stock->paginate($fields['limit']);

            $data = array(
                'truck_stock' => $truck_stock,
            );
            $res = Response::send('true',
                $data,
                $message = 'Success',
                $code = 200);
        }
        return $res;
    }
}