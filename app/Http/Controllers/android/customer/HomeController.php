<?php

namespace App\Http\Controllers\android\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\android\customer\FuelStation;
use App\Models\android\customer\FuelStationStock;
use Illuminate\Support\Facades\DB;
use App\Models\service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Validator;

class HomeController extends Controller
{
    /*
     * Home Screen
     * @params: latitude, longitude
     */
    public function index(Request $request)
    {
        $auth_user = auth('sanctum')->user();
        $auth_user_id = $auth_user->user_id;
        $fuel_stations = [];
        $validator = Validator::make($request->all(),
            [
                'latitude' => 'required|numeric',
                'longitude' => 'required',
                'limit' => 'required',
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $fuel_stations = FuelStation::select('fuel_stations.id', 'name_en', 'name_so', 'place', 'latitude', 'longitude',  'address', 'fuel_stations.status', 'fuel_stations.created_at', DB::raw("ROUND(6371 * acos(cos(radians(" . floatval($request->latitude) . ")) 
                * cos(radians(fuel_stations.latitude)) 
                * cos(radians(fuel_stations.longitude) - radians(" . floatval($request->longitude) . ")) 
                + sin(radians(" .$request->latitude. ")) 
                * sin(radians(fuel_stations.latitude))), 2) AS distance"))
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->active()
                ->where('role_id', 5)
                ->whereIn('fuel_stations.id', function($query){
                    $query->select('fuel_station_id')
                    ->from(with(new FuelStationStock)->getTable())
                    ->where('status', 1);
                })
                ->orderBy('distance', 'asc')
                ->with([
                    'fuels' => function ($query) {
                        $query->select('fuel_type_id', 'fuel_en', 'fuel_so', 'price', 'stock')
                        ->where('fuel_station_stocks.status', '=', 1);
                    },

                    'favorites' => function ($query) use($auth_user_id) {
                        $query->select('customers.id', 'name_en', 'name_so', 'customers.created_at', 'customers.status')
                        ->where('customer_favorite_stations.customer_id', '=', $auth_user_id);
                    },
                ])
                ->paginate($request->limit);
        

            $sliders = DB::table('sliders')
                        ->select('image')
                        ->where('status', 1)
                        ->get();       
            
            $data = array(
                'fuel_stations' => $fuel_stations,
                'sliders' => $sliders,
            );

            $res = Response::send(true, $data, 'Data found', 200);
        }

        return $res;
    }

    /*
     * Home Search
     * @params: keyword, lang
     */
    public function search(Request $request) {
        $validator = Validator::make($request->all(),
            [
                'lang' => 'required',
            ]
        );

        $fuel_stations = [];
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $fuel_stations = FuelStation::select('fuel_stations.id', 'name_en', 'name_so', 'place', 'latitude', 'longitude',  'address', 'fuel_stations.status', 'fuel_stations.created_at')
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->active()
                ->where('role_id', 5)
                ->whereIn('fuel_stations.id', function($query){
                    $query->select('fuel_station_id')
                    ->from(with(new FuelStationStock)->getTable())
                    ->where('status', 1);
                });

            if ($request->lang == 1) {
                $fuel_stations->where('name_en', 'LIKE', $request->keyword . '%');
            }

            if ($request->lang == 2) {
                $fuel_stations->where('name_so', 'LIKE', $request->keyword . '%');
            }

            $fuel_stations = $fuel_stations->get();

            $data = array(
                'fuel_stations' => $fuel_stations,
            );  
            $res = Response::send(true, $data, '', 200); 
                
        }           

        return $res;


    }

    /*************
     * Fuel Station Fuels
     * @params: id
    **************/
    public function getFuelStationFuels(Request $request)
    {
        $auth_user = auth('sanctum')->user();
        $auth_user_id = $auth_user->user_id;
        $fuel_stations = [];
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|exists:fuel_stations,id',
                'latitude' => 'required',
                'longitude' => 'required',
            ],
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $fuel_station = FuelStation::select('fuel_stations.id', 'name_en', 'name_so', 'place', 'latitude', 'longitude',  'address', 'fuel_stations.status', 'fuel_stations.created_at', DB::raw("ROUND(6371 * acos(cos(radians(" . floatval($request->latitude) . ")) 
                * cos(radians(fuel_stations.latitude)) 
                * cos(radians(fuel_stations.longitude) - radians(" . floatval($request->longitude) . ")) 
                + sin(radians(" .$request->latitude. ")) 
                * sin(radians(fuel_stations.latitude))), 2) AS distance"))
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->active()
                ->where('role_id', 5)                
                ->with([
                    'fuels' => function ($query) {
                        $query->select('fuel_type_id', 'fuel_en', 'fuel_so', 'price', 'stock')
                       ->where('fuel_station_stocks.status', '=', 1);
                    },

                    'favorites' => function ($query) use($auth_user_id) {
                        $query->select('customers.id', 'name_en', 'name_so', 'customers.created_at', 'customers.status')
                        ->where('customer_favorite_stations.customer_id', '=', $auth_user_id);
                    },
                ])
                ->first();
            $data = array(
                'fuel_station' => $fuel_station,
            );
            $res = Response::send(true, $data, 'Fuel Station found', 200);
        }

        return $res;
    }
}
