<?php

namespace App\Http\Controllers\android\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\android\customer\Customer;
use App\Models\Service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Validator;

class ProfileController extends Controller
{
    /*************
     * Customer Profile
     * @params: null
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
                ], 
                $message = '', 
                $code    = 200
            ); 
        }
        else {
            $res = Response::send(
                'false', 
                $data    = [], 
                $message = 'Data not found', 
                $code    = 404
            );   
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
        $validator = Validator::make($request->all(),
            [
                
                'current_password' => 'required',
                // 'password' => 'required|not_contains_space|min:6|max:16|confirmed',
                'password' => 'required|min:6|max:16|confirmed',
            ],
            [
                'current_password.required' => __('customer-error.current_password_required'),
                'password.not_contains_space' => __('customer-error.password_no_space'),
                'password.required' => __('customer-error.password_required'),
                'password.min' => __('customer-error.password_min_max'),
                'password.max' => __('customer-error.password_min_max'),
                'password.confirmed' => __('customer-error.password_confirmed'),
            ]
        );

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $customer = Customer::find($auth_user->id);
            if(Hash::check($request->current_password, $user->password)) {
                $customer->password = bcrypt($request->password);

                if ($customer->save()) {
                    $res = Response::send(true, [], __('customer-success.change_password'), 200);
                } else {
                    $res = Response::send(false, [], __('customer-error.change_password'), 400);
                }
            } else {
                $res = Response::send(false, [], $message = ['current_password' => [__('customer-error.current_password')]], 422);
            }
        }

        return $res;
    }

}
