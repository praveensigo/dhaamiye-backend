<?php

namespace App\Http\Controllers\android\driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheckController extends Controller
{
    /*************
    Check if mobile number is registered
    @params: country_code, mobile, lang
    **************/
    public function isMobileRegistered(Request $request)
    {        

        $lang = [
            'country_code.required' => __('customer-error.country_code_required_en'),
            'mobile.required' => __('customer-error.mobile_required_en'),
        ];

        if($request->lang == 2) {
            $lang = [
            'country_code.required' => __('customer-error.country_code_required_so'),
            'mobile.required' => __('customer-error.mobile_required_so'),
            ];
        }
        $validator = Validator::make($request->all(),
            [
                'country_code' => 'required|numeric',
                'mobile' => 'required|numeric',
            ], $lang
            
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $user = User::withTrashed()->where('mobile', $request->mobile)->where('country_code_id', $request->country_code)->where('role_id', 4)->first();
            if ($user) {
                if($user->deleted_at) {
                    $message = __('customer-error.account_deleted_en');
                    if($request->lang  == 2) {
                        $message = __('customer-error.account_deleted_so');
                    }
                    $res = Response::send(false, [], $message = ['mobile' => $message], 422);
                } else {

                    /* User Array */
                    $user_array = [
                        'id' => $user->id,
                        'country_code' => $user->country_code_id,
                        'mobile' => $user->mobile,
                    ];

                    $message = __('customer-success.mobile_registered_en');
                    if($request->lang  == 2) {
                        $message = __('customer-success.mobile_registered_so');
                    }

                    $res = Response::send(true, $user_array, $message, 200);
                }

            } else {

                $message = __('customer-error.mobile_not_registered_en');
                if($request->lang  == 2) {
                    $message = __('customer-error.mobile_not_registered_so');
                }
                $res = Response::send(false, [], $message = ['mobile' => $message], 422);
            }
        }
        return $res;
    }

    /*************
    Check if mobile number and email is already registered
    @params: country_code, mobile, email, lang
    **************/
    public function isRegistrable(Request $request)
    {        
        $lang = [
                'country_code.required' => __('customer-error.country_code_required_en'),
                'mobile.required' => __('customer-error.mobile_required_en'),
            ];
        if($request->lang == 2) {
            $lang = [
                'country_code.required' => __('customer-error.country_code_required_so'),
                'mobile.required' => __('customer-error.mobile_required_so'),
            ];
        }
        $validator = Validator::make($request->all(),
            [
                'country_code' => 'required|numeric',
                'mobile' => 'required|numeric',
                'email' => 'nullable|email'
            ], $lang
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $user = User::withTrashed()->where('mobile', $request->mobile)->where('country_code_id', $request->country_code)->where('role_id', 4)->first();

            if ($user) {
                if($user->deleted_at) {
                    $message = __('customer-error.account_deleted_en');
                    if($request->lang  == 2) {
                        $message = __('customer-error.account_deleted_so');
                    }
                    $res = Response::send(false, [], $message = ['mobile' => [$message]], 422);

                } else {
                    $message = __('customer-error.mobile_exists_en');
                    if($request->lang  == 2) {
                        $message = __('customer-error.mobile_exists_so');
                    }
                    $res = Response::send(false, [], $message = ['mobile' => $message], 422);
                }

            } else {
                if($request->email) {

                    $user = User::withTrashed()->where('email', $request->email)->where('role_id', 4)->first();

                    if ($user) {
                        $message = __('customer-error.email_exists_en');
                        if($request->lang  == 2) {
                            $message = __('customer-error.email_exists_so');
                        }
                        $res = Response::send(false, [], $message = ['email' => $message], 422);
                    } else {
                        $res = Response::send(true, [], '', 200);
                    }
                } else {
                    $res = Response::send(true, [], '', 200);
                }
            }
        }
        return $res;
    }

    /*************
     * Reset password
     * @params: id, password, password_confirmation,
    **************/

    public function resetPassword(Request $request)
    {

        $lang = [
                    'id.exists' => __('customer-error.exists_en'),
                    'password.required' => __('customer-error.password_required_en'),
                    'password.min' => __('customer-error.password_min_max_en'),
                    'password.max' => __('customer-error.password_min_max_en'),
                    'password.confirmed' => __('customer-error.password_confirmed_en'),
            ];
        if($request->lang == 2) {
            $lang = [
                    'id.exists' => __('customer-error.exists_so'),
                    'password.required' => __('customer-error.password_required_so'),
                    'password.min' => __('customer-error.password_min_max_so'),
                    'password.max' => __('customer-error.password_min_max_so'),
                    'password.confirmed' => __('customer-error.password_confirmed_so'),
            ];
        }
        $validator = Validator::make($request->all(),
            [
                    
                'id' => 'required|exists:users,id',
                'password' => 'required|min:6|max:16|confirmed',
            ], $lang            
        );

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $user = User::find($request->id);
            if($user) {
                $user->password = bcrypt($request->password);

                if ($user->save()) {

                    $message = __('customer-success.change_password_en');
                    if($request->lang == 2) {
                        $message = __('customer-success.change_password_so');
                    }     
                    $res = Response::send(true, [], $message, 200);

                } else {

                    $message = __('customer-error.change_password_en');
                    if($request->lang == 2) {
                        $message = __('customer-error.change_password_so');
                    }
                    $res = Response::send(false, [], $message, 400);
                }
            } else {

                $message = __('customer-error.exists_en');
                if($request->lang == 2) {
                    $message = __('customer-error.exists_so');
                }
                $res = Response::send(false, [], $message = $message, 422);
            }
        }

        return $res;
    }
}
