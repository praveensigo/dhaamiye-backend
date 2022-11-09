<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\android\Customer;
use App\Models\android\FuelStation;
use App\Models\android\SubAdmin;
use App\Models\web\DriverFuelStation;
use App\Models\android\Driver;
use App\Models\Service\ResponseSender as Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class RegisterController extends Controller
{
    public function customer(Request $request)
    {
        $fields    = $request->input();
        $validator = Validator::make($request->all(), [
                'name' => 'required|min:3|max:100',
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => 'required|numeric|digits:10|starts_with:6,7,8,9|unique:users,mobile',
                'email'  => 'nullable|unique:users,email|email|max:200|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
                'password'    => 'required|min:6|max:16',
                'password_confirmation' => 'required|same:password|min:6|max:16',
                'fcm'            => 'nullable'
            ],
            [
                'name.required' =>  __('error2.name_required'),
                'name.min' => __('error2.name_min'),
                'name.max' =>  __('error2.name_max'),
                'country_code.required' => __('error2.country_code_required'),
                'country_code.exists' => __('error2.country_code_exists'),
                'country_code.numeric' => __('error2.country_code_numeric'),
                'mobile.required' =>  __('error2.mobile_required'),
                'mobile.unique' => __('error2.mobile_unique'),
                'mobile.exists' => __('error2.mobile_exists'),
                'email.exists' => __('error2.email_exists'),
                'email.unique' => __('error2.email_unique'),
                'password.required' => __('error2.password_required'),
                'password_confirmation.required'=> __('error2.password_confirmation_required'),
                'password_confirmation.same' =>  __('error2.password_confirmation_same'),
                'password.min' => __('error2.password_min'),
                'password.max' => __('error2.password_max'),
            ]
        );
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } 
        else
        {
            $customer              = new Customer;  
            $customer->added_by        = '3';
            $customer->created_at  = date('Y-m-d H:i:s');
            $result               = $customer->save();
            if($result)
            {
                $user  = new User;
                $user->name_en        = $fields['name'];
                $user->country_code_id = $fields['country_code'];
                $user->mobile    = $fields['mobile'];
                $user->email     = $fields['email'];
                $user->password  = bcrypt($fields['password']);
                $user->role_id   = 3;
                $user->user_id   = $customer->id;
                $user->reg_status  = 1;
                $user->fcm       = $fields['fcm'];
                $user->created_at= date('Y-m-d H:i:s');
                $user->save();

                $user_details = DB::table('users')
                                    ->select('customers.id','users.name_en','users.country_code_id','country_codes.country_code','users.mobile','users.email')
                                    ->join('customers','customers.id','=','users.user_id')
                                    ->join('country_codes','country_codes.id','=','users.country_code_id')
                                    ->where('users.role_id','3')
                                    ->where('customers.id', $customer->id)
                                    ->first();

                /* Create Token */
                $token = $user->createToken('my-app-token')->plainTextToken;
                $user_details->token = $token;
                 
                $res    = Response::send('true', 
                                       $data = $user_details, 
                                       $message = __('message_android.register_success'), 
                                       $code = 200);

            }
            else
            {
                $res    = sendResponse('false', 
                                       $data = [], 
                                       $message = __('message_android.register_error'), 
                                       $code = 400);
            }
        }
        return $res;
    }    
 public function driver(Request $request)
    {
        $fields    = $request->input();
        $validator = Validator::make($request->all(), [
                'name_en' => 'nullable|min:3|max:100',
                'name_so' => 'nullable|min:3|max:100',
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => 'required|numeric|digits:10|starts_with:6,7,8,9|unique:users,mobile',
                'email'  => 'nullable|unique:users,email|email|max:200|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
                'fuel_station' => 'required|numeric|exists:fuel_stations,id',
                'image'     => 'required|max:4096|mimes:png,jpg,jpeg',
                'passport'     => 'required|max:4096|mimes:png,jpg,jpeg',
                'license'     => 'required|max:4096|mimes:png,jpg,jpeg',
                'license_expiry'    => 'required|date_format:Y-m-d|after:today',
                'password'    => 'required|min:6|max:16',
                'password_confirmation' => 'required|same:password|min:6|max:16',
                'fcm'            => 'nullable'
            ],
            [
                //'name_en.required' =>  __('error2.name_en_required'),
                'name_en.min' => __('error2.name_en_min'),
                'name_en.max' =>  __('error2.name_en_max'),
                //'name_so.required' =>  __('error2.name_so_required'),
                'name_so.min' => __('error2.name_so_min'),
                'name_so.max' =>  __('error2.name_so_max'),
                'country_code.required' => __('error2.country_code_required'),
                'country_code.exists' => __('error2.country_code_exists'),
                'country_code.numeric' => __('error2.country_code_numeric'),
                'mobile.required' =>  __('error2.mobile_required'),
                'mobile.unique' => __('error2.mobile_unique'),
                'mobile.exists' => __('error2.mobile_exists'),
                'email.exists' => __('error2.email_exists'),
                'email.unique' => __('error2.email_unique'),
                'fuel_station.required' => __('error2.fuel_station_required'),
                'fuel_station.exists' => __('error2.fuel_station_exists'),
                'fuel_station.numeric' => __('error2.fuel_station_numeric'),
                'password.required' => __('error2.password_required'),
                'password_confirmation.required'=> __('error2.password_confirmation_required'),
                'password_confirmation.same' =>  __('error2.password_confirmation_same'),
                'password.min' => __('error2.password_min'),
                'password.max' => __('error2.password_max'),
                'image.required'=> __('error2.image_required'),
                'image.mimes'=> __('error2.image_mimes'),
                'image.max'=> __('error2.image_max'),
                'passport.required'=> __('error2.passport_required'),
                'passport.mimes'=> __('error2.passport_mimes'),
                'passport.max'=> __('error2.passport_max'),  
                'license.required'=> __('error2.license_required'),
                'license.mimes'=> __('error2.license_mimes'),
                'license.max'=> __('error2.license_max'),
                'license_expiry.required'=> __('error2.license_expiry_required'),

            ]
        );
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } 
        else
        {
            $driver              = new Driver;  
            $driver->license_expiry = $fields['license_expiry'];
            $driver->fuel_station_id = $fields['fuel_station'];
            $driver->added_by  = '4';
            $driver->created_at  = date('Y-m-d H:i:s');

            $passport_uploaded_path = '';
            if ($request->file('passport')!=null) {
                $uploadFolder = 'driver/passport';
                $image = $request->file('passport');
                $passport_uploaded_path = $image->store($uploadFolder, 'public');
            }
            $driver->passport_url= $passport_uploaded_path;


            $license_uploaded_path = '';
            if ($request->file('license')!=null) {
                $uploadFolder = 'driver/license';
                $image = $request->file('license');
                $license_uploaded_path = $image->store($uploadFolder, 'public');
            }
            $driver->license_url = $license_uploaded_path;

            $result               = $driver->save();
            if($result)
            {
                $user  = new User;
                $user->name_en        = $fields['name_en'];
                $user->name_so        = $fields['name_so'];
                $image_uploaded_path = '';
                    if ($request->file('image')!=null) {
                        $uploadFolder = 'driver/images';
                        $image = $request->file('image');
                        $image_uploaded_path = $image->store($uploadFolder, 'public');
                    }
                    $user->image= $image_uploaded_path;

                $user->country_code_id = $fields['country_code'];
                $user->mobile    = $fields['mobile'];
                $user->email     = $fields['email'];
                $user->password  = bcrypt($fields['password']);
                $user->role_id   = 4;
                $user->user_id   = $driver->id;
                $user->reg_status  = 0;
                $user->fcm       = $fields['fcm'];
                $user->created_at= date('Y-m-d H:i:s');
                $result1 = $user->save();
               
                $user_details = DB::table('users')
                                    ->select('drivers.id','users.name_en','users.name_so','users.image','users.country_code_id','country_codes.country_code','users.mobile','users.email')
                                    ->join('drivers','drivers.id','=','users.user_id')
                                    ->join('country_codes','country_codes.id','=','users.country_code_id')
                                    ->where('users.role_id','4')
                                    ->where('drivers.id', $driver->id)
                                    ->first();

                /* Create Token */
                $token = $user->createToken('my-app-token')->plainTextToken;
                $user_details->token = $token;
                 
                $res    = Response::send('true', 
                                       $data = $user_details, 
                                       $message = __('message_android.register_success'), 
                                       $code = 200);

            }
            else
            {
                $res    = sendResponse('false', 
                                       $data = [], 
                                       $message = __('message_android.register_error'), 
                                       $code = 400);
            }
        }
        return $res;
    
}
}