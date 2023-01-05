<?php

namespace App\Http\Controllers\fuelstation;

use App\Http\Controllers\Controller;
use App\Models\fuelstation\FuelStationPriceLog;
use App\Models\fuelstation\FuelStationStock;
use App\Models\fuelstation\FuelStationStockLog;
use App\Models\service\ResponseSender as Response;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class FuelController extends Controller
{
/*GET FUELSTOCKS*/
    public function index(Request $request)
    {
        $user_id = auth('sanctum')->user()->user_id;
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'status' => 'nullable|numeric|in:1,2', //1:Active, 2:Blocked

        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $fuels = FuelStationStock::select('fuel_station_stocks.*', 'fuel_types.fuel_en', 'fuel_types.fuel_so', )
                ->join('fuel_types', 'fuel_types.id', '=', 'fuel_station_stocks.fuel_type_id')
                ->where('fuel_station_stocks.fuel_station_id', $user_id)
                ->with([
                    'fuel_station', 'fuel_type',
                ])
                ->orderBy('id', 'desc');

            // SEARCH BY KEYWORD
            if ($request->keyword) {
                $fuels->where(function ($query) use ($request) {
                    $query->where('fuel_en', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('fuel_so', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('fuel_station_stocks.stock', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('fuel_station_stocks.price', 'LIKE', '%' . $request->keyword . '%')

                    ;});
            }

            if ($fields['status'] != '' && $fields['status'] != null) {
                $fuels->where('fuel_station_stocks.status', $fields['status']);
            }

            $fuels = $fuels->paginate($fields['limit']);

            $data = array(
                'fuels' => $fuels,
            );

            $res = Response::send('true',
                $data,
                $message = 'Success',
                $code = 200);
        }
        return $res;
    }

/*CREATE FUELS*/
    public function addFuel(Request $request)
    {$user_id = auth('sanctum')->user()->user_id;

        $fields = $request->input();
        $validator = Validator::make($request->all(),
            [
                'fuel_type_id' => 'required|numeric|exists:fuel_types,id',
                'price' => 'required|numeric',

            ],
            [
                'fuel_type_id.required' => 'Please select the fuel type.',
                'price.required' => 'Please enter the price.',
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);

        } else { $exist = FuelStationStock::where('fuel_type_id', $fields['fuel_type_id'])->where('fuel_station_id', $user_id)->first();
            if ($exist === null) {

                $stock = new FuelStationStock;
                $stock->fuel_station_id = $user_id;
                $stock->fuel_type_id = $fields['fuel_type_id'];
                $stock->price = $fields['price'];
                $role_id = auth('sanctum')->user()->role_id;
                $user_id = auth('sanctum')->user()->user_id;
                $stock->added_by = $role_id;
                $stock->added_user = $user_id;
                $result = $stock->save();

                if ($result) {$price = new FuelStationPriceLog;
                    $price->fuel_station_id = $stock->fuel_station_id;
                    $price->fuel_type_id = $stock->fuel_type_id;
                    $price->price = $stock->price;
                    $role_id = auth('sanctum')->user()->role_id;
                    $user_id = auth('sanctum')->user()->user_id;
                    $price->added_by = $role_id;
                    $price->added_user = $user_id;
                    $price->save();
                }
                $res = Response::send('true',
                    [],
                    $message = 'Fuel added successfully.',
                    $code = 200);
            } else {
                $res = Response::send('false',
                    [],
                    $message = 'Failed to add fuel.',
                    $code = 400);
            }
        }

        return $res;
    }
    //UPDATE FUELPRICE
    public function updatePrice(Request $request)
    {
        $user_id = auth('sanctum')->user()->user_id;
        $fields = $request->input();

        $validator = Validator::make($request->all(),
            [
                'fuel_type_id' => 'required|numeric|exists:fuel_types,id',
                'price' => 'required|numeric',
            ],
            [
                'price.required' => 'Please enter the price.',
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {

            $fuel_station = FuelStationStock::where('fuel_station_id', '=', $user_id)->where('fuel_type_id', '=', $fields['fuel_type_id'])->first();
            $fuel_station->price = $fields['price'];
            $result = $fuel_station->save();

            if ($result) {

                $price = new FuelStationPriceLog;
                $price->fuel_station_id = $user_id;
                $price->fuel_type_id = $fields['fuel_type_id'];
                $price->price = $fields['price'];
                $role_id = auth('sanctum')->user()->role_id;
                $user_id = auth('sanctum')->user()->user_id;
                $price->added_by = $role_id;
                $price->added_user = $user_id;
                $price->save();

                $res = Response::send(true, [], __('success.update_price'), 200);
            } else {
                $res = Response::send(false, [], __('error.update_price'), 400);
            }
        }
        return $res;
    }

    //UPDATE FUELSTOCK
    public function updateStock(Request $request)
    {
        $user_id = auth('sanctum')->user()->user_id;

        $fields = $request->input();
        $validator = Validator::make($request->all(),
            [
                'fuel_type_id' => 'required|numeric|exists:fuel_types,id',
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
            $fuel_station = FuelStationStock::where('fuel_station_id', '=', $user_id)->where('fuel_type_id', '=', $fields['fuel_type_id'])->first();
            $stock= $fuel_station->stock;          
            $fuel_station->stock =$stock+$fields['stock'];
            $result = $fuel_station->save();

            if ($result) {

                $stock = new FuelStationStockLog;
                $stock->fuel_station_id = $user_id;
                $stock->fuel_type_id = $fields['fuel_type_id'];
                $stock->stock = $fields['stock'];
                $stock->type = 1;
                $stock->balance_stock =$fuel_station->stock ;
                $role_id = auth('sanctum')->user()->role_id;
                $user_id = auth('sanctum')->user()->user_id;
                $stock->added_by = $role_id;
                $stock->added_user = $user_id;
                $stock->save();

                $res = Response::send(true, [], __('success.update_stock'), 200);
            } else {
                $res = Response::send(false, [], __('error.update_stock'), 400);

            }
        }
        return $res;
    }

/*GET FUELPRICELOGS*/
    public function fuelPriceLogs(Request $request)
    {$user_id = auth('sanctum')->user()->user_id;

        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'fuel_type_id' => 'required|numeric|exists:fuel_types,id',

        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {

            $fuel_price = FuelStationPriceLog::select('fuel_station_price_logs.*', 'fuel_types.fuel_en', 'fuel_types.fuel_so')->join('fuel_types', 'fuel_types.id', '=', 'fuel_station_price_logs.fuel_type_id')
                ->with([
                    'fuel_station', 'fuel_type', 'user',
                ])

                ->where('fuel_station_price_logs.fuel_station_id', $user_id)
                ->where('fuel_station_price_logs.fuel_type_id', $fields['fuel_type_id'])
                ->orderBy('fuel_station_price_logs.id', 'desc');

            // SEARCH BY KEYWORD
            if ($request->keyword) {
                $fuel_price->where(function ($query) use ($request) {
                    $query->where('fuel_station_price_logs.price', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('fuel_station_price_logs.created_at', 'LIKE', '%' . $request->keyword . '%');});
            }

            $fuel_price = $fuel_price->paginate($fields['limit']);

            $data = array(
                'fuel_price_logs' => $fuel_price,
            );

            $res = Response::send('true',
                $data,
                $message = 'Success',
                $code = 200);
        }
        return $res;
    }

/*GET FUELSTOCKLOGS*/
   
public function fuelStockLogs(Request $request)
    {$user_id = auth('sanctum')->user()->user_id;

        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'fuel_type_id' => 'required|numeric|exists:fuel_types,id',

        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {

           
            $fuel_stock = FuelStationStockLog::select('fuel_station_stock_logs.*', 'fuel_types.fuel_en', 'fuel_types.fuel_so', )->join('fuel_types', 'fuel_types.id', '=', 'fuel_station_stock_logs.fuel_type_id')
                ->with([
                    'fuel_station', 'fuel_type', 'user',
                ])
                ->where('fuel_station_stock_logs.fuel_station_id', $user_id)
                ->where('fuel_station_stock_logs.fuel_type_id', $fields['fuel_type_id'])
                ->orderBy('fuel_station_stock_logs.id', 'desc');

            // SEARCH BY KEYWORD
            if ($request->keyword) {
                $fuel_stock->where(function ($query) use ($request) {
                    $query->where('fuel_station_stock_logs.stock', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('fuel_station_stock_logs.created_at', 'LIKE', '%' . $request->keyword . '%');});
            }

            $fuel_stock = $fuel_stock->paginate($fields['limit']);

            $data = array(
                'fuel_stock_logs' => $fuel_stock,
            );

            $res = Response::send('true',
                $data,
                $message = 'Success',
                $code = 200);
        }
        return $res;
    }

}
