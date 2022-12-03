<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\admin\CustomerOrder;
use App\Models\admin\Driver;
use App\Models\admin\FuelStation;
use App\Models\admin\FuelStationPaymentLog;
use App\Models\admin\FuelStationPriceLog;
use App\Models\admin\FuelStationStock;
use App\Models\admin\FuelStationStockLog;
use App\Models\admin\Truck;
use App\Models\service\ResponseSender as Response;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Validator;

class FuelStationController extends Controller
{ /*GET FUELSTATIONS*/
    
    public function index(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'status' => 'nullable|numeric|in:1,2', // 2:Blocked, 1:Active
        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $fuel_stations = FuelStation::select('fuel_stations.id as fuel_station_id', DB::raw("(SELECT COUNT(id) AS truck_count FROM trucks WHERE trucks.fuel_station_id=fuel_stations.id) as truckcount"), DB::raw("(SELECT COUNT(id) AS order_count FROM customer_orders WHERE customer_orders.fuel_station_id=fuel_stations.id) as ordercount"), DB::raw("(select balance from fuel_station_payment_logs WHERE fuel_station_payment_logs.fuel_station_id=fuel_stations.id order by id desc limit 1) as balance"), 'users.name_en', 'users.name_so', 'users.image', 'fuel_stations.added_by', 'place', 'latitude', 'longitude', 'address', 'trucks.truck_no', 'fuel_stations.status', 'country_code_id', 'country_code', 'mobile', 'email', 'role_id', 'fuel_stations.created_at')
                ->leftjoin('trucks', 'fuel_stations.id', '=', 'trucks.fuel_station_id')
                ->leftjoin('customer_orders', function ($join) {
                    $join->on('customer_orders.fuel_station_id', '=', 'fuel_stations.id')
                        ->where('customer_orders.status', '<>', '0');
                })
            //->leftjoin('customer_orders', 'fuel_stations.id', '=', 'customer_orders.fuel_station_id')
                ->leftjoin('fuel_station_payment_logs', 'fuel_stations.id', '=', 'fuel_station_payment_logs.fuel_station_id')
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->where('users.role_id', '5')
                ->where('users.reg_status', '1')
                ->groupBy('fuel_stations.id', 'users.name_en', 'trucks.truck_no', 'users.name_so', 'users.image', 'added_by', 'place', 'latitude', 'longitude', 'address', 'fuel_stations.status', 'country_code_id', 'country_code', 'mobile', 'email', 'role_id', 'fuel_stations.created_at')
                ->orderBy('fuel_stations.id', 'desc');
            // SEARCH
            if ($request->keyword) {
                $fuel_stations->where(function ($query) use ($request) {
                    $query->where('users.name_en', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('users.name_so', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('trucks.truck_no', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('place', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('mobile', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->keyword . '%');

                });
            }

            // Enables sorting - Published and unpublished
            if ($request->status != '' && $request->status != null) {
                if ($request->status == FuelStation::BLOCKED) {
                    $fuel_stations->blocked();
                } elseif ($request->status == FuelStation::ACTIVE) {
                    $fuel_stations->active();
                };
            }
            // Paginate records
            $fuel_stations = $fuel_stations->paginate($request->limit);

            $res = Response::send('true',
                $data = ['fuel_stations' => $fuel_stations,
                ],
                $message = 'Success',
                $code = 200);
        }
        return $res;}
//FUEL STATION ADD
    public function add(Request $request)
    {$fields = $request->input();
        $validator = Validator::make($request->all(),
            [
                'name_en' => 'nullable|required_without:name_so|min:3|max:100',
                'name_so' => 'nullable|required_without:name_en|min:3|max:100',

                'image' => 'nullable|mimes:png,jpg,jpeg,pdf|max:1024|dimensions:max_width=600,  max_height=600',
                'email' => 'required|email|unique:users',
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => 'required|integer|digits_between:6,14|unique:users',
                'place' => 'required|min:3|max:100',
                'latitude' => 'required|min:3|max:100',
                'longitude' => 'required|min:3|max:100',
                'address' => 'required|min:3|max:100',
                'password' => 'required|min:6|max:16',
                // 'password' => 'required|min:6|max:16|not_contains_space',
                'password_confirmation' => 'required|same:password|min:6|max:16',
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

                'name_en.min' => __('error.name_min'),
                'name_en.max' => __('error.name_max'),
                'name_so.min' => __('error.name_min'),
                'name_so.max' => __('error.name_max'),

                'name.required' => __('error.name_required'),
                'name.min' => __('error.name_min'),
                'name.max' => __('error.name_max'),
                'image.mimes' => __('error.image_mimes'),
                'address.required' => __('error.address_required'),
                'address.min' => __('error.address_min'),
                'address.max' => __('error.address_max'),
                //'address.contains_alphabets' => __('error.address_contains_alphabets'),
                //'address.starts_with_alphabet' => __('error.address_starts_with_alphabet'),
                'country_code.required' => __('error.country_code_required'),
                'country_code.exists' => __('error.country_code_exists'),
                'mobile.required' => __('error.mobile_required'),
                'mobile.unique' => __('error.mobile_unique'),
                'email.unique' => __('error.email_unique'),
                'password.required' => __('error.password_required'),
                'password.min' => __('error.password_min'),
                'password.max' => __('error.password_max'),
                'password_confirmation.required' => 'Please enter the confirmation password.',
                'password_confirmation.same' => 'Entered password and confirmation password should be same.',
                'place.required' => __('error.place_required'),
                'place.min' => __('error.place_min'),
                'place.max' => __('error.place_max'),
                'latitude.required' => __('error.latitude_required'),
                'latitude.min' => __('error.latitude_min'),
                'latitude.max' => __('error.latitude_max'),
                'longitude.required' => __('error.longitude_required'),
                'logitude.min' => __('error.logitude_min'),
                'logitude.max' => __('error.logitude_max'),
                'email.required' => 'Please enter email.',
                'account_number.required' => 'Please enter the account number.',
                'bank_name.required' => 'Please enter the bank name.',
                'branch.required' => 'Please enter the branch name.',
                'account_type.required' => 'Please select the type,1:Savings account,2:Current account',
                'account_no.required' => 'Please enter the account number.',
                'account_holder_name.required' => 'Please enter the account holder name.',
                'ifsc_code.required' => 'Please enter the IFSC code.',
                'upi_id.required' => 'Please enter the upi id.',

            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $fuel_station = new FuelStation;
            $fuel_station->place = $fields['place'];
            $role_id = auth('sanctum')->user()->role_id;
            $user_id = auth('sanctum')->user()->user_id;
            $fuel_station->added_by = $role_id;
            $fuel_station->added_user = $user_id;
            $fuel_station->address = $fields['address'];
            $fuel_station->latitude = $fields['latitude'];
            $fuel_station->longitude = $fields['longitude'];
            $result = $fuel_station->save();
            if ($result) {
                $user = new User;
                $user->name_en = $fields['name_en'];
                $user->name_so = $fields['name_so'];
                $user->country_code_id = $fields['country_code'];
                $user->mobile = $fields['mobile'];
                $user->email = $fields['email'];
                $user->password = bcrypt($fields['password']);
                $user->role_id = 5;
                $user->user_id = $fuel_station->id;
                $user->reg_status = 1;
                $image_uploaded_path = '';
                if ($request->file('image') != null) {
                    $uploadFolder = 'admin/fuelStations';
                    $image = $request->file('image');
                    $image_uploaded_path = $image->store($uploadFolder, 'public');
                    $user->image = $image_uploaded_path;

                } else {
                    $user->image = '';
                }
                $user->save();
                DB::table('fuel_station_bank_details')->insert(
                    array('fuel_station_id' => $fuel_station->id,
                        'bank_name' => $fields['bank_name'],
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
                    $message = 'Fuel station created successfully.',
                    $code = 200);
            } else {
                $res = Response::send('false',
                    [],
                    $message = 'Failed to create Fuel station.',
                    $code = 400);
            }
        }

        return $res;
    }

    /*
    Update fuel_station
    @params: fuel_station, id
     */
    public function update(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(),

            ['id' => 'required|numeric|exists:fuel_stations,id',
                'name_en' => 'nullable|required_without:name_so|min:3|max:100',
                'name_so' => 'nullable|required_without:name_en|min:3|max:100',
                'image' => 'nullable|mimes:png,jpg,jpeg|max:1024|dimensions:max_width=600,max_height=600',
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => ['required', 'numeric',
                    Rule::unique('users', 'mobile')->ignore($request->id, 'user_id')],
                'email' => ['required', 'email',
                    Rule::unique('users', 'email')->ignore($request->id, 'user_id')],
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
            $fuel_station = FuelStation::find($fields['id']);
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
                $user = User::where('user_id', $fields['id'])->where('role_id', '5')->first();
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

                } else {
                    $user->image = '';
                }

                $user->save();
                DB::table('fuel_station_bank_details')->where('fuel_station_id', $fields['id'])->update(
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
    /*GET FUEL STATION DETAILS*/
    public function details(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:fuel_stations,id',
        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {


            $fuel_station_detail = FuelStation::select('fuel_stations.id', 'users.name_en', 'users.name_so', 'users.image', 'added_by', 'added_user', 'updated_by', 'updated_user', 'place', 'latitude', 'longitude', 'address', 'fuel_stations.status', 'country_code_id', 'country_code', 'mobile', 'email', 'role_id', 'fuel_stations.created_at')
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->where('fuel_stations.id', $fields['id'])
                ->where('users.role_id', '5')
                ->first();
            $bank_details = DB::table('fuel_station_bank_details')->select('bank_name', 'branch', 'account_no', 'account_holder_name', 'ifsc_code', 'upi_id', 'account_type', 'created_at')
                ->where('fuel_station_id', $fields['id'])
                ->first();
            $count = DB::table('customer_orders')->select('fuel_stations.id')
                ->where('users.role_id', '5')
                ->join('users', 'users.user_id', '=', 'customer_orders.fuel_station_id')
                ->join('fuel_stations', 'fuel_stations.id', '=', 'customer_orders.fuel_station_id')
                ->where('customer_orders.fuel_station_id', $fields['id'])
                ->get();
            $number = count($count);
            $balance = DB::table('fuel_station_payment_logs')->select('balance')
                ->where('fuel_station_payment_logs.fuel_station_id', $fields['id'])
                ->orderBy('id', 'desc')
                ->first();

            $res = Response::send('true',

                $data = ['fuel_station_detail' => $fuel_station_detail,
                    'bank_details' => $bank_details,
                    'no_of_orders' => $number,
                    'Earning balance' => $balance,
                ],
                $message = 'Success',
                $code = 200);
        }
        return $res;
    }

//CHANGE PASSWORD
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|numeric|exists:fuel_stations,id',
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
            $user = User::where(['user_id' => $request->id, 'role_id' => 5])->first();
            $user->password = bcrypt($request->password);

            if ($user->save()) {
                $res = Response::send(true, [], __('success.change_password'), 200);
            } else {
                $res = Response::send(false, [], __('error.change_password'), 400);
            }
        }
        return $res;
    }

    //CHANGE STATUS
    public function status(Request $request)
    {$fields = $request->input();

        $validator = Validator::make($request->all(),
            [
                'id' => 'required|numeric|exists:fuel_stations,id',
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
            $fuel_station = FuelStation::find($fields['id']);
            $fuel_station->status = $fields['status'];
            $result = $fuel_station->save();
            if ($result) {
                $user = User::where('user_id', $fields['id'])->where('role_id', '5')->first();
                $user->status = $fields['status'];
                $user->save();

                if ($request->status == 1) {
                    $error_message = __('success.publish_fuel_station');
                } else {
                    $error_message = __('success.unpublish_fuel_station');
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
    /*GET ORDERS */
    public function order_index(Request $request)
    {

        $fields = $request->input();
        $validator = Validator::make($request->all(),
            ['limit' => 'required',
                'keyword' => 'nullable',
                'status' => 'nullable|numeric|in:0,1,2,3,4,5,6',
                'fuel_station_id' => 'required|numeric|exists:fuel_stations,id',

            ]);

        if ($validator->fails()) {$errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {

            $orders = CustomerOrder::select('customer_orders.id as customer_order_id', 'customer_orders.customer_id', 'customer_orders.driver_id', 'customer_orders.fuel_station_id', 'p1.name_en as customer_name_en', 'p1.name_so as customer_name_so', 'p2.name_en as driver_name_en', 'p2.name_so as driver_name_so', 'p1.mobile as customer_mobile', 'p2.mobile as driver_mobile', 'p3.mobile as fuel_station_mobile', 'p1.country_code_id', 'customer_orders.status as customer_order_status', 'customer_orders.total', 'p3.name_en as fuel_station_name_en', 'p3.name_so as fuel_station_name_so', 'p3.mobile as fuel_station_mobile', 'customer_orders.created_at', 'customer_orders.added_by as customer_order_added_by', 'customer_orders.added_user as customer_order_added_user')
                ->join('users as p1', function ($join) {
                    $join->on('p1.user_id', '=', 'customer_orders.customer_id')
                        ->where('p1.role_id', 3);
                })
                ->join('users as p2', function ($join) {
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
                ->where('customer_orders.fuel_station_id', $fields['fuel_station_id'])
                ->orderBy('customer_orders.id', 'desc');

            if ($request->keyword) {
                $orders->where(function ($query) use ($request) {
                    $query->where('customer_orders.order_type', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('p2.name_en', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('p2.name_so', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('p2.mobile', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('p1.name_en', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('p1.name_so', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('p1.mobile', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('customer_orders.order_id', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('customer_orders.total', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('customer_orders.created_at', 'LIKE', '%' . $request->keyword . '%');

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
    /*GET TRUCKS*/
    public function trucks(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'status' => 'nullable|numeric|in:1,2', // 2:Blocked, 1:Active
            'fuel_station_id' => 'required|numeric|exists:fuel_stations,id',
        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $fuel_station_detail = FuelStation::select('fuel_stations.id', 'users.name_en', 'users.name_so', 'users.image', 'added_by', 'added_user', 'updated_by', 'updated_user', 'place', 'latitude', 'longitude', 'address', 'fuel_stations.status', 'country_code_id', 'country_code', 'mobile', 'email', 'role_id', 'fuel_stations.created_at')
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->where('fuel_stations.id', $fields['fuel_station_id'])
                ->where('users.role_id', '5')
                ->first();

            $trucks = Truck::select('trucks.id as truck_id', 'trucks.truck_no', 'trucks.fuel_station_id', 'trucks.manufacturer', 'trucks.manufactured_year', 'trucks.model', 'trucks.color', 'trucks.status', 'trucks.created_at', 'users.name_en as fuel_station_name_en', 'users.name_so as fuel_station_name_so', 'users.mobile as fuel_station_mobile')
            // ->leftjoin('fuel_stations', 'fuel_stations.id', '=', 'trucks.fuel_station_id')
                ->join('users', 'users.user_id', '=', 'trucks.fuel_station_id')
                ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->with([
                    'driver', 'fuel_station',
                ])
                ->where('users.role_id', '5')
                ->where('users.reg_status', '1')
                ->where('trucks.fuel_station_id', $fields['fuel_station_id'])
                ->where('trucks.reg_status', '1')
                ->orderBy('trucks.id', 'desc');
            // SEARCH
            if ($request->keyword) {
                $trucks->where(function ($query) use ($request) {
                    $query->where('truck_no', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('model', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('manufacturer', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('manufactured_year', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('color', 'LIKE', '%' . $request->keyword . '%');

                });
            }

            // Enables sorting - Published and unpublished
            if ($request->status != '' && $request->status != null) {
                if ($request->status == Truck::BLOCKED) {
                    $trucks->blocked();
                } elseif ($request->status == Truck::ACTIVE) {
                    $trucks->active();
                };
            }
            // Paginate records
            $trucks = $trucks->paginate($request->limit);

            $res = Response::send('true',
                $data = ['fuel_station' => $fuel_station_detail,
                    'trucks' => $trucks,
                ],
                $message = 'Success',
                $code = 200);
        }
        return $res;}

/*GET DRIVERS*/
    public function drivers(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'status' => 'nullable|numeric|in:1,2', // 2:Blocked, 1:Active
            'fuel_station_id' => 'required|numeric|exists:fuel_stations,id',
        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $fuel_station_detail = FuelStation::select('fuel_stations.id', 'users.name_en', 'users.name_so', 'users.image', 'added_by', 'added_user', 'updated_by', 'updated_user', 'place', 'latitude', 'longitude', 'address', 'fuel_stations.status', 'country_code_id', 'country_code', 'mobile', 'email', 'role_id', 'fuel_stations.created_at')
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->where('fuel_stations.id', $fields['fuel_station_id'])
                ->where('users.role_id', '5')
                ->first();

            $drivers = Driver::select('drivers.id as driver_id', 'trucks.color as truck_color', 'trucks.id as truck_id', 'users.name_en as driver_name_en', 'users.name_so as driver_name_so', 'users.mobile', 'users.country_code_id', 'users.email', 'country_codes.country_code', 'trucks.truck_no', DB::raw("(SELECT COUNT(id) AS order_count FROM customer_orders WHERE customer_orders.driver_id=drivers.id) as ordercount"), 'drivers.online', 'drivers.status', 'drivers.created_at')
                ->leftjoin('trucks', 'drivers.truck_id', '=', 'trucks.id')
                ->leftjoin('customer_orders', function ($join) {
                    $join->on('customer_orders.driver_id', '=', 'drivers.id')
                    ->where( 'customer_orders.status','<>','0');
                })
               // ->leftjoin('customer_orders', 'customer_orders.driver_id', '=', 'drivers.id')
                ->join('users', 'users.user_id', '=', 'drivers.id')
                ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->with([
                    'truck',
                ])
                ->where('users.role_id', '4')
                ->where('users.reg_status', '1')
                ->where('drivers.fuel_station_id', $fields['fuel_station_id'])
                ->orderBy('drivers.id', 'desc')
                ->groupBy('drivers.id', 'trucks.color', 'drivers.online', 'drivers.status', 'trucks.id', 'users.name_en', 'users.name_so', 'users.mobile', 'users.country_code_id', 'country_codes.country_code', 'users.mobile', 'users.email', 'trucks.truck_no', 'drivers.created_at');
            // SEARCH
            if ($request->keyword) {
                $drivers->where(function ($query) use ($request) {
                    $query->where('trucks.truck_no', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('users.name_en', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('users_name_so', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('users.mobile', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('users.email', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('trucks.color', 'LIKE', '%' . $request->keyword . '%');

                });
            }

            // Enables sorting - Published and unpublished
            if ($request->status != '' && $request->status != null) {
                if ($request->status == Driver::BLOCKED) {
                    $drivers->blocked();
                } elseif ($request->status == Driver::ACTIVE) {
                    $drivers->active();
                };
            }
            // Paginate records
            $drivers = $drivers->paginate($request->limit);

            $res = Response::send('true',
                $data = ['fuel_station' => $fuel_station_detail,
                    'drivers' => $drivers,
                ],
                $message = 'Success',
                $code = 200);
        }
        return $res;}
/*GET FUELSTOCKS*/
    public function FuelTypes(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'status' => 'nullable|numeric|in:1,2', //1:Active, 2:Blocked
            'fuel_station_id' => 'required|numeric|exists:fuel_stations,id',

        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $fuels = FuelStationStock::select('fuel_station_stocks.*', 'fuel_types.fuel_en','fuel_types.fuel_so', )

                ->join('fuel_types', 'fuel_types.id', '=', 'fuel_station_stocks.fuel_type_id')
                ->where('fuel_station_stocks.fuel_station_id', $fields['fuel_station_id'])
                ->with([
                    'fuel_station', 'fuel_type',
                ])

                ->orderBy('id', 'desc');

            // SEARCH BY KEYWORD
            if ($request->keyword) {
                $fuels->where(function ($query) use ($request) {
                    $query->where('fuel_en', 'LIKE', '%' . $request->keyword . '%')
                         ->orWhere('fuel_so', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('fuel_station_stocks.stock', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('fuel_station_stocks.price', 'LIKE', '%' . $request->keyword . '%')

                    ;});
            }

            if ($fields['status'] != '' && $fields['status'] != null) {
                $fuels->where('fuel_station_stocks.status', $fields['status']);
            }

            $fuels = $fuels->paginate($fields['limit']);

            $data = array(
                'fuels' => $fuels,
            );

            $res = Response::send('true',
                $data,
                $message = 'Success',
                $code = 200);
        }
        return $res;
    }

/*CREATE FUELS*/
    public function addFuel(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(),
            [
                'fuel_station_id' => 'required|numeric|exists:fuel_stations,id',
                'fuel_type_id' => 'required|numeric|exists:fuel_types,id',
                'price' => 'required|numeric',

            ],
            [
                'fuel_type_id.required' => 'Please select the fuel type.',
                'price.required' => 'Please enter the price.',
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);

        } else { $exist = FuelStationStock::where('fuel_type_id', $fields['fuel_type_id'])->where('fuel_station_id', $fields['fuel_station_id'])->first();
            if ($exist === null) {

                $stock = new FuelStationStock;

                $stock->fuel_station_id = $fields['fuel_station_id'];
                $stock->fuel_type_id = $fields['fuel_type_id'];
                $stock->price = $fields['price'];
               
                
                $role_id = auth('sanctum')->user()->role_id;
                $user_id = auth('sanctum')->user()->user_id;
                $stock->added_by = $role_id;
                $stock->added_user = $user_id;
                $result = $stock->save();
                $res = Response::send('true',
                    [],
                    $message = 'Fuel added successfully.',
                    $code = 200);
            } else {
                $res = Response::send('false',
                    [],
                    $message = 'Failed to add fuel.',
                    $code = 400);
            }
        }

        return $res;
    }
    //UPDATE FUELPRICE
    public function updatePrice(Request $request)
    {$fields = $request->input();

        $validator = Validator::make($request->all(),
            [
                'id' => 'required|numeric|exists:fuel_station_stocks,id',
                'price' => 'required|numeric',
            ],
            [
                'price.required' => 'Please enter the price.',
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $fuel_station = FuelStationStock::find($fields['id']);
            $fuel_station->price = $fields['price'];
            $result = $fuel_station->save();
            $logs = DB::table('fuel_station_stocks')->select('fuel_station_stocks.id', 'fuel_station_stocks.fuel_station_id', 'fuel_station_stocks.fuel_type_id')->where('id', $fields['id'])
                ->where('id', $fields['id'])->first();
            $fuel_station_id = $logs->fuel_station_id;
            $fuel_type_id = $logs->fuel_type_id;

            if ($result) {

                $price = new FuelStationPriceLog;
                $price->fuel_station_id = $fuel_station_id;
                $price->fuel_type_id = $fuel_type_id;
                $price->price = $fields['price'];
                $role_id = auth('sanctum')->user()->role_id;
                $user_id = auth('sanctum')->user()->user_id;
                $price->added_by = $role_id;
                $price->added_user = $user_id;
                $price->save();

                $res = Response::send(true, [], __('success.update_price'), 200);
            } else {
                $res = Response::send(false, [], __('error.update_price'), 400);
            }
        }
        return $res;
    }

    //UPDATE FUELSTOCK
    public function updateStock(Request $request)
    {$fields = $request->input();

        $validator = Validator::make($request->all(),
            [
                'id' => 'required|numeric|exists:fuel_station_stocks,id',
                'stock' => 'required|numeric',
            ],
            [
                'stock.required' => 'Please enter the stock(in litres).',
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $fuel_station = FuelStationStock::find($fields['id']);
            $fuel_station->stock = $fields['stock'];
            $result = $fuel_station->save();
            $logs = DB::table('fuel_station_stocks')->select('fuel_station_stocks.id', 'fuel_station_stocks.fuel_station_id', 'fuel_station_stocks.fuel_type_id')
                ->where('id', $fields['id'])->first();
            $fuel_station_id = $logs->fuel_station_id;
            $fuel_type_id = $logs->fuel_type_id;

            if ($result) {

                $stock = new FuelStationStockLog;
                $stock->fuel_station_id = $fuel_station_id;
                $stock->fuel_type_id = $fuel_type_id;
                $stock->stock = $fields['stock'];
                $role_id = auth('sanctum')->user()->role_id;
                $user_id = auth('sanctum')->user()->user_id;
                $stock->added_by = $role_id;
                $stock->added_user = $user_id;
                $stock->save();

                $res = Response::send(true, [], __('success.update_stock'), 200);
            } else {
                $res = Response::send(false, [], __('error.update_stock'), 400);

            }
        }
        return $res;
    }

/*GET FUELPRICELOGS*/
    public function FuelPriceLogs(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'fuel_station_stock_id' => 'required|numeric|exists:fuel_station_stocks,id',

        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {

            $logs = DB::table('fuel_station_stocks')->select('fuel_station_stocks.id', 'fuel_station_stocks.fuel_station_id', 'fuel_station_stocks.fuel_type_id')
                ->where('id', $fields['fuel_station_stock_id'])->first();
            $fuel_station_id = $logs->fuel_station_id;
            $fuel_type_id = $logs->fuel_type_id;

            $fuel_price = FuelStationPriceLog::select('fuel_station_price_logs.*', 'fuel_types.fuel_en','fuel_types.fuel_so')->join('fuel_types', 'fuel_types.id', '=', 'fuel_station_price_logs.fuel_type_id')
                ->with([
                    'fuel_station', 'fuel_type', 'user',
                ])

                ->where('fuel_station_price_logs.fuel_station_id', $fuel_station_id)
                ->where('fuel_station_price_logs.fuel_type_id', $fuel_type_id)
                ->orderBy('fuel_station_price_logs.id', 'desc');

            // SEARCH BY KEYWORD
            if ($request->keyword) {
                $fuel_price->where(function ($query) use ($request) {
                    $query->where('fuel_station_price_logs.price', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('fuel_station_price_logs.created_at', 'LIKE', '%' . $request->keyword . '%');});
            }

            $fuel_price = $fuel_price->paginate($fields['limit']);

            $data = array(
                'fuel_price_logs' => $fuel_price,
            );

            $res = Response::send('true',
                $data,
                $message = 'Success',
                $code = 200);
        }
        return $res;
    }

/*GET FUELSTOCKLOGS*/
    public function FuelstockLogs(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'fuel_station_stock_id' => 'required|numeric|exists:fuel_station_stocks,id',

        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {

            $logs = DB::table('fuel_station_stocks')->select('fuel_station_stocks.id', 'fuel_station_stocks.fuel_station_id', 'fuel_station_stocks.fuel_type_id')
                ->where('id', $fields['fuel_station_stock_id'])->first();
            $fuel_station_id = $logs->fuel_station_id;
            $fuel_type_id = $logs->fuel_type_id;

            $fuel_stock = FuelStationStockLog::select('fuel_station_stock_logs.*', 'fuel_types.fuel_en','fuel_types.fuel_so',)->join('fuel_types', 'fuel_types.id', '=', 'fuel_station_stock_logs.fuel_type_id')
                ->with([
                    'fuel_station', 'fuel_type', 'user',
                ])

                ->where('fuel_station_stock_logs.fuel_station_id', $fuel_station_id)
                ->where('fuel_station_stock_logs.fuel_type_id', $fuel_type_id)
                ->orderBy('fuel_station_stock_logs.id', 'desc');

            // SEARCH BY KEYWORD
            if ($request->keyword) {
                $fuel_stock->where(function ($query) use ($request) {
                    $query->where('fuel_station_stock_logs.stock', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('fuel_station_stock_logs.created_at', 'LIKE', '%' . $request->keyword . '%');});
            }

            $fuel_stock = $fuel_stock->paginate($fields['limit']);

            $data = array(
                'fuel_stock_logs' => $fuel_stock,
            );

            $res = Response::send('true',
                $data,
                $message = 'Success',
                $code = 200);
        }
        return $res;
    }

/*GET FUELS*/
    public function paymentLogs(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'type' => 'nullable|numeric|in:1,2', //1:Credit, 2:Debit
            'fuel_station_id' => 'required|numeric|exists:fuel_stations,id',

        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $fuels = FuelStationPaymentLog::select('fuel_station_payment_logs.*', )

                ->where('fuel_station_payment_logs.fuel_station_id', $fields['fuel_station_id'])
                ->with([
                    'fuel_station', 'user', 'order',
                ])

                ->orderBy('id', 'desc');

            // SEARCH BY KEYWORD
            if ($request->keyword) {
                $fuels->where(function ($query) use ($request) {
                    $query->where('amount', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('balance', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('order_id', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('created_at', 'LIKE', '%' . $request->keyword . '%')

                    ;});
            }

            if ($fields['type'] != '' && $fields['type'] != null) {
                $fuels->where('fuel_station_payment_logs.type', $fields['type']);
            }

            $fuels = $fuels->paginate($fields['limit']);

            $data = array(
                'fuels' => $fuels,
            );

            $res = Response::send('true',
                $data,
                $message = 'Success',
                $code = 200);
        }
        return $res;
    }

    public function earningLogs(Request $request)
    {

        $fields = $request->input();
        $validator = Validator::make($request->all(),
            ['limit' => 'required',
                'keyword' => 'nullable',
                'fuel_station_id' => 'required|numeric|exists:fuel_stations,id',
            ]);

        if ($validator->fails()) {$errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {

            $earning_logs = CustomerOrder::select('customer_orders.id as customer_order_id', 'customer_orders.customer_id', 'customer_orders.fuel_station_id', 'customer_orders.driver_id', 'customer_orders.truck_id', 'customer_orders.order_type', 'customer_orders.fuel_quantity_price', 'customer_orders.tax', 'customer_orders.delivery_charge', 'customer_orders.coupon_code', 'customer_orders.promotion_discount', 'customer_orders.other_charges', 'customer_orders.total', 'customer_orders.amount_commission', 'customer_orders.delivery_charge_commission', 'customer_orders.total_commission as Total_admin_earnings', DB::raw('(total - total_commission)as fual_station_earning'), 'customer_orders.delivery_date', 'customer_orders.delivery_time', 'customer_orders.pin', 'customer_orders.delivered_at', 'customer_orders.status', 'customer_orders.created_at as customer_order_created_on', 'customer_order_payments.payment_type', )
                ->leftjoin('customer_order_payments', 'customer_order_payments.order_id', '=', 'customer_orders.id')

                ->with([
                    'customers', 'drivers', 'fuel_stations', 'trucks',
                ])
                ->where('customer_orders.status', 6)
                ->where('customer_orders.fuel_station_id', $fields['fuel_station_id'])
                ->orderBy('customer_orders.id', 'desc');

            // SEARCH BY KEYWORD
            if ($request->keyword) {
                $earning_logs->where(function ($query) use ($request) {
                    $query->where('customer_orders.fuel_quantity_price', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('customer_orders.delivery_charge', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('customer_orders.tax', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('customer_orders.promotion_discount', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('customer_orders.total', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('customer_orders.amount_commission', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('customer_orders.delivery_charge_commission', 'LIKE', '%' . $request->keyword . '%')
                        ->orwhere('customer_orders.total_commission', 'LIKE', '%' . $request->keyword . '%')

                    ;});
            }

            $earning_logs = $earning_logs->paginate($fields['limit']);

            $res = Response::send('true',
                $data = [
                    'earning_logs' => $earning_logs,

                ],
                $message = 'Success',
                $code = 200);}
        return $res;
    }



    
 /* //CHANGE STATUS
 public function status(Request $request)
 {$fields = $request->input();

     $validator = Validator::make($request->all(),
         [
             'id' => 'required|numeric|exists:fuel_stations,id',
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
         $fuel_station = FuelStation::find($fields['id']);
         $fuel_station->status = $fields['status'];
         $result = $fuel_station->save();
         if ($result) {
             $user = User::where('user_id', $fields['id'])->where('role_id', '5')->first();
             $user->status = $fields['status'];
             $user->save();

             if ($request->status == 1) {
                 $error_message = __('success.publish_fuel_station');
             } else {
                 $error_message = __('success.unpublish_fuel_station');
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
} */
 public function FuelStationFuels(Request $request)
 {
        $validator = Validator::make($request->all(),
             [
                'limit' => 'required|numeric',
                'keyword' => 'nullable',

              ]);
        if ($validator->fails()) 
            {
                $errors = collect($validator->errors());
                $res = Response::send(false, [], $message = $errors, 422);

            } else
                {
                 $fuels = FuelStationStock::select('fuel_station_stocks.*','users.name_en as fuel_station_name_en','users.name_so as fuel_station_name_so') 
                            ->leftjoin('users', 'users.user_id', '=', 'fuel_station_stocks.fuel_station_id')
                                ->with([
                                   'fuel' 
                                        ])
                                ->where('users.role_id', '5')
                                ->where('fuel_station_stocks.status','1')
                                ->orderBy('fuel_station_stocks.id', 'desc');
                

                 if ($request->keyword) 
                    {   $search_text=$request->keyword;
                         $fuels->where(function ($query) use ($search_text) 
                            {
                                $query->where('users.name_en', 'LIKE', $search_text . '%')
                                      ->orWhere('users.name_so', 'LIKE', $search_text . '%')
                                      ->orWhereHas('fuel', function ($query)use($search_text) 
                                            {
                                                $query->where('fuel_types.fuel_en', 'Like', '%' . $search_text . '%')
                                                    ->orWhere('fuel_types.fuel_so', 'Like', '%' . $search_text . '%');
                                                return $query;       
                                            },);
                                     

                            });
                     }
                   

            $fuels = $fuels->paginate($request->limit);
       
            $data = array(
                  'fuels' => $fuels,
                         );

            $res = Response::send(true, $data, '', 200);
      }

       return $res;
}
}
