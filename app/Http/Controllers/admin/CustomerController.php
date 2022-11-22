<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\admin\Customer;
use App\Models\admin\CustomerOrder;
use App\Models\Service\ResponseSender as Response;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

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

            $customers = Customer::select('customers.id as customer_id', 'users.name_en', 'users.name_so', 'country_codes.country_code', 'users.country_code_id','users.image', 'users.email', 'users.mobile', 'customers.created_at', DB::raw('MAX(customer_orders.created_at)as last_ordered_on'), DB::raw('count(customer_orders.customer_id) as total_orders'), 'customers.status')
                ->join('users', 'users.user_id', '=', 'customers.id')
                ->leftjoin('customer_orders', function ($join) {
                    $join->on('customer_orders.customer_id', '=', 'customers.id')
                    ->where( 'customer_orders.status','<>','0');
                })
                
               // ->leftjoin('customer_orders', 'customers.id', '=', 'customer_orders.customer_id')
                ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->where('users.role_id', 3)
                ->groupBy('customers.id','users.image' ,'users.name_en', 'users.name_so', 'users.mobile', 'users.country_code_id', 'country_codes.country_code', 'users.country_code_id', 'users.email', 'customers.created_at', 'customers.status')
                ->orderBy('customers.id','desc');


            // SEARCH
            if ($request->keyword) {
                $customers->where(function ($query) use ($request) {
                    $query->where('name_en', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('name_so', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('mobile', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('customers.created_at', 'LIKE', '%' . $request->keyword . '%');

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
            'name_en' => 'nullable|required_without:name_so|min:3|max:100',
            'name_so' => 'nullable|required_without:name_en|min:3|max:100',
            'email' => 'nullable|email|unique:users',
            'country_code' => 'required|numeric|exists:country_codes,id',
            'mobile' => 'required|integer|digits_between:6,14|unique:users',
            'password' => 'required|min:6|max:16',
            // 'password' => 'required|min:6|max:16|not_contains_space',
            'password_confirmation' => 'required|same:password|min:6|max:16',
            'image' => 'nullable|mimes:png,jpg,jpeg,pdf|max:1024|dimensions:max_width=600,max_height=600',


        ],
            ['name_en.required_without' => __('error.name_en_required_without'),
            'name_so.required_without' => __('error.name_so_required_without'),
            'name_en.min' => __('error.name_min'),
                'name_en.max' => __('error.name_max'),
                'name_so.min' => __('error.name_min'),
                'name_so.max' => __('error.name_max'),
                'country_code.required' => __('error.country_code_required'),
                'country_code.exists' => __('error.country_code_exists'),
                'mobile.required' => __('error.mobile_required'),
                'mobile.unique' => __('error.mobile_unique'),
                'email.unique' => __('error.email_unique'),

                'image.mimes' => __('error.image_mimes'),

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
            $role_id = auth('sanctum')->user()->role_id;
            $user_id = auth('sanctum')->user()->user_id;
            $customer->added_by = $role_id;
            $customer->added_user = $user_id;
            $result = $customer->save();

            if ($result) {
                $user = new User;
                $user->name_en = $fields['name_en'];
                $user->name_so = $fields['name_so'];
                $user->country_code_id = $fields['country_code'];
                $user->mobile = $fields['mobile'];
                $user->email = $fields['email'];
                $user->password = bcrypt($fields['password']);
                $user->role_id = 3;
                $user->user_id = $customer->id;
                $user->reg_status = 1;
                $image_uploaded_path = '';
                if ($request->file('image') != null) {
                    $uploadFolder = 'admin/customers';
                    $image = $request->file('image');
                    $image_uploaded_path = $image->store($uploadFolder, 'public');
                    $user->image = $image_uploaded_path;} else {
                    $user->image = '';
                }
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
                'name_en' => 'nullable|required_without:name_so|min:3|max:100',
                'name_so' => 'nullable|required_without:name_en|min:3|max:100',
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => ['required', 'numeric',
                    Rule::unique('users', 'mobile')->ignore($request->id, 'user_id')],
                'email' => ['nullable', 'email',
                    Rule::unique('users', 'email')->ignore($request->id, 'user_id')],

            ],

            ['name_en.required_without' => __('error.name_en_required_without'),
            'name_so.required_without' => __('error.name_so_required_without'),
           
                'name_en.min' => __('error.name_min'),
                'name_en.max' => __('error.name_max'),
                'name_so.min' => __('error.name_min'),
                'name_so.max' => __('error.name_max'),
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
            $role_id = auth('sanctum')->user()->role_id;
            $user_id = auth('sanctum')->user()->user_id;
            $customer->updated_by = $role_id;
            $customer->updated_user = $user_id;
            $result = $customer->save();
            if ($result) {
                $user = User::where('user_id', $fields['id'])->where('role_id', '3')->first();
                $user->name_en = $fields['name_en'];
                $user->name_so = $fields['name_so'];
                $user->country_code_id = $fields['country_code'];
                $user->mobile = $fields['mobile'];
                $user->email = $fields['email'];
                $image_uploaded_path = '';
                if ($request->file('image') != null) {
                    $uploadFolder = 'admin/customers';
                    $image = $request->file('image');
                    $image_uploaded_path = $image->store($uploadFolder, 'public');
                    $user->image = $image_uploaded_path;} else {
                    $user->image = '';
                }

                $user->save();
              
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


           
            $customer_details = Customer::select('customers.id as customer_id', 'customers.added_by', 'customers.added_user', 'customers.updated_by', 'customers.updated_user', 'customers.created_at', 'customers.updated_at', 'customers.deleted_at', 'customers.status', 'users.*','country_codes.country_code', )
                ->leftjoin('users', 'users.user_id', '=', 'customers.id')
                ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->where('users.role_id', 3)
                ->where('customers.id', $request->id)
                ->first();

            $address = DB::table('Customer_order_address')->select('Customer_order_address.*','customers.created_at as customer_created_on')
            ->join('customers', 'customers.id', '=', 'Customer_order_address.customer_id')
            ->where('Customer_order_address.customer_id', $request->id)
             ->orderBy('customer_order_address.updated_at', 'desc')->first();

            $count = CustomerOrder::select('customer_orders.customer_id','customers.id',)
                ->where('users.role_id', '3')
                ->join('users', 'users.user_id', '=', 'customer_orders.customer_id')
                ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->join('customers', 'customers.id', '=', 'customer_orders.customer_id')
                ->where('customer_orders.customer_id', $request->id)
                ->get();
            $number = count($count);

            $paid = DB::table('Customer_order_payments')
                ->where('users.role_id', '3')
                ->join('users', 'users.user_id', '=', 'customer_order_payments.customer_id')
                ->join('customers', 'customers.id', '=', 'customer_order_payments.customer_id')
                ->where('customer_order_payments.status', 2)
                ->where('customer_order_payments.customer_id', $request->id)
                ->sum('total_amount');

            $data = array(
                'customer_details' => $customer_details,
                'last_ordered_address' => $address,
                'total_orders' => $number,
                'paid' => $paid,

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
                'name_en' => 'nullable|required_without:name_so|min:3|max:100',
                'name_so' => 'nullable|required_without:name_en|min:3|max:100',

                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => ['required', 'numeric',
                    Rule::unique('users', 'mobile')->ignore($request->id, 'user_id')],
                'email' => ['nullable', 'email',
                    Rule::unique('users', 'email')->ignore($request->id, 'user_id')],


                'image' => 'nullable|mimes:png,jpg,jpeg,pdf|max:1024|dimensions:max_width=600,max_height=600',

            ],
            ['name_en.required_without' => __('error.name_en_required_without'),
            'name_so.required_without' => __('error.name_so_required_without'),
           
         'name_en.min' => __('error.name_min'),
                'name_en.max' => __('error.name_max'),
                'name_so.min' => __('error.name_min'),
                'name_so.max' => __('error.name_max'),
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
            $role_id = auth('sanctum')->user()->role_id;
            $user_id = auth('sanctum')->user()->user_id;
            $customer->updated_by = $role_id;
            $customer->updated_user = $user_id;
            $result = $customer->save();

            if ($result) {
                $user = User::where('user_id', $fields['id'])->where('role_id', '3')->first();
                $user->name_en = $fields['name_en'];
                $user->name_so = $fields['name_so'];
                $user->country_code_id = $fields['country_code'];
                $user->mobile = $fields['mobile'];
                $user->email = $fields['email'];
                $image_uploaded_path = '';
                if ($request->file('image') != null) {

                    $uploadFolder = 'admin/customers';
                    $image = $request->file('image');
                    $image_uploaded_path = $image->store($uploadFolder, 'public');
                    $user->image = $image_uploaded_path;} else {
                    $user->image = '';
                }

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

/*GET ORDERS */
    public function order_index(Request $request)
    {

        $fields = $request->input();
        $validator = Validator::make($request->all(),
            ['limit' => 'required',
                'keyword' => 'nullable',
                'status' => 'nullable|numeric|in:0,1,2,3,4,5,6',
                'customer_id' => 'required',

            ]);

        if ($validator->fails()) {$errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {

           
            $orders = CustomerOrder::select('customer_orders.id as customer_order_id','customer_orders.customer_id', 'customer_orders.driver_id', 'customer_orders.fuel_station_id', 'p1.name_en as customer_name_en', 'p1.name_so as customer_name_so', 'p2.name_en as driver_name_en', 'p2.name_so as driver_name_so', 'p1.mobile as customer_mobile','p2.mobile as driver_mobile' ,'p1.country_code_id',  'p3.name_en as fuel_station_name_en', 'p3.name_so as fuel_station_name_so', 'p3.mobile as fuel_station_mobile','customer_orders.status', 'customer_orders.total', 'customer_orders.created_at','customer_orders.added_by as customer_order_added_by','customer_orders.added_user as customer_order_added_user')
                ->join('users as p1', function ($join) {
                    $join->on('p1.user_id', '=', 'customer_orders.customer_id')
                        ->where('p1.role_id', 3);
                })
                ->leftjoin('users as p2', function ($join) {
                    $join->on('p2.user_id', '=', 'customer_orders.driver_id')
                        ->where('p2.role_id', 4);
                })
                ->join('users as p3', function ($join) {
                    $join->on('p3.user_id', '=', 'customer_orders.fuel_station_id')
                        ->where('p3.role_id', 5);
                })
    
                ->leftjoin('country_codes', 'country_codes.id', '=', 'p1.country_code_id')
                ->with([
                    'customers', 'drivers', 'fuel_stations',
                ])
                ->where( 'customer_orders.status','<>','0')
                ->where('customer_orders.customer_id', $fields['customer_id'])
                ->orderBy('customer_orders.id','desc');

            if ($request->keyword) {
                $orders->where(function ($query) use ($request) {
                    $query->where('customer_orders.total', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('p2.name_en', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('p2.name_so', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('p2.mobile', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('p3.mobile', 'LIKE', '%' . $request->keyword . '%')
                         ->orWhere('p3.name_en', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('p3.name_so', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('customer_orders.order_type', 'LIKE', '%' . $request->keyword . '%');
                });}

            if ($fields['status'] != '' && $fields['status'] != null) {
              
                if ($fields['status'] == 1) {

                    $orders->where('customer_orders.status', $fields['status']);
                }
                if ($fields['status'] == 2) {

                    $orders->where('customer_orders.status', $fields['status']);
                }

                if ($fields['status'] == 3) {

                    $orders->where('customer_orders.status', $fields['status']);
                }
                if ($fields['status'] == 4) {

                    $orders->where('customer_orders.status', $fields['status']);
                }if ($fields['status'] == 5) {

                    $orders->where('customer_orders.status', $fields['status']);
                }
                if ($fields['status'] == 6) {

                    $orders->where('customer_orders.status', $fields['status']);
                }
                
            }
            $orders = $orders->paginate($fields['limit']);

            $res = Response::send('true',
                $data = [
                    'Orders' => $orders,
                ],
                $message = 'Success',
                $code = 200);}
        return $res;
    }

    public function sendPlaceSMS(Request $request)
    {
        // $fields = $request->input();

        //  $mobile=$fields['mobile'];

        $res = file_get_contents("http://sms.moplet.com/api/sendhttp.php?authkey=2773AVudLLXJ62ea1040P43&mobiles=9400171938&message=
        Your%20order%20placed%20successfully.%20Thank%20You%20for%20choosing%20Medicino!&sender=MEDCIO&route=4&country=91&DLT_TE_ID=1407165945239381201");
        return $res;
    }

}
