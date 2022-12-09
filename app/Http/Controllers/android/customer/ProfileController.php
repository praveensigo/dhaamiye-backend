<?php

namespace App\Http\Controllers\android\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\android\customer\Customer;
use App\Models\android\customer\User;
use App\Models\service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Validator;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /*************
     * Customer Profile
     * @params: lang
    **************/
    public function index()
    {
        $auth_user      = auth('sanctum')->user();
        $customer  = Customer::select('customers.id', 'name_en', 'name_so', 'country_code', 'users.mobile', 'users.email', 'users.image', 'customers.created_at', 'customers.status')
                    ->join('users','users.user_id','=','customers.id')
                    ->join('country_codes','country_codes.id','=','users.country_code_id')                    
                    ->where('customers.id',$auth_user->user_id)
                    ->where('users.role_id','3')
                    ->first();        

        if($customer) {
            $res = Response::send(
                'true', 
                $data = [
                    'customer' => $customer,
                    'auth_user' => $auth_user,
                    //'msg' => $msg

                ], 
                $message = '', 
                $code    = 200
            ); 
        }
        else {

            $message = __('customer-error.exists_en');

            if($request->lang==2) {
                $message = __('customer-error.exists_so');
            }             

            $res = Response::send(
                'false', 
                $data    = [], 
                $message = $message, 
                $code    = 404
            );   
        }      
        return $res; 
    }

    /*************
    Update profile
    @params: name_en, name_so, country_code, mobile, email, profile_image
    **************/
    public function update(Request $request)
    {
        $auth_user = auth('sanctum')->user();

        
        $lang =   [
                'name_en.required_without' => __('customer-error.name_required_en'),
                'name_so.required_without' => __('customer-error.name_required_en'),
                'country_code.exists' => __('customer-error.exists_en'),
                'mobile.required' => __('customer-error.mobile_required_en'),
                'mobile.unique' => __('customer-error.mobile_unique_en'),
                'email.email' => __('customer-error.email_valid_en'),
                'email.unique' => __('customer-error.email_unique_en'),
                'profile_image.dimensions' => __('customer-error.profile_image_dimensions_en'),
                'profile_image.max' => __('customer-error.profile_image_max_en'),
        ];

        if($request->lang == 2) {
            $lang =   [
                'name_en.required_without' => __('customer-error.name_required_so'),
                'name_so.required_without' => __('customer-error.name_required_so'),
                'country_code.exists' => __('customer-error.exists_so'),
                'mobile.required' => __('customer-error.mobile_required_so'),
                'mobile.unique' => __('customer-error.mobile_unique_so'),
                'email.email' => __('customer-error.email_valid_so'),
                'email.unique' => __('customer-error.email_unique_so'),
                'profile_image.dimensions' => __('customer-error.profile_image_dimensions_so'),
                'profile_image.max' => __('customer-error.profile_image_max_so'),
            ];
        }

        $validator = Validator::make($request->all(),
            [
                'name_en' => 'required_without:name_so|nullable|min:3|max:100',    
                'name_so' => 'required_without:name_en|nullable|min:3|max:100',              
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => [
                    'bail',
                    'required',                    
                    Rule::unique('users', 'mobile')->ignore($auth_user->id, 'id'),
                ],

                'email' => [
                    'bail',
                    'nullable',
                    'email',
                    Rule::unique('users', 'email')->ignore($auth_user->id, 'id'),
                ],
                
                'profile_image' => 'nullable|mimes:png,jpg,jpeg|max:1024|dimensions:max_width=600,max_height=600',
            ], $lang
            
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $user = User::find($auth_user->id);
            $user->name_en = $request->name_en;  
            $user->name_so = $request->name_so;           
            $user->country_code_id = $request->country_code;
            $user->mobile = $request->mobile;
            $user->email = $request->email;           

            $path = null;
            if ($request->hasFile('image')) {
                $uploadFolder = 'customers';
                $image = $request->file('image');
                $path = $image->store($uploadFolder, 'public');
            }
            
            if ($path) {
                $user->image = $path;
            }

            if ($user->save()) {

                $customer = Customer::find($user->user_id);
                $customer->updated_at = date('Y-m-d H:i:s');
                $customer->updated_by = 3;
                $customer->save();

                $message = __('customer-success.update_profile_en');

                if($request->lang  == 2) {
                    $message = __('customer-success.update_profile_so');
                }

                $res = Response::send(true, [], $message, 200);

            } else {

                $message = __('customer-error.update_profile_en');
                if($request->lang  == 2) {
                    $message = __('customer-error.update_profile_so');
                }

                $res = Response::send(false, [], $message, 400);
            }
        }
        return $res;
    }

    /*************
     * Change password
     * @params: current_password, password, password_confirmation
    **************/

    public function changePassword(Request $request)
    {
        $auth_user = auth('sanctum')->user();

        $lang = [
                'current_password.required' => __('customer-error.current_password_required_en'),
                'password.not_contains_space' => __('customer-error.password_no_space_en'),
                'password.required' => __('customer-error.password_required_en'),
                'password.min' => __('customer-error.password_min_max_en'),
                'password.max' => __('customer-error.password_min_max_en'),
                'password.confirmed' => __('customer-error.password_confirmed_en'),
            ];
        if($request->lang == 2) {
            $lang = [
                'current_password.required' => __('customer-error.current_password_required_so'),
                'password.not_contains_space' => __('customer-error.password_no_space_so'),
                'password.required' => __('customer-error.password_required_so'),
                'password.min' => __('customer-error.password_min_max_so'),
                'password.max' => __('customer-error.password_min_max_so'),
                'password.confirmed' => __('customer-error.password_confirmed_so'),
            ];
        }

        $validator = Validator::make($request->all(),
            [
                'current_password' => 'required',
                // 'password' => 'required|not_contains_space|min:6|max:16|confirmed',
                'password' => 'required|min:6|max:16|confirmed',
            ], $lang
        );

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $user = User::find($auth_user->id);
            if(Hash::check($request->current_password, $user->password)) {
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
                    $res = Response::send(false, [],$message, 400);
                }

            } else {
                $message = __('customer-error.current_password_en');
                if($request->lang == 2) {
                    $message = __('customer-error.current_password_so');
                }                 
                $res = Response::send(false, [], $message = ['current_password' => [$message]], 422);
                
            }
        }

        return $res;
    }

    /*************
    Check if the given mobile number is unique
    @params: country_code, mobile, lang
    **************/
    public function isMobileUnique(Request $request)
    {        
        $auth_user = auth('sanctum')->user();
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
            $user = User::withTrashed()
                    ->where('mobile', $request->mobile)
                    ->where('country_code_id', $request->country_code)
                    //->where('role_id', 3)
                    ->where('id', '<>', $auth_user->id)
                    ->first();
            if ($user) {                

                $message = __('customer-error.mobile_exists_en');
                if($request->lang  == 2) {
                    $message = __('customer-error.mobile_exists_so');
                }
                $res = Response::send(false, [], $message = ['mobile' => $message], 422);

            } else {
              
                $res = Response::send(true, [], '', 200);
            }
        }
        return $res;
    }

}
