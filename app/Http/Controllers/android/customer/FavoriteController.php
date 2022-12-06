<?php

namespace App\Http\Controllers\android\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\android\customer\CustomerFavoriteStation;
use App\Models\android\customer\Customer;
use App\Models\service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Validator;

class FavoriteController extends Controller
{
    /*************
    Add/ Remove Favorite
    @params: fuel_station_id, type, lang
    type: 1. Add 2. Remove
    **************/
    public function addRemove(Request $request)
    {        
        $auth_user = auth('sanctum')->user();
        
        $validator = Validator::make($request->all(),
            [
                'fuel_station_id' => 'required|exists:fuel_stations,id',
                'type' => 'required|numeric|in:1,2'
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {

            $isAdded = CustomerFavoriteStation::select('fuel_station_id', 'customer_id')
                        ->where('fuel_station_id', $request->fuel_station_id)
                        ->where('customer_id', $auth_user->user_id)
                        ->first();

            if($request->type == 1) {                

                if($isAdded) {
                    $message = __('customer-error.favourite_exists_en');
                    if($request->lang == 2) {
                        $message = __('customer-error.favourite_exists_so');
                    }                 
                    $res = Response::send(false, [], $message, 400);

                } else {

                    $favorite = new CustomerFavoriteStation;
                    $favorite->customer_id = $auth_user->user_id;
                    $favorite->fuel_station_id = $request->fuel_station_id;

                    $favorite->created_at = date('Y-m-d H:i:s');
                    $favorite->updated_at = date('Y-m-d H:i:s');
                    if($favorite->save()) {

                        $message = __('customer-success.add_favorite_en');
                        if($request->lang == 2) {
                            $message = __('customer-success.add_favorite_so');
                        }    

                        $res = Response::send(true, [], $message, 200);
                    } else {
                        $message = __('customer-error.add_favorite_en');
                        if($request->lang == 2) {
                            $message = __('customer-error.add_favorite_so');
                        }                 
                        $res = Response::send(false, [], $message, 400);
                    }
                }

            } else {   

                $result = CustomerFavoriteStation::where('fuel_station_id', $request->fuel_station_id)
                ->where('customer_id', $auth_user->user_id)
                ->delete();             

                if($result) {
                    $message = __('customer-success.remove_favorite_en');
                    if($request->lang == 2) {
                        $message = __('customer-success.remove_favorite_so');
                    }    

                    $res = Response::send(true, [], $message, 200);
                } else {
                    $message = __('customer-error.remove_favorite_en');
                    if($request->lang == 2) {
                        $message = __('customer-error.remove_favorite_so');
                    }                 
                    $res = Response::send(false, [], $message, 400);
                }
            }
        }
        return $res;
    }


    /*************
    Favorites listing
    @params: 
    **************/
    public function index(Request $request)
    {
        $auth_user = auth('sanctum')->user();
        
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required'
        ]);

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $favorites = CustomerFavoriteStation::select('customer_favorite_stations.*')
                ->descending()
                ->where('customer_favorite_stations.customer_id', $auth_user->user_id)
                ->with([
                    'fuel_station'=> function($query) use($request) {
                        $query->select('fuel_stations.id', 'name_en', 'name_so', 'place', 'latitude', 'longitude',  'address', 'fuel_stations.status', 'fuel_stations.created_at')
                        ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                        ->where('role_id', 5);
                    }, 'fuel_station.fuels'
                ])
                ->get();  

            $data = array(
                'favorites' => $favorites,
            );

            $res = Response::send(true, $data, '', 200);
        }
        return $res;
    }
}
