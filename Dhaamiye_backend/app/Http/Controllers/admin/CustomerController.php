<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\admin\Customer;
use App\Models\Service\ResponseSender as Response;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'status' => 'nullable|numeric|in:1,2', //1:Active, 2:Blocked
        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $customers = Customer::select('customers.id as customer_id', 'users.name', 'country_codes.country_code', 'users.country_code_id', 'users.email', 'users.mobile', 'customers.added_by', 'customers.created_at', 'customers.status')
                ->join('users', 'users.user_id', '=', 'customers.id')
                ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->where('users.role_id', 3)
                ->orderBy('customers.id', 'desc');
            // SEARCH
            if ($request->keyword) {
                $customers->where(function ($query) use ($request) {
                    $query->where('name', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('mobile', 'LIKE', '%' . $request->keyword . '%');

                });
            }

            // Enables sorting - Published and unpublished
            if ($request->status != '' && $request->status != null) {
                if ($request->status == Customer::BLOCKED) {
                    $customers->blocked();
                } elseif ($request->status == Customer::ACTIVE) {
                    $customers->active();
                };
            }
            // Paginate records
            $customers = $customers->paginate($request->limit);
            $data = array(
                'customers' => $customers,
            );

            $res = Response::send('true',
                $data,
                $message = 'Success',
                $code = 200);
        }
        return $res;
    }
//ADD CUSTOMER

    public function add(Request $request)
    {$fields = $request->input();
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:100',
            'email' => 'nullable|email|unique:users',
            'country_code' => 'required|numeric|exists:country_codes,id',
            'mobile' => 'required|integer|digits_between:6,14|unique:users',
            'password' => 'required|min:6|max:16',
            // 'password' => 'required|min:6|max:16|not_contains_space',
            'password_confirmation' => 'required|same:password|min:6|max:16',
        ], ['name.required' => __('error.name_required'),
            'name.min' => __('error.name_min'),
            'name.max' => __('error.name_max'),
            'country_code.required' => __('error.country_code_required'),
            'country_code.exists' => __('error.country_code_exists'),
            'mobile.required' => __('error.mobile_required'),
            'mobile.unique' => __('error.mobile_unique'),
            'email.unique' => __('error.email_unique'),
            // 'password.not_contains_space' => __('error.password_no_space'),
            'password.required' => __('error.password_required'),
            'password.min' => __('error.password_min'),
            'password.max' => __('error.password_max'),
            'password_confirmation.required' => 'Please enter the confirmation password.',
            'password_confirmation.same' => 'Entered password and confirmation password should be same.',

        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $customer = new Customer;
            $customer->added_by = $fields['added_by'];
            $customer->added_user = $fields['added_user'];
            $customer->updated_by = $fields['updated_by'];
            $customer->updated_user = $fields['updated_user'];
            $result = $customer->save();

            if ($result) {
                $user = new User;
                $user->name = $fields['name'];
                $user->country_code_id = $fields['country_code'];
                $user->mobile = $fields['mobile'];
                $user->email = $fields['email'];
                $user->password = bcrypt($fields['password']);
                $user->role_id = 3;
                $user->user_id = $customer->id;
                $user->reg_status = 1;
                $user->save();
                $res = Response::send('true',
                    [],
                    $message = __('success.create_customer'),
                    $code = 200);
            } else {
                $res = Response::send('false',
                    [],
                    $message = __('error.create_customer'),
                    $code = 400);
            }

        }

        return $res;
    }
//UPDATE CUSTOMER
    public function update(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(),
            ['id' => 'required|numeric|exists:customers,id',
                'name' => 'required|min:3|max:100',
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => ['required', 'numeric',
                    Rule::unique('users', 'mobile')->ignore($request->id, 'user_id')],
                'email' => ['nullable', 'email',
                    Rule::unique('users', 'email')->ignore($request->id, 'user_id')],
            ],
            ['name.required' => __('error.name_required'),
                'name.min' => __('error.name_min'),
                'name.max' => __('error.name_max'),
                'country_code.required' => __('error.country_code_required'),
                'country_code.exists' => __('error.country_code_exists'),
                'mobile.required' => __('error.mobile_required'),
                'mobile.unique' => __('error.mobile_unique'),
                'email.unique' => __('error.email_unique'),

            ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $customer = Customer::find($fields['id']);
            $customer->added_by = 'Admin';

            $result = $customer->save();
            if ($result) {
                $user = User::where('user_id', $fields['id'])->where('role_id', '3')->first();
                $user->name = $fields['name'];
                $user->country_code_id = $fields['country_code'];
                $user->mobile = $fields['mobile'];
                $user->email = $fields['email'];
                $user->save();
                $res = Response::send('true',
                    [],
                    $message = __('success.update_customer'),
                    $code = 200);
            } else {
                $res = Response::send('false',
                    [],
                    $message = __('error.update_customer'),
                    $code = 400);
            }
        }

        return $res;
    }

    //CHANGE STATUS
    public function status(Request $request)
    {$fields = $request->input();

        $validator = Validator::make($request->all(),
            [
                'id' => 'required|numeric|exists:customers,id',
                'status' => 'required|numeric|in:1,2',
            ],
            [
                'status.in' => __('error.status_in'),
                'id.exists' => __('error.id_exists'),
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $customer = Customer::find($fields['id']);
            $customer->status = $fields['status'];
            $result = $customer->save();
            if ($result) {
                $user = User::where('user_id', $fields['id'])->where('role_id', '3')->first();
                $user->status = $fields['status'];
                $user->save();

                if ($request->status == 1) {
                    $error_message = __('success.publish_customer');
                } else {
                    $error_message = __('success.unpublish_customer');
                }
                $res = Response::send('true',
                    [],
                    $message = $error_message,
                    $code = 200);
            } else {
                $res = Response::send('false',
                    [],
                    $message = $error_message,
                    $code = 400);
            }
        }
        return $res;
    }

//CHANGE PASSWORD
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|numeric|exists:customers,id',
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
            $user = User::where(['user_id' => $request->id, 'role_id' => 3])->first();
            $user->password = bcrypt($request->password);

            if ($user->save()) {
                $res = Response::send(true, [], __('success.change_password'), 200);
            } else {
                $res = Response::send(false, [], __('error.change_password'), 400);
            }
        }
        return $res;
    }
//CUSTOMER DETAILS
    public function profile(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|exists:customers,id',
            ],
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);
        } else {
            $customer = Customer::select('customers.id as customer_id', 'customers.name', 'country_codes.country_code', 'users.country_code_id', 'users.email', 'users.mobile', 'customers.added_by', 'customers.created_at', 'customers.status')

                ->join('users', 'users.user_id', '=', 'customers.id')
                ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->where('users.role_id', 3)
                ->where('customers.id', $request->id)
                ->first();
            $data = array(
                'customer' => $customer,
            );
            $res = Response::send(true, $data, 'Customer found', 200);
        }
        return $res;
    }
//UPDATE PROFILE
    public function updateProfile(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(),
            ['id' => 'required|numeric|exists:customers,id',
                'name' => 'required|min:3|max:100',
                'image' => 'nullable|mimes:png,jpg,jpeg|max:1024|dimensions:max_width=600,max_height=600',
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => ['required', 'numeric', 'digits:10',
                    Rule::unique('users', 'mobile')->ignore($request->id, 'user_id')],
                'email' => ['nullable', 'email',
                    Rule::unique('users', 'email')->ignore($request->id, 'user_id')],
            ],
            ['name.required' => __('error.name_required'),
                'name.min' => __('error.name_min'),
                'name.max' => __('error.name_max'),
                'image.mimes' => __('error.image_mimes'),
                'country_code.required' => __('error.country_code_required'),
                'country_code.exists' => __('error.country_code_exists'),
                'mobile.required' => __('error.mobile_required'),
                'mobile.unique' => __('error.mobile_unique'),
                'email.unique' => __('error.email_unique'),

            ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $customer = Customer::find($fields['id']);
            $customer->added_by = 'Admin';

            $result = $customer->save();
            $path = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('patients', 'public');
            }
            $patient->image = $path;
            $result = $patient->save();

            if ($result) {
                $user = User::where('user_id', $fields['id'])->where('role_id', '3')->first();
                $user->country_code_id = $fields['country_code'];
                $user->mobile = $fields['mobile'];
                $user->email = $fields['email'];
                $user->save();
                $res = Response::send('true',
                    [],
                    $message = __('success.update_customer'),
                    $code = 200);
            } else {
                $res = Response::send('false',
                    [],
                    $message = __('error.update_customer'),
                    $code = 400);
            }
        }

        return $res;
    }
     public function sendPlaceSMS(Request $request) {
       // $fields = $request->input();

      //  $mobile=$fields['mobile'];
                        
    $res = file_get_contents("http://sms.moplet.com/api/sendhttp.php?authkey=2773AVudLLXJ62ea1040P43&mobiles=9400171938&message=
        Your%20order%20placed%20successfully.%20Thank%20You%20for%20choosing%20Medicino!&sender=MEDCIO&route=4&country=91&DLT_TE_ID=1407165945239381201");
         return $res;
     }
        
        


}
