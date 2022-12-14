<?php

namespace App\Http\Controllers\fuelstation;

use App\Http\Controllers\Controller;
use App\Models\admin\FuelStation;
use App\Models\service\ResponseSender as Response;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Validator;


class ProfileController extends Controller
{
    /*GET PROFILE*/
    public function profile(Request $request)
    {

        $user_id = auth('sanctum')->user()->user_id;

        $fuel_station_detail = FuelStation::select('fuel_stations.id', 'users.name_en', 'users.name_so', 'users.image', 'added_by', 'added_user', 'updated_by', 'updated_user', 'place', 'latitude', 'longitude', 'address', 'fuel_stations.status', 'country_code_id', 'country_code', 'mobile', 'email', 'role_id', 'fuel_stations.created_at')
            ->join('users', 'users.user_id', '=', 'fuel_stations.id')
            ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
            ->where('fuel_stations.id', $user_id)
            ->where('users.role_id', '5')
            ->first();
        $bank_details = DB::table('fuel_station_bank_details')->select('bank_name', 'branch', 'account_no', 'account_holder_name', 'ifsc_code', 'upi_id', 'account_type', 'created_at')
            ->where('fuel_station_id', $user_id)
            ->first();
        $count = DB::table('customer_orders')->select('fuel_stations.id')
            ->where('users.role_id', '5')
            ->join('users', 'users.user_id', '=', 'customer_orders.fuel_station_id')
            ->join('fuel_stations', 'fuel_stations.id', '=', 'customer_orders.fuel_station_id')
            ->where('customer_orders.fuel_station_id', $user_id)
            ->get();
        $number = count($count);
        $deposite = DB::table('fuel_station_deposits')->select('fuel_station_deposits.*')
            ->where('fuel_station_id', $user_id)
            ->first();
        $fuel_station_deposite = $deposite->balance;

        $data = array(
            'fuel_station_detail' => $fuel_station_detail,
            'bank_details' => $bank_details,
            'no_of_orders' => $number,
            'Earning balance' => $fuel_station_deposite,
        );

        $res = Response::send(true, $data, $message = 'Success', 200);
        return $res;
    }
 /*
    Update fuel_station
    @params: fuel_station, id
     */
    public function updateProfile(Request $request)
    {        $user_id = auth('sanctum')->user()->user_id;

        $fields = $request->input();
        $validator = Validator::make($request->all(),

       [
        
                'name_en' => 'nullable|required_without:name_so|min:3|max:100',
                'name_so' => 'nullable|required_without:name_en|min:3|max:100',
                'image' => 'nullable|mimes:png,jpg,jpeg|max:1024|dimensions:max_width=600,max_height=600',
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => ['required', 'numeric',
                    Rule::unique('users', 'mobile')->ignore($user_id,  'user_id')],
                'email' => ['required', 'email',
                    Rule::unique('users', 'email')->ignore($user_id, 'user_id')],
                'place' => 'required|min:3|max:100',
                'latitude' => 'required|min:3|max:100',
                'longitude' => 'required|min:3|max:100',
                'address' => 'required|min:3|max:100',
                'bank_name' => 'required|min:3|max:100',
                'branch' => 'required|min:9|max:18',
                'account_no' => 'required|min:9|max:18',
                'account_holder_name' => 'required|min:3|max:100',
                'account_type' => 'required|in:1,2',
                'ifsc_code' => 'required|min:9|max:12',
                'upi_id' => 'required|min:6|max:50|regex:^(.+)@(.+)$^',

            ],
            ['name_en.required_without' => __('error.name_en_required_without'),
                'name_so.required_without' => __('error.name_so_required_without'),
                'name.required' => __('error.name_required'),
                'name.min' => __('error.name_min'),
                'name.max' => __('error.name_max'),
                'image.mimes' => __('error.image_mimes'),
                'address.required' => __('error.address_required'),
                'address.min' => __('error.address_min'),
                'address.max' => __('error.address_max'),
                'country_code.required' => __('error.country_code_required'),
                'country_code.exists' => __('error.country_code_exists'),
                'mobile.required' => __('error.mobile_required'),
                'mobile.unique' => __('error.mobile_unique'),
                'email.required' => __('error.email_required'),
                'email.unique' => __('error.email_unique'),
                'place.required' => __('error.place_required'),
                'place.min' => __('error.place_min'),
                'place.max' => __('error.place_max'),
                'latitude.required' => __('error.latitude_required'),
                'latitude.min' => __('error.latitude_min'),
                'latitude.max' => __('error.latitude_max'),
                'longitude.required' => __('error.longitude_required'),
                'logitude.min' => __('error.logitude_min'),
                'logitude.max' => __('error.logitude_max'),
                'bank_name.required' => 'Please enter the bank name.',
                'branch.required' => 'Please enter the branch.',
                'account_type.required' => 'Please select the type,1:Savings account,2:Current account',
                'account_no.required' => 'Please enter the account number.',
                'account_holder_name.required' => 'Please enter the account holder name.',
                'ifsc_code.required' => 'Please enter the IFSC code.',
                'upi_id.required' => 'Please enter the upi id.',

            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $fuel_station = FuelStation::find($user_id);
            $role_id = auth('sanctum')->user()->role_id;
            $user_id = auth('sanctum')->user()->user_id;
            $fuel_station->updated_by = $role_id;
            $fuel_station->updated_user = $user_id;
            $fuel_station->place = $fields['place'];
            $fuel_station->address = $fields['address'];
            $fuel_station->latitude = $fields['latitude'];
            $fuel_station->longitude = $fields['longitude'];
            $result = $fuel_station->save();

            if ($result) {
                $user = User::where('user_id', $user_id)->where('role_id', '5')->first();
                $user->name_en = $fields['name_en'];
                $user->name_so = $fields['name_so'];
                $user->country_code_id = $fields['country_code'];
                $user->mobile = $fields['mobile'];
                $user->email = $fields['email'];
                $image_uploaded_path = '';
                if ($request->file('image') != null) {

                    $uploadFolder = 'admin/fuelStations';
                    $image = $request->file('image');
                    $image_uploaded_path = $image->store($uploadFolder, 'public');
                    $user->image = $image_uploaded_path;

                }

                $user->save();
                DB::table('fuel_station_bank_details')->where('fuel_station_id', $user_id)->update(
                    array('bank_name' => $fields['bank_name'],
                        'branch' => $fields['branch'],
                        'account_no' => $fields['account_no'],
                        'account_type' => $fields['account_type'],
                        'account_holder_name' => $fields['account_holder_name'],
                        'ifsc_code' => $fields['ifsc_code'],
                        'upi_id' => $fields['upi_id'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),

                    )
                );

                $res = Response::send('true',
                    [],
                    $message = __('success.update_fuel_station'),
                    $code = 200);
            } else {
                $res = Response::send('false',
                    [],
                    $message = __('error.update_fuel_station'),
                    $code = 400);
            }
        }

        return $res;
    }

//CHANGE PASSWORD
public function changePassword(Request $request)
{    {        $user_id = auth('sanctum')->user()->user_id;

    $validator = Validator::make($request->all(),
        [
            // 'password' => 'required|not_contains_space|min:6|max:16',
            'password' => 'required|min:6|max:16',
            'password_confirmation' => 'required|same:password',
        ],
        [
            'id.exists' => __('error.id_exists'),
            //'password.not_contains_space' => __('error.password_no_space'),
            'password.required' => __('error.password_required'),
            'password.min' => __('error.password_min'),
            'password.max' => __('error.password_max'),
            'password_confirmation.required' => 'Please enter the confirmation password',
            'password_confirmation.same' => 'The password confirmation does not match.',
        ]
    );
    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res = Response::send(false, [], $message = $errors, 422);
    
    
    } else {
        $user = User::where(['user_id' => $user_id, 'role_id' => 5])->first();
        $user->password = bcrypt($request->password);

        if ($user->save()) {
            $res = Response::send(true, [], __('success.change_password'), 200);
        } else {
            $res = Response::send(false, [], __('error.change_password'), 400);
        }
    }
    return $res;
}



}
}