<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\service\ResponseSender as Response;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Validator;

class LoginController extends Controller
{
    /**
     * * admin/subadmin login API
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Json
     */
    public function adminLogin(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6|max:16',
        ],
            [
                'email.required' => __('error.email_required'),
                'email.email' => __('error.email_valid'),
                'password.required' => __('error.password_required'),
                'password.min' => __('error.password_min_max'),
                'password.max' => __('error.password_min_max'),
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $email = $fields['email'];
            $user = new User;
            $check = User::where('email', $email)->first();
            if ($check) {
                if (Hash::check($fields['password'], $check->password)) {
                    if ($check->status == '1') {

                        if ($check->role_id == '1') {




                            $user = 'Admin';

                            





                        } elseif ($check->role_id == '2') {
                            $user = DB::table('sub_admins')->where('id', $check->user_id)->first();
                        }
                        $details = $check;
                        $user_details = $details;

                        /* Create Token */
                        $token = $details->createToken('my-app-token')->plainTextToken;
                        $user_details->token = $token;
                        $details = array(
                            'details' => $details,
                            'other_details' => $user,

                        );

                        $res = Response::send('true', $data = $details, $message = __('auth.login_succuss'), $code = 200);

                    } else {
                        $res = Response::send('false', $data = [], $message = __('auth.login_blocked_error'), $code = 400);
                    }
                } else {
                    $res = Response::send('false', $data = [], $message = ['password' => [__('auth.login_password_error')]], $code = 422);
                }
            } else {
                $res = Response::send('false', $data = [], $message = ['email' => [__('auth.login_email_error')]], $code = 422);
            }
        }
        return $res;
    }

    /**
     * * custome login API
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Json
     */
    public function userLogin(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => 'required|numeric|digits_between:6,14',
                'password' => 'required|min:6|max:16',
            ],
            [
                'country_code.required' => __('error.country_code_required'),
                'country_code.exists' => __('error.country_code_exists'),
                'mobile.required' => __('error.mobile_required'),
                'password.required' => __('error.password_required'),
                'password.min' => __('error.password_min_max'),
                'password.max' => __('error.password_min_max'),
            ]
        );
        
        if ($validator->fails()) {
            $errors = collect($validator->errors());

            $res = Response::send(false, [], $message = $errors, 422);
        } else {
            $user = new User;
            $customer = User::withTrashed()->where('mobile', $request->mobile)->where('country_code_id', $request->country_code)->where('role_id',3 )->first();
            if ($customer) {
                if ($customer->deleted_at) {
                    $res = Response::send(false, [], $message = ['mobile' => [__('error.account_deleted')]], 422);
                } else {

                    if (Auth::attempt(['country_code_id' => $request->country_code, 'mobile' => $request->mobile, 'password' => $request->password])) {

                        if (Auth::attempt(['country_code_id' => $request->country_code, 'mobile' => $request->mobile, 'password' => $request->password, 'status' => 1])) {

                            $customer->fcm = $request->fcm;
                            $customer->save();
                            if ($customer->role_id == '3') {
                                $user = DB::table('customers')->where('id', $customer->user_id)->first();

                            }
                            $details = $customer;
                            $user_details = $details;

                            /* Create Token */
                            $token = $details->createToken('my-app-token')->plainTextToken;
                            $user_details->token = $token;

                              /* User Array */
                            $user_array = [
                                'details' => $customer,
                                'other_details' => $user,

                            ];

                            $res = Response::send(true, $user_array, __('auth.login_success'), 200);

                        } else {
                            $res = Response::send(false, [], __('auth.account_suspended'), 401);
                        }
                    } else {
                        $res = Response::send(false, [], $message = ['password' => [__('auth.incorrect_password')]], 422);
                    }
                }
            } else {
                $res = Response::send(false, [], $message = ['mobile' => [__('auth.mobile_not_registered')]], 422);
            }
        }
        return $res;
    }

/*DRIVER LOGIN WITH PASSWORD*/
    public function driverLogin(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => 'required|numeric',
                'password' => 'required|min:6|max:16',
            ],
            [
                'country_code.required' => __('error.country_code_required'),
                'country_code.exists' => __('error.country_code_exists'),
                'mobile.required' => __('error.mobile_required'),
                'password.required' => __('error.password_required'),
                'password.min' => __('error.password_min_max'),
                'password.max' => __('error.password_min_max'),
            ]
        );
        
        if ($validator->fails()) {
            $errors = collect($validator->errors());

            $res = Response::send(false, [], $message = $errors, 422);
        } else {
            $user = new User;
            $driver = User::withTrashed()->where('mobile', $request->mobile)->where('country_code_id', $request->country_code)->where('role_id',4 )->first();
            if ($driver) {
                if ($driver->deleted_at) {
                    $res = Response::send(false, [], $message = ['mobile' => [__('error.account_deleted')]], 422);
                } else {

                    if (Auth::attempt(['country_code_id' => $request->country_code, 'mobile' => $request->mobile, 'password' => $request->password])) {

                        if (Auth::attempt(['country_code_id' => $request->country_code, 'mobile' => $request->mobile, 'password' => $request->password, 'status' => 1])) {

                            $driver->fcm = $request->fcm;
                            $driver->save();
                            if ($driver->role_id == '4') {
                                $user = DB::table('drivers')->where('id', $driver->user_id)->first();

                            } 
                            $details = $driver;
                            $user_details = $details;

                            /* Create Token */
                            $token = $details->createToken('my-app-token')->plainTextToken;
                            $user_details->token = $token;

                              /* User Array */
                            $user_array = [
                                'details' => $driver,
                                'other_details' => $user,

                            ];

                            $res = Response::send(true, $user_array, __('auth.login_success'), 200);

                        } else {
                            $res = Response::send(false, [], __('auth.account_suspended'), 401);
                        }
                    } else {
                        $res = Response::send(false, [], $message = ['password' => [__('auth.incorrect_password')]], 422);
                    }
                }
            } else {
                $res = Response::send(false, [], $message = ['mobile' => [__('auth.mobile_not_registered')]], 422);
            }
        }
        return $res;
    }

/*FUELSTATION LOGIN WITH PASSWORD*/
public function fuelStationLogin(Request $request)
{
    $validator = Validator::make($request->all(),
        [
            'country_code' => 'required|numeric|exists:country_codes,id',
            'mobile' => 'required|numeric',
            'password' => 'required|min:6|max:16',
        ],
        [
            'country_code.required' => __('error.country_code_required'),
            'country_code.exists' => __('error.country_code_exists'),
            'mobile.required' => __('error.mobile_required'),
            'password.required' => __('error.password_required'),
            'password.min' => __('error.password_min_max'),
            'password.max' => __('error.password_min_max'),
        ]
    );
    
    if ($validator->fails()) {
        $errors = collect($validator->errors());

        $res = Response::send(false, [], $message = $errors, 422);
    } else {
        $user = new User;
        $fuel_station = User::withTrashed()->where('mobile', $request->mobile)->where('country_code_id', $request->country_code)->where('role_id',5 )->first();
        if ($fuel_station) {
            if ($fuel_station->deleted_at) {
                $res = Response::send(false, [], $message = ['mobile' => [__('error.account_deleted')]], 422);
            } else {

                if (Auth::attempt(['country_code_id' => $request->country_code, 'mobile' => $request->mobile, 'password' => $request->password])) {

                    if (Auth::attempt(['country_code_id' => $request->country_code, 'mobile' => $request->mobile, 'password' => $request->password, 'status' => 1])) {

                        $fuel_station->fcm = $request->fcm;
                        $fuel_station->save();
                        if ($fuel_station->role_id == '5') {
                            $user = DB::table('fuel_stations')->where('id', $fuel_station->user_id)->first();

                        } 
                        $details = $fuel_station;
                        $user_details = $details;

                        /* Create Token */
                        $token = $details->createToken('my-app-token')->plainTextToken;
                        $user_details->token = $token;

                          /* User Array */
                        $user_array = [
                            'details' => $fuel_station,
                            'other_details' => $user,

                        ];

                        $res = Response::send(true, $user_array, __('auth.login_success'), 200);

                    } else {
                        $res = Response::send(false, [], __('auth.account_suspended'), 401);
                    }
                } else {
                    $res = Response::send(false, [], $message = ['password' => [__('auth.incorrect_password')]], 422);
                }
            }
        } else {
            $res = Response::send(false, [], $message = ['mobile' => [__('auth.mobile_not_registered')]], 422);
        }
    }
    return $res;
}


/*LOGOUT*/
    public function logout()
    {
        $user = auth('sanctum')->user();
        if ($user->tokens()->where('id', $user->currentAccessToken()->id)->delete()) {
            $res = Response::send('true', $data = [], $message = __('auth.logout_succuss'), $code = 200);
        } else {
            $res = Response::send('true', $data = [], $message = __('auth.logout_error'), $code = 400);
        }
        return $res;
    }
    //LOGIN WITH OTP
    public function loginWithOtp(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => 'required|numeric|digits_between:6,14',
            ],
            [
                'country_code.required' => __('error.country_code_required'),
                'country_code.exists' => __('error.country_code_exists'),
                'mobile.required' => __('error.mobile_required'),
            ]
        );
        
        if ($validator->fails()) {
            $errors = collect($validator->errors());

            $res = Response::send(false, [], $message = $errors, 422);
        } else {
            $user = new User;
            $customer = User::withTrashed()->where('mobile', $request->mobile)->where('country_code_id', $request->country_code)->where('role_id',3 )->first();
            if ($customer) {
                if ($customer->deleted_at) {
                    $res = Response::send(false, [], $message = ['mobile' => [__('error.account_deleted')]], 422);
                } else {

                            $customer->fcm = $request->fcm;
                            $customer->save();
                            if ($customer->role_id == '3') {
                                $user = DB::table('customers')->where('id', $customer->user_id)->first();

                            } 
                            $details = $customer;
                            $user_details = $details;

                            /* Create Token */
                            $token = $details->createToken('my-app-token')->plainTextToken;
                            $user_details->token = $token;

                              /* User Array */
                            $user_array = [
                                'details' => $customer,
                                'other_details' => $user,

                            ];

                            $res = Response::send(true, $user_array, __('auth.login_success'), 200);

                      
                    } 
                
            } else {
                $res = Response::send(false, [], $message = ['mobile' => [__('auth.mobile_not_registered')]], 422);
            }
        
        }
        return $res;
    }

        //LOGIN WITH OTP

    public function loginWithOtpDriver(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => 'required|numeric|digits_between:6,14',
            ],
            [
                'country_code.required' => __('error.country_code_required'),
                'country_code.exists' => __('error.country_code_exists'),
                'mobile.required' => __('error.mobile_required'),
            ]
        );
        
        if ($validator->fails()) {
            $errors = collect($validator->errors());

            $res = Response::send(false, [], $message = $errors, 422);
        } else {
            $user = new User;
            $driver = User::withTrashed()->where('mobile', $request->mobile)->where('country_code_id', $request->country_code)->where('role_id',4 )->first();
            if ($driver) {
                if ($driver->deleted_at) {
                    $res = Response::send(false, [], $message = ['mobile' => [__('error.account_deleted')]], 422);
                } else {

                            $driver->fcm = $request->fcm;
                            $driver->save();
                            if ($driver->role_id == '4') {
                                $user = DB::table('drivers')->where('id', $driver->user_id)->first();

                            } 
                            $details = $driver;
                            $user_details = $details;

                            /* Create Token */
                            $token = $details->createToken('my-app-token')->plainTextToken;
                            $user_details->token = $token;

                              /* User Array */
                            $user_array = [
                                'details' => $driver,
                                'other_details' => $user,

                            ];

                            $res = Response::send(true, $user_array, __('auth.login_success'), 200);

                      
                    } 
                
            } else {
                $res = Response::send(false, [], $message = ['mobile' => [__('auth.mobile_not_registered')]], 422);
            }
        
        }
        return $res;
    }

    //LOGIN WITH OTP 
    
    public function loginWithOtpFuelStation(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => 'required|numeric|digits_between:6,14',
            ],
            [
                'country_code.required' => __('error.country_code_required'),
                'country_code.exists' => __('error.country_code_exists'),
                'mobile.required' => __('error.mobile_required'),
            ]
        );
        
        if ($validator->fails()) {
            $errors = collect($validator->errors());

            $res = Response::send(false, [], $message = $errors, 422);
        } else {
            $user = new User;
            $fuel_station = User::withTrashed()->where('mobile', $request->mobile)->where('country_code_id', $request->country_code)->where('role_id',5 )->first();
            if ($fuel_station) {
                if ($fuel_station->deleted_at) {
                    $res = Response::send(false, [], $message = ['mobile' => [__('error.account_deleted')]], 422);
                } else {

                            $fuel_station->fcm = $request->fcm;
                            $fuel_station->save();
                            if ($fuel_station->role_id == '5') {
                                $user = DB::table('fuel_stations')->where('id', $fuel_station->user_id)->first();

                            } 
                            $details = $fuel_station;
                            $user_details = $details;

                            /* Create Token */
                            $token = $details->createToken('my-app-token')->plainTextToken;
                            $user_details->token = $token;

                              /* User Array */
                            $user_array = [
                                'details' => $fuel_station,
                                'other_details' => $user,

                            ];

                            $res = Response::send(true, $user_array, __('auth.login_success'), 200);

                      
                    } 
                
            } else {
                $res = Response::send(false, [], $message = ['mobile' => [__('auth.mobile_not_registered')]], 422);
            }
        
        }
        return $res;
    }

}