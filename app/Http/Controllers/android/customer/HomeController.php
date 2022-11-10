<?php

namespace App\Http\Controllers\android\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\android\customer\FuelStation;
use App\Models\android\customer\FuelStationStock;
use Illuminate\Support\Facades\DB;
use App\Models\Service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Validator;

class HomeController extends Controller
{
    /*
     * Home Screen
     * @params: 
     */
    public function index()
    {
        $auth_user = auth('sanctum')->user();

        
        $validator = Validator::make($request->all(),
            [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ],[]
            
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $fuel_stations = FuelStation::select('fuel_stations.id', 'name_en', 'name_so', 'place', 'latitude', 'longitude',  'address' 'status', 'created_at')
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->active()
                ->where('role_id', 5)
                ->whereIn('id', function($query){
                    $query->select('fuel_station_id')
                    ->from(with(new FuelStationStock)->getTable())
                    ->where('status', 1);
                })
                ->orderBy('distance', 'asc')
                ->with([
                    'fuels' => function ($query) {
                        $query->where('fuel_station_stocks.status', '=', 1)
                    },
                ]);
                ->get();
        }

        $sliders = DB::table('sliders')
                    ->select('image')
                    ->where('status', 1)
                    ->get();

        // DB::raw("6371 * acos(cos(radians(" . floatval($lat) . ")) 
        //         * cos(radians(users.lat)) 
        //         * cos(radians(users.lon) - radians(" . floatval($lon) . ")) 
        //         + sin(radians(" .$lat. ")) 
        //         * sin(radians(users.lat))) AS distance")


        
       
        $data = array(
            'fuel_stations' => $fuel_stations,
            'sliders' => $sliders,
        );

        return Response::send(true, $data, 'Data found', 200);
    }
}
