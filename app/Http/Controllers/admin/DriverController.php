<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service\ResponseSender as Response;
use App\Models\admin\Driver;
use App\Models\admin\CustomerOrderFuel;
use App\Models\admin\DriverPayments;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\admin\User;


use Validator;

class DriverController extends Controller
{
    
    public function add(Request $request)
    {
        $auth_user            = Auth::user();
        $role_id = $auth_user->role_id;
        $user_id = $auth_user->user_id;

        $fields    = $request->input();
        $validator = Validator::make($request->all(), [
                'name_en' => 'nullable|required_without:name_so|min:3|max:100',
                'name_so' => 'nullable|required_without:name_en|min:3|max:100',
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => 'required|integer|digits_between:6,14|unique:users,mobile',
                'email'  => 'nullable|unique:users,email|email|max:200|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
                'fuel_station' => 'required|numeric|exists:fuel_stations,id',
                'truck' => 'required|numeric|exists:trucks,id',
                'image'     => 'required|max:4096|mimes:png,jpg,jpeg',
                'passport'     => 'required|max:4096|mimes:png,jpg,jpeg',
                'license'     => 'required|max:4096|mimes:png,jpg,jpeg',
                'license_expiry'    => 'required|date_format:Y-m-d|after:today',
                'password'    => 'required|min:6|max:16',
                'password_confirmation' => 'required|same:password|min:6|max:16',
            ],
            [
                 //'name_en.required' =>  __('error2.name_en_required'),
                 'name_en.required_without' => __('error2.name_en_required_without'),
                 'name_en.min' => __('error2.name_en_min'),
                 'name_en.max' =>  __('error2.name_en_max'),
                 //'name_so.required' =>  __('error2.name_so_required'),
                 'name_so.required_without' => __('error2.name_so_required_without'),
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
            $driver->license_expiry        = $fields['license_expiry'];
            $driver->fuel_station_id = $fields['fuel_station'];
            $driver->truck_id = $fields['truck'];
            $driver->added_by        = $role_id;
            $driver->added_user        = $user_id;
            $driver->approval_by        = $role_id;
            $driver->approval_user        = $user_id;
            $driver->created_at  = date('Y-m-d H:i:s');
            $driver->updated_at  = date('Y-m-d H:i:s');
           
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
                $user->reg_status  = 1;
                $user->created_at= date('Y-m-d H:i:s');
                $result1 = $user->save();
                if($result1)
                {
                    
                $res    = Response::send('true', 
                $data = [], 
                $message = __('success2.create_driver'), 
                $code = 200);
                }
                }
            else
            {
                $res    = sendResponse('false', 
                                       $data = [], 
                                       $message = __('error2.create_driver'), 
                                       $code = 400);
            }
        }
        return $res;
    }   
    public function update(Request $request)
    {   
        $auth_user            = Auth::user();
        $role_id = $auth_user->role_id;
        $user_id = $auth_user->user_id;

        $fields    = $request->input();
        $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:drivers,id',
                'name_en' => 'nullable|required_without:name_so|min:3|max:100',
                'name_so' => 'nullable|required_without:name_en|min:3|max:100',
                'country_code' => 'required|numeric|exists:country_codes,id',
                'mobile' => ['required','integer','digits_between:6,14',            
                                Rule::unique('users', 'mobile')->ignore($request->id, 'user_id'),
                ],
                'email' => ['nullable','email',            
                                Rule::unique('users', 'email')->ignore($request->id, 'user_id'),
                ],
                'fuel_station' => 'required|numeric|exists:fuel_stations,id',
                'truck' => 'required|numeric|exists:trucks,id',
                'image'     => 'nullable|max:4096|mimes:png,jpg,jpeg',
                'passport'     => 'nullable|max:4096|mimes:png,jpg,jpeg',
                'license'     => 'nullable|max:4096|mimes:png,jpg,jpeg',
                'license_expiry'    => 'required|date_format:Y-m-d|after:today',
                
                
            ],
            [
                 //'name_en.required' =>  __('error2.name_en_required'),
                 'name_en.required_without' => __('error2.name_en_required_without'),
                 'name_en.min' => __('error2.name_en_min'),
                 'name_en.max' =>  __('error2.name_en_max'),
                 //'name_so.required' =>  __('error2.name_so_required'),
                 'name_so.required_without' => __('error2.name_so_required_without'),
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
            $driver              = Driver::find($fields['id']);
            $driver->license_expiry        = $fields['license_expiry'];
            $driver->fuel_station_id = $fields['fuel_station'];
            $driver->truck_id = $fields['truck'];
            $driver->updated_by  = $role_id;
            $driver->updated_user  = $user_id;
            $driver->updated_at  = date('Y-m-d H:i:s');

            $passport_uploaded_path = '';
            if ($request->file('passport')!=null) {
                $uploadFolder = 'driver/passport';
                $image = $request->file('passport');
                $passport_uploaded_path = $image->store($uploadFolder, 'public');
                $driver->passport_url= $passport_uploaded_path;

            }
            

            $license_uploaded_path = '';
            if ($request->file('license')!=null) {
                $uploadFolder = 'driver/license';
                $image = $request->file('license');
                $license_uploaded_path = $image->store($uploadFolder, 'public');
                $driver->license_url = $license_uploaded_path;
            }
           
            $result               = $driver->save();
            if($result)
            {
                $user  = User::where('user_id',$fields['id'])->where('role_id','4')->first();
                $user->name_en        = $fields['name_en'];
                $user->name_so        = $fields['name_so'];
                $image_uploaded_path = '';
                if ($request->file('image')!=null) 
                    {
                    $uploadFolder = 'driver/images';
                    $image = $request->file('image');
                    $image_uploaded_path = $image->store($uploadFolder, 'public');
                    $user->image= $image_uploaded_path;
                     }
                $user->country_code_id = $fields['country_code'];
                $user->mobile    = $fields['mobile'];
                $user->email     = $fields['email'];
                $user->updated_at= date('Y-m-d H:i:s');
                $result1 = $user->save();
                if($result1)
                {
                   
                 
                $res    = Response::send('true', 
                                       $data = [], 
                                       $message = __('success2.driver_update'), 
                                       $code = 200);

                }}
            else
            {
                $res    = sendResponse('false', 
                                       $data = [], 
                                       $message = __('error2.driver_update'), 
                                       $code = 400);
            }
        }
        return $res;
    }   

    public function index(Request $request)
    {
        $fields    = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'status' => 'nullable|numeric',

        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            

          
            $drivers = Driver::select( 'drivers.id as driver_id','drivers.fuel_station_id','drivers.truck_id','drivers.status as driver_status','drivers.online','drivers.created_at',
                                DB::raw('count(customer_orders.driver_id) as no_of_orders'),'users.name_en','users.name_so','users.email','users.image','users.country_code_id','users.mobile', )
                                ->join('users', 'users.user_id', '=', 'drivers.id')
                                ->leftjoin('customer_orders', 'customer_orders.driver_id', '=', 'drivers.id')
                                ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                                ->where('users.role_id','4')
                                ->where('users.reg_status','1')
                                ->with([
                                    'truck','fuel_station', 
                                        ])
                                ->groupBy('drivers.id','drivers.fuel_station_id','drivers.truck_id','driver_status','drivers.online','drivers.created_at','users.name_en','users.name_so','users.email','users.image','users.country_code_id','users.mobile')
                                ->orderBy('drivers.id','desc');
                            
                                        
                // if($fields['keyword'])
                //    {
                //         $drivers->where('users.name_en', 'Like', '%' . $fields['keyword'] . '%')
                //                 ->orWhere('users.name_so', 'Like', '%' . $fields['keyword'] . '%')
                //                 ->orWhere('users.email', 'Like', '%' . $fields['keyword'] . '%')
                //                 ->orWhere('users.mobile', 'Like', '%' . $fields['keyword'] . '%')
                //                 ->orWhere('drivers.truck_id', 'Like', '%' . $fields['keyword'] . '%')
                //                 ->orWhere('drivers.fuel_station_id', 'Like', '%' . $fields['keyword'] . '%'); 

                //     }
                   
                    if($fields['keyword'])
                            {
                                 $search_text=$fields['keyword'];
                                 $drivers->where('users.name_en', 'Like', '%' . $fields['keyword'] . '%')
                                        ->orWhere('users.name_so', 'Like', '%' . $fields['keyword'] . '%')
                                        ->orWhere('users.email', 'Like', '%' . $fields['keyword'] . '%')
                                        ->orWhere('users.mobile', 'Like', '%' . $fields['keyword'] . '%')
                                         ->orWhereHas('truck', function ($query2)use($search_text) 
                                             {
                                                 $query2->where('trucks.truck_no', 'Like',  $search_text . '%');
                                             })
                                             ->orWhereHas('fuel_station', function ($query2)use($search_text) 
                                             {
                                                 $query2->where('users.name_en', 'Like',  $search_text . '%')
                                                 ->orWhere('users.name_so', 'Like', '%' . $search_text. '%');
                                             });
                             }
                    if ($fields['status'] != '' && $fields['status'] != null) 
                    {
                       if($fields['status'] == 1)
                         {
                          $drivers->where('drivers.status', $fields['status']);
                         
                         }
                         if($fields['status'] == 2)
                         {
                          $drivers->where('drivers.status', $fields['status']);
                         
                         }
                        }
        
                 $drivers = $drivers->paginate($fields['limit']);
           
                $data = array(
                      'drivers' => $drivers,
                             );

                $res = Response::send(true, $data, '', 200);
          }
           return $res;
    }
    // public function index(Request $request)
    // {
    //     $fields    = $request->input();
    //     $validator = Validator::make($request->all(), [
    //         'limit' => 'required|numeric',
    //         'keyword' => 'nullable',
    //         'status' => 'nullable|numeric',

    //     ]);
    //     if ($validator->fails()) {
    //         $errors = collect($validator->errors());
    //         $res = Response::send(false, [], $message = $errors, 422);

    //     } else {
            

    //         // $drivers = Driver::select('drivers.id as driver_id','drivers.fuel_station_id','drivers.truck_id','drivers.passport_url','drivers.license_url','drivers.license_expiry','drivers.status as driver_status','drivers.added_by','drivers.added_user','drivers.approval_by','drivers.approval_user','drivers.updated_by','drivers.updated_user','drivers.online',
    //         //                         DB::raw('Count(customer_orders.id) as no_of_orders'),'users.*')
    //         $drivers = Driver::select( 'drivers.id as driver_id','drivers.fuel_station_id','drivers.truck_id','drivers.passport_url','drivers.license_url','drivers.license_expiry','drivers.status','drivers.added_by','drivers.added_user','drivers.approval_by','drivers.approval_user','drivers.updated_by','drivers.updated_user','drivers.online',
    //                                     'SELECT count(customer_orders.id)','users.*')
    //                             ->join('users', 'users.user_id', '=', 'drivers.id')
    //                             ->leftjoin('customer_orders', 'customer_orders.driver_id', '=', 'drivers.id')
    //                             ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
    //                             ->where('users.role_id','4')
    //                             ->where('users.reg_status','1')
    //                             ->with([
    //                                 'truck','fuel_station','orders' 
    //                                     ])
    //                                     //->orderBy('drivers.id');
    //                             ->groupBy('drivers.id','users.id','users.name_en','users.name_so','users.image','users.email','users.country_code_id','users.mobile','users.password','users.role_id','users.user_id','users.status','users.reg_status','users.fcm','users.remember_token','users.created_at','users.deleted_at','users.updated_at',
    //                             'drivers.fuel_station_id','drivers.truck_id','drivers.passport_url','drivers.license_url','drivers.license_expiry','drivers.status as driver_status','drivers.added_by','drivers.added_user','drivers.approval_by','drivers.approval_user','drivers.updated_by','drivers.updated_user','drivers.online');
                                        
    //             if($fields['keyword'])
    //                {
    //                     $drivers->where('users.name_en', 'Like', '%' . $fields['keyword'] . '%')
    //                             ->orWhere('users.name_so', 'Like', '%' . $fields['keyword'] . '%')
    //                             ->orWhere('users.email', 'Like', '%' . $fields['keyword'] . '%')
    //                             ->orWhere('users.mobile', 'Like', '%' . $fields['keyword'] . '%')
    //                             ->orWhere('drivers.truck_id', 'Like', '%' . $fields['keyword'] . '%')
    //                             ->orWhere('drivers.fuel_station_id', 'Like', '%' . $fields['keyword'] . '%'); 

    //                 }
                       
 


    //                 if ($fields['status'] != '' && $fields['status'] != null) 
    //                 {
    //                    if($fields['status'] == 1)
    //                      {
    //                       $drivers->where('drivers.status', $fields['status']);
                         
    //                      }
    //                      if($fields['status'] == 2)
    //                      {
    //                       $drivers->where('drivers.status', $fields['status']);
                         
    //                      }
    //                     }
        
    //              $drivers = $drivers->paginate($fields['limit']);
           
    //             $data = array(
    //                   'drivers' => $drivers,
    //                          );

    //             $res = Response::send(true, $data, '', 200);
    //       }
    //        return $res;
    // }
    public function details(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|exists:drivers,id',
            ],
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);
        } else {
            $drivers = Driver::select('drivers.id as driver_id','drivers.fuel_station_id','drivers.truck_id','drivers.passport_url','drivers.license_url','drivers.license_expiry','drivers.status as driver_status','drivers.added_by','drivers.added_user','drivers.approval_by','drivers.approval_user','drivers.updated_by','drivers.updated_user','drivers.online',
                                       'users.*')
                                    ->join('users', 'users.user_id', '=', 'drivers.id')
                                    ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                                    ->where('users.role_id','4')
                                    ->where('users.reg_status','1')
                                    ->with([
                                        'truck','fuel_station','orders' 
                                            ])
                                    ->where('drivers.id',$request->id)

                                    ->first();
                        $data = array(
                            'drivers' => $drivers,
                                );
                        $res = Response::send(true, $data, 'Driver found', 200);
                    }

        return $res;
    }
/*UPDATE STATUS*/
public function status(Request $request)
{
    $fields    = $request->input();
    $validator = Validator::make($request->all(),
        [
            'id' => 'required|numeric|exists:drivers,id',
            'status' => 'required|numeric',
        ],
    );
    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res    = Response::send('false', $data = [], $message = $errors, $code = 422);

    } else {
        $driver = Driver::find($fields['id']);
        $driver->status = $fields['status'];
        $result = $driver->save();

        if ($result) {
            $user = User::where('user_id',$fields['id'])->where('role_id','4')->first();
            $user->status=$fields['status'];
            $user->save(); 
            if ($request->status == 1) {
                $error_message = __('success2.block_driver');
            } else {
                $error_message = __('error2.block_driver');
            }
            $res    = Response::send('true', 
                           [], 
                           $message = $error_message, 
                           $code = 200);
        } else {
            $res    = Response::send('false', 
                           [], 
                           $message = $error_message, 
                           $code = 400);
        }
    }
    return $res;
}   
    public function approve(Request $request)
    { 
        $auth_user            = Auth::user();
        $role_id = $auth_user->role_id;
        $user_id = $auth_user->user_id;

        $fields    = $request->input();
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|exists:drivers,id',

            ],
            [
                'id.exists' => __('error2.id_exists'),
            ]
              );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);
                 } else{

            $user   = User::where('user_id',$fields['id'])->where('role_id','4')->first();


            if ($user->reg_status == 0) 
            {
            $user->reg_status = 1;
            $result = $user->save();
           
            if ($result)
                     {
                        $driver   = Driver::where('id',$fields['id'])->first();
                        $driver->approval_by        = $role_id;
                        $driver->approval_user        = $user_id;
                        $result2 = $driver->save();
                        $dmessage = 'Approved';
                    }
            }
                else 
                {
                $dmessage = 'Already approved';
                }
                $res  =  Response::send('true', [], $message = $dmessage, 
                        $code = 200);
      
    }
    return $res;
} 
public function pendingIndex(Request $request)
    {
        $fields    = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            

          
            $drivers = Driver::select( 'drivers.id as driver_id','drivers.fuel_station_id','drivers.passport_url','drivers.license_url','drivers.license_expiry','drivers.status as driver_status','drivers.added_by','drivers.added_user',
                                        'users.*')
                                ->join('users', 'users.user_id', '=', 'drivers.id')
                                ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                                ->where('users.role_id','4')
                                ->where('users.reg_status','0')
                                ->with([
                                    'fuel_station', 
                                        ])
                                ->orderBy('drivers.id');
                            
                                        
                if($fields['keyword'])
                   {
                        $drivers->where('users.name_en', 'Like', '%' . $fields['keyword'] . '%')
                                ->orWhere('users.name_so', 'Like', '%' . $fields['keyword'] . '%')
                                ->orWhere('users.email', 'Like', '%' . $fields['keyword'] . '%')
                                ->orWhere('users.mobile', 'Like', '%' . $fields['keyword'] . '%');
;                    }
                       
                 $drivers = $drivers->paginate($fields['limit']);
           
                $data = array(
                      'drivers' => $drivers,
                             );

                $res = Response::send(true, $data, '', 200);
          }
           return $res;
    }
    public function pendingDetails(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|exists:drivers,id',
            ],
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);
        } else {
            $drivers = Driver::select('drivers.id as driver_id','drivers.fuel_station_id','drivers.passport_url','drivers.license_url','drivers.license_expiry','drivers.status as driver_status','drivers.added_by','drivers.added_user',
                                       'users.*')
                                    ->join('users', 'users.user_id', '=', 'drivers.id')
                                    ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                                    ->where('users.role_id','4')
                                    ->where('users.reg_status','0')
                                    ->with([
                                        'fuel_station' 
                                            ])
                                    ->where('drivers.id',$request->id)

                                    ->first();
                        $data = array(
                            'drivers' => $drivers,
                                );
                        $res = Response::send(true, $data, 'Driver found', 200);
                    }

        return $res;
    }
    public function changePassword(Request $request)
    {   
        $auth_user            = Auth::user();
        $role_id = $auth_user->role_id;
        $user_id = $auth_user->user_id;

        $validator = Validator::make($request->all(),
        [
            'id' => 'required|numeric|exists:drivers,id',
            // 'password' => 'required|not_contains_space|min:6|max:16',
            'password'         => 'required|min:6|max:16',
             'password_confirmation' => 'required|same:password',
        ],
        [
            'id.exists' => __('error.id_exists'),
            'password.not_contains_space' => __('error.password_no_space'),
            'password.required' => __('error.password_required'),
            'password.min' => __('error.password_min'),
            'password.max' => __('error.password_max'),
            'password_confirmation.required' => 'Please enter the confirmation password',
            'password_confirmation.same' => 'The password confirmation does not match.'
        ]
         );
    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res = Response::send(false, [], $message = $errors, 422);
    } else {
        $user = User::where(['user_id' => $request->id, 'role_id' => 4])->first();
        $user->password = bcrypt($request->password);
        $result = $user->save();
        if($result)
        {
        $driver = Driver::where('id', $request->id )->first();
        $driver->updated_by  = $role_id;
        $driver->updated_user  = $user_id;
        $driver->updated_at  = date('Y-m-d H:i:s');
        $driver->save();
        }
        if ($driver->save()) {
            $res = Response::send(true, [], __('success.change_password'), 200);
        } else {
            $res = Response::send(false, [], __('error.change_password'), 400);
        }
    }
     return $res;
}
public function orders(Request $request)
{
    $fields    = $request->input();
    $validator = Validator::make($request->all(), [
        'id' => 'required|numeric|exists:drivers,id',
        'limit' => 'required|numeric',
        'keyword' => 'nullable',
        'status' => 'nullable|numeric',

    ]);
    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res = Response::send(false, [], $message = $errors, 422);

    } else {
        
        $orders = CustomerOrderFuel::select('customer_order_fuels.id as customer_order_fuels_id','customer_order_fuels.customer_id','customer_order_fuels.order_id','customer_order_fuels.fuel_type_id','customer_order_fuels.quantity','customer_order_fuels.price','customer_order_fuels.amount','customer_orders.*')
                                    ->join('customer_orders', 'customer_orders.id', '=', 'customer_order_fuels.order_id')
                                    ->with([
                                        'customer' ,'fuel','driver'
                                        ])
                                    ->where('customer_orders.driver_id',$fields['id'])
                                    ->orderBy('customer_order_fuels.id');
                   
                                    
            if($fields['keyword'])
               {    
                $search_text=$fields['keyword'];
                    $orders->where('customer_order_fuels.amount', 'Like', '%' . $search_text . '%')
                            ->orWhere('customer_order_fuels.order_id', 'Like', '%' . $search_text . '%')
                            ->orWhereHas('customer', function ($query)use($search_text) 
                                                {
                                                    $query->where('users.email', 'Like', '%' . $search_text . '%')
                                                        ->orWhere('users.mobile', 'Like', '%' . $search_text . '%')
                                                        ->orWhere('users.name_en', 'Like', '%' . $search_text . '%')
                                                        ->orWhere('users.name_so', 'Like', '%' . $search_text . '%');
                                                        
                                                    return $query;       
                                                },)
                             ->orWhereHas('fuel', function ($query)use($search_text) 
                                                {
                                                    $query->where('fuel_types.fuel_en', 'Like', '%' . $search_text . '%')
                                                        ->orWhere('fuel_types.fuel_so', 'Like', '%' . $search_text . '%');
                                                    return $query;       
                                                },);

                }
                   
                if ($fields['status'] != '' && $fields['status'] != null) 
                {
                   if($fields['status'] == 0)
                     {
                      $orders->where('customer_orders.status', $fields['status']);
                     
                     }
                     if($fields['status'] == 1)
                     {
                      $orders->where('customer_orders.status', $fields['status']);
                     
                     }
                     if($fields['status'] == 2)
                     {
                      $orders->where('customer_orders.status', $fields['status']);
                     
                     } if($fields['status'] == 3)
                     {
                      $orders->where('customer_orders.status', $fields['status']);
                     
                     } if($fields['status'] == 4)
                     {
                      $orders->where('customer_orders.status', $fields['status']);
                     
                     } if($fields['status'] == 5)
                     {
                      $orders->where('customer_orders.status', $fields['status']);
                     }
                     if($fields['status'] == 6)
                     {
                      $orders->where('customer_orders.status', $fields['status']);
                     }
                    }
    
             $orders = $orders->paginate($fields['limit']);
       
            $data = array(
                  'orders' => $orders,
                         );

            $res = Response::send(true, $data, '', 200);
      }
       return $res;
}



}

