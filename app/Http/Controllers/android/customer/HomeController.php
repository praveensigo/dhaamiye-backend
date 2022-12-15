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
use App\Models\service\CollectionHelper;

class HomeController extends Controller
{
    /*
     * Home Screen
     * @params: latitude, longitude
     */
    // public function index(Request $request)
    // {
    //     $auth_user = auth('sanctum')->user();
    //     $auth_user_id = $auth_user->user_id;
    //     $fuel_stations = [];
    //     $validator = Validator::make($request->all(),
    //         [
    //             'latitude' => 'required|numeric',
    //             'longitude' => 'required',
    //             'limit' => 'required',
    //         ]
    //     );
    //     if ($validator->fails()) {
    //         $errors = collect($validator->errors());
    //         $res = Response::send(false, [], $message = $errors, 422);

    //     } else {
    //         $fuel_stations = FuelStation::select('fuel_stations.id', 'name_en', 'name_so', 'place', 'latitude', 'longitude',  'address', 'fuel_stations.status', 'fuel_stations.created_at', DB::raw("ROUND(6371 * acos(cos(radians(" . floatval($request->latitude) . ")) 
    //             * cos(radians(fuel_stations.latitude)) 
    //             * cos(radians(fuel_stations.longitude) - radians(" . floatval($request->longitude) . ")) 
    //             + sin(radians(" .$request->latitude. ")) 
    //             * sin(radians(fuel_stations.latitude))), 2) AS distance"))
    //             ->join('users', 'users.user_id', '=', 'fuel_stations.id')
    //             ->active()
    //             ->where('role_id', 5)
    //             ->whereIn('fuel_stations.id', function($query){
    //                 $query->select('fuel_station_id')
    //                 ->from(with(new FuelStationStock)->getTable())
    //                 ->where('status', 1);
    //             })
    //             ->orderBy('distance', 'asc')
    //             ->with([
    //                 'fuels' => function ($query) {
    //                     $query->select('fuel_type_id', 'fuel_en', 'fuel_so', 'price', 'stock')
    //                     ->where('fuel_station_stocks.status', '=', 1);
    //                 },

    //                 'favorites' => function ($query) use($auth_user_id) {
    //                     $query->select('customers.id', 'name_en', 'name_so', 'customers.created_at', 'customers.status')
    //                     ->where('customer_favorite_stations.customer_id', '=', $auth_user_id);
    //                 },
    //             ])
    //             ->paginate($request->limit);
        

    //         $sliders = DB::table('sliders')
    //                     ->select('image')
    //                     ->where('status', 1)
    //                     ->get();       
            
    //         $data = array(
    //             'fuel_stations' => $fuel_stations,
    //             'sliders' => $sliders,
    //         );

    //         $res = Response::send(true, $data, 'Data found', 200);
    //     }

    //     return $res;
    // }

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
            $results = FuelStation::select('fuel_stations.id', 'name_en', 'name_so', 'place', 'latitude', 'longitude',  'address', 'fuel_stations.status', 'fuel_stations.created_at')
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->active()
                ->where('role_id', 5)
                ->whereIn('fuel_stations.id', function($query){
                    $query->select('fuel_station_id')
                    ->from(with(new FuelStationStock)->getTable())
                    ->where('status', 1);
                })
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
                ->get()
                ->sortBy('distance');

                $fuel_stations = CollectionHelper::paginate($results, $request->limit);
        

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

            $fuel_stations = $fuel_stations->get()->makeHidden(['distance']) ;

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
            $fuel_station = FuelStation::select('fuel_stations.id', 'name_en', 'name_so', 'place', 'latitude', 'longitude',  'address', 'fuel_stations.status', 'fuel_stations.created_at')
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->active()
                ->where('role_id', 5)  
                ->where('fuel_stations.id', $request->id)              
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

    function GetDrivingDistance(Request $request)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$request->latitude1.",".$request->longitude1."&destinations=".$request->latitude2."%2C".$request->longitude2."&mode=driving&language=pl-PL&key=AIzaSyDmehs_u8H6kgD9d9aVV38RuAS-GSZT598";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response, true);
        //return $response_a;
        if(array_key_exists('distance', $response_a['rows'][0]['elements'][0]) ) {
            $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
            $time = $response_a['rows'][0]['elements'][0]['duration']['text'];
        
            $array = array('distance' => $dist, 'time' => $time);
            $exploded = explode(' ', $array['distance']);
            $distance   = intval($exploded[0]);
            return $distance;
        } else {
            return null;
        }
    }  
}
