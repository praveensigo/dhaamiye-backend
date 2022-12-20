<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Service\ResponseSender as Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class RatingController extends Controller
{

    // CUSTOMER'S RATINGS
    public function customerRatings(Request $request)
    {  
        $fields    = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'star_rating'=>'nullable|numeric|in:1,2,3,4,5',


        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {

            $customer_ratings = DB::table('ratings')->select('ratings.id', 'ratings.order_id', 'ratings.role_id', 'ratings.user_id', 'ratings.review', 'ratings.star_rating', 'users.name_en', 'users.name_so', 'users.email', 'users.mobile', 'users.country_code_id', 'country_codes.country_code', 'ratings.created_at')
                ->leftjoin("users", function ($join) {
                    $join->on("users.id", "=", "ratings.user_id")
                        ->on("users.role_id", "=", "ratings.role_id");
                })
                ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->where('ratings.role_id', '3')
                ->orderBy('id', 'desc');

            // SEARCH BY KEYWORD
            if ($request->keyword) {
                $customer_ratings->where(function ($query) use ($request) {
                    $query->where('name_en', 'LIKE', '%' . $request->keyword . '%')
                        ->where('name_so', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('mobile', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('review', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('order_id', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('star_rating', 'LIKE', '%' . $request->keyword . '%')

                    ;});
            }
               if ($fields['star_rating'] != '' && $fields['star_rating'] != null) {
                $customer_ratings->where('star_rating', $fields['star_rating']);
            }

            // PAGINATE
            $customer_ratings = $customer_ratings->paginate($request->limit);

            $data = array(
                'customer_ratings' => $customer_ratings,
            );

            $res = Response::send(true, $data, '', 200);
        }
        return $res;
    }
    // DRIVER'S RATINGS
    public function driverRatings(Request $request)
    {
        $fields    = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'star_rating'=>'nullable|numeric|in:1,2,3,4,5',


        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {

            $driver_ratings = DB::table('ratings')->select('ratings.id', 'ratings.order_id', 'users.role_id as urole_id', 'ratings.role_id', 'ratings.user_id', 'ratings.review', 'ratings.star_rating', 'users.name_en', 'users.name_so', 'users.email', 'users.mobile', 'users.country_code_id', 'country_codes.country_code', 'ratings.created_at')
                ->leftjoin("users", function ($join) {
                    $join->on("users.id", "=", "ratings.user_id")
                        ->on("users.role_id", "=", "ratings.role_id");
                })
                ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->where('ratings.role_id', '4')
                ->orderBy('id', 'desc');

            // SEARCH BY KEYWORD
            if ($request->keyword) {
                $driver_ratings->where(function ($query) use ($request) {
                    $query->where('name_en', 'LIKE', '%' . $request->keyword . '%')
                        ->where('name_so', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('mobile', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('review', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('order_id', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('star_rating', 'LIKE', '%' . $request->keyword . '%')

                    ;});
            }
            if ($fields['star_rating'] != '' && $fields['star_rating'] != null) {
                $driver_ratings->where('star_rating', $fields['star_rating']);
            }
            // PAGINATE
            $driver_ratings = $driver_ratings->paginate($request->limit);

            $data = array(
                'driver_ratings' => $driver_ratings,
            );

            $res = Response::send(true, $data, '', 200);
        }
        return $res;
    }
    //
}
