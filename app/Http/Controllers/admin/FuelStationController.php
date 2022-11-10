<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\admin\FuelStation;
use App\Models\admin\FuelStationStock;
use App\Models\Service\ResponseSender as Response;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Validator;

class FuelStationController extends Controller
{ /*GET DOCTORS*/
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
            $fuel_stations = fuelStation::select('fuel_stations.id', 'users.name', 'users.image', 'added_by', 'location', 'latitude', 'longitude', 'address', 'image', 'fuel_stations.status', 'country_code_id', 'country_code', 'mobile', 'email', 'role_id', 'fuel_stations.created_at')
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->where('users.role_id', '5')
                ->where('users.reg_status', '1')
                ->orderBy('fuel_stations.id', 'desc');
            // SEARCH
            if ($request->keyword) {
                $fuel_stations->where(function ($query) use ($request) {
                    $query->where('name', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('mobile', 'LIKE', '%' . $request->keyword . '%');

                });
            }

            // Enables sorting - Published and unpublished
            if ($request->status != '' && $request->status != null) {
                if ($request->status == Customer::BLOCKED) {
                    $fuel_stations->blocked();
                } elseif ($request->status == Customer::ACTIVE) {
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

    public function add(Request $request)
    {$fields = $request->input();
        $validator = Validator::make($request->all(),
            [
                'name_en' => 'nullable|required_without:name_so|min:3|max:100',
                'name_so' => 'nullable|required_without:name_en|min:3|max:100',
                'image' => 'nullable|mimes:png,jpg,jpeg|max:1024|dimensions:max_width=600,max_height=600',
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
                'upi_id' => 'required|min:6|max:16',

            ],
            ['name_en.min' => __('error.name_min'),
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
            $fuel_station = new fuelStation;
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
                    $uploadFolder = 'fuel_station/images';
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
            [   'id' => 'required|numeric|exists:fuel_stations,id',
                 'name_en' => 'nullable|required_without:name_so|min:3|max:100',
                 'name_so' => 'nullable|required_without:name_en|min:3|max:100',
                 'image' => 'nullable|mimes:png,jpg,jpeg|max:1024|dimensions:max_width=600,max_height=600',
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => ['required', 'numeric', 'digits:10',
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
                'upi_id' => 'required|min:6|max:16',

            ],
            [
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
            $fuel_station = fuelStation::find($fields['id']);
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
                    $uploadFolder = 'fuel_station/images';
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
    /*GET fuel_station DETAILS*/
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

            $fuel_station = fuelStation::select('fuel_stations.id', 'users.name_en','users.name_so', 'users.image' ,'address', 'country_code_id', 'country_code', 'mobile', 'email',)
            ->join('users', 'users.user_id', '=', 'fuel_stations.id')
            ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
            ->where('fuel_stations.id', $fields['id'])
            ->where('users.role_id', '5')
            ->first();
       
            $fuel_station_detail = fuelStation::select('fuel_stations.id', 'users.name_en','users.name_so', 'users.image', 'added_by','added_user','updated_by','updated_user' ,'place', 'latitude', 'longitude', 'address', 'fuel_stations.status', 'country_code_id', 'country_code', 'mobile', 'email', 'role_id', 'fuel_stations.created_at')
                ->join('users', 'users.user_id', '=', 'fuel_stations.id')
                ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->where('fuel_stations.id', $fields['id'])
                ->where('users.role_id', '5')
                ->first();
            $bank_details = DB::table('fuel_station_bank_details')->select('bank_name', 'branch', 'account_no', 'account_holder_name', 'ifsc_code', 'upi_id', 'account_type', 'created_at')
                ->where('fuel_station_id', $fields['id'])
                ->get();

            $res = Response::send('true',
                $data = ['fuel_station' => $fuel_station,
                'details' => $fuel_station_detail,
                    'bank_details' => $bank_details,
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
