<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\service\ResponseSender as Response;
use App\Models\admin\Driver;
use App\Models\admin\CustomerOrderFuel;
use App\Models\admin\CustomerOrder;
use App\Models\admin\CustomerOrderPayment;
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
        $user_id = $auth_user->id;

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
                $uploadFolder = 'drivers/passports';
                $image = $request->file('passport');
                $passport_uploaded_path = $image->store($uploadFolder, 'public');
            }
            $driver->passport_url= $passport_uploaded_path;

            $license_uploaded_path = '';
            if ($request->file('license')!=null) {
                $uploadFolder = 'driver/licenses';
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
                        $uploadFolder = 'drivers';
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
        $user_id = $auth_user->id;

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
                $uploadFolder = 'drivers/passports';
                $image = $request->file('passport');
                $passport_uploaded_path = $image->store($uploadFolder, 'public');
                $driver->passport_url= $passport_uploaded_path;

            }
            

            $license_uploaded_path = '';
            if ($request->file('license')!=null) {
                $uploadFolder = 'drivers/licenses';
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
                    $uploadFolder = 'drivers';
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
            $date    = date('Y-m-d');
            $drivers = Driver::select('drivers.id as driver_id','drivers.fuel_station_id','drivers.truck_id','drivers.passport_url','drivers.license_url','drivers.license_expiry','drivers.status as driver_status','drivers.added_by','drivers.added_user','drivers.approval_by','drivers.approval_user','drivers.updated_by','drivers.updated_user','drivers.online',
                                       'users.*')
                                    ->join('users', 'users.user_id', '=', 'drivers.id')
                                    ->join('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                                    ->where('users.role_id','4')
                                    ->where('users.reg_status','1')
                                    ->with([
                                        'truck','fuel_station' 
                                            ])
                                    ->where('drivers.id',$request->id)

                                    ->first();
        $orders = CustomerOrder::select('customer_orders.id',)
                                    ->where('customer_orders.driver_id', $request->id)
                                    ->get()->count();
        $total_pending = CustomerOrderPayment::query()
                                    ->select(
                                        DB::raw('SUM(total_amount) AS total_pending_amount')
                                    )
                                    ->where('customer_order_payments.driver_id', $request->id)
                                    ->whereNotIn('customer_order_payments.order_id', function ($query) {
                                        $query->select('order_id')
                                        ->from('driver_payments');
                                    })
                                    ->first()->total_pending_amount;
        $total_completed = DriverPayments::query()
                                    ->select(
                                        DB::raw('SUM(amount) AS total_completed_amount')
                                    )
                                    ->where('driver_payments.driver_id', $request->id)
                                    ->first()->total_completed_amount;
        $location = DB::table('driver_location')->select('latitude','longitude',DB::raw('MAX(driver_location.created_at)'))
                                    ->where('driver_location.driver_id', $request->id)
                                    ->where(DB::raw('CAST(driver_location.date as date)'), '=', $date)
                                    ->groupBy('latitude','longitude')
                                    ->first();
       
                        $data = array(
                            'drivers' => $drivers,
                            'orders' => $orders,
                            'total_pending' => $total_pending,
                            'total_completed' => $total_completed,
                            'location' => $location,
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
        $user_id = $auth_user->id;

        $fields    = $request->input();
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|exists:drivers,id',
                'truck_id'=> 'required|exists:trucks,id'
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
                        $driver->truck_id        = $fields['truck_id'];
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
        $user_id = $auth_user->id;

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
        
        // $orders = CustomerOrderFuel::select('customer_order_fuels.id as customer_order_fuels_id','customer_order_fuels.customer_id','customer_order_fuels.order_id','customer_order_fuels.fuel_type_id','customer_order_fuels.quantity','customer_order_fuels.price','customer_order_fuels.amount','customer_orders.*')
        //                             ->join('customer_orders', 'customer_orders.id', '=', 'customer_order_fuels.order_id')
        //                             ->with([
        //                                 'customer' ,'fuel','driver'
        //                                 ])
        //                             ->where('customer_orders.driver_id',$fields['id'])
        //                             ->orderBy('customer_orders.id');
        $orders = CustomerOrder::select('customer_orders.*')
                                //->join('customer_order_fuels', 'customer_order_fuels.order_id', '=', 'customer_orders.id')
                                ->with([
                                    'customer' ,'fuels','driver','fuel'
                                    ])
                                ->where('customer_orders.driver_id',$fields['id'])
                                ->orderBy('customer_orders.id');         
                                    
            if($fields['keyword'])
               {    
                $search_text=$fields['keyword'];
                    $orders->where('customer_orders.fuel_quantity_price', 'Like', '%' . $search_text . '%')
                            ->orWhere('customer_orders.id', 'Like', '%' . $search_text . '%')
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
                                                },)
                            ;

                }
                   
                if ($fields['status'] != '' && $fields['status'] != null) 
                { 
                    $orders->where('customer_orders.status', $fields['status']);

                //    if($fields['status'] == 0)
                //      {
                //       $orders->where('status', $fields['status']);
                     
                //      }
                //      if($fields['status'] == 1)
                //      {
                //       $orders->where('customer_orders.status', $fields['status']);
                     
                //      }
                //      if($fields['status'] == 2)
                //      {
                //       $orders->where('customer_orders.status', $fields['status']);
                     
                //      } if($fields['status'] == 3)
                //      {
                //       $orders->where('customer_orders.status', $fields['status']);
                     
                //      } if($fields['status'] == 4)
                //      {
                //       $orders->where('customer_orders.status', $fields['status']);
                     
                //      } if($fields['status'] == 5)
                //      {
                //       $orders->where('customer_orders.status', $fields['status']);
                //      }
                //      if($fields['status'] == 6)
                //      {
                //       $orders->where('customer_orders.status', $fields['status']);
                //      }
                    }
    
             $orders = $orders->paginate($fields['limit']);
       
            $data = array(
                  'orders' => $orders,
                         );

            $res = Response::send(true, $data, '', 200);
      }
       return $res;
}

// public function earnings(Request $request)
// {
//     $fields    = $request->input();
//     $validator = Validator::make($request->all(), [
//         'id' => 'required|numeric|exists:drivers,id',
//         'limit' => 'required|numeric',
//         'keyword' => 'nullable',
//         'status' => 'nullable|numeric',

//     ]);
//     if ($validator->fails()) {
//         $errors = collect($validator->errors());
//         $res = Response::send(false, [], $message = $errors, 422);

//     } else {
       
//         $payments = CustomerOrderPayment::select(DB::raw('DATE(created_at) as day' ))
//                                 ->where('customer_order_payments.driver_id',$fields['id'])
//                                 ->groupBy(DB::raw('DATE(created_at)'),)
//                                 ->get();
       
//          foreach($payments as $payment){

//             $payment-> total_completed = DriverPayments::query()
//                                         ->select(
//                                             DB::raw('SUM(amount) AS total_completed_amount')
//                                         )
//                                         ->whereDATE('created_at', $payment->day)
//                                         ->first()->total_completed_amount;

//             $payment-> mobile_completed = DriverPayments::query()
//                                         ->select(
//                                             DB::raw('SUM(amount) AS mobile_amount')
//                                         )
//                                         ->where('payment_type', '1')
//                                         ->whereDATE('created_at', $payment->day)
//                                         ->first()->mobile_amount;
//             $payment-> cash_completed = DriverPayments::query()
//                                         ->select(
//                                             DB::raw('SUM(amount) AS cash')
//                                         )
//                                         ->where('payment_type', '2')
//                                         ->whereDATE('created_at', $payment->day)
//                                         ->first()->cash;
//             $payment-> total_pending = CustomerOrderPayment::query()
//                                         ->select(
//                                             DB::raw('SUM(total_amount) AS total_pending_amount')
//                                         )
//                                         ->whereDATE('created_at', $payment->day)
//                                         ->whereNotIn('customer_order_payments.order_id', function ($query) {
//                                             $query->select('order_id')
//                                             ->from('driver_payments');
//                                         })
//                                         ->first()->total_pending_amount;

//              $payment-> mobile_pending = CustomerOrderPayment::query()
//                                         ->select(
//                                             DB::raw('SUM(total_amount) AS mobile_pending')
//                                         )
//                                         ->whereDATE('created_at', $payment->day)
//                                         ->where('payment_type', '1')
//                                         ->whereNotIn('customer_order_payments.order_id', function ($query) {
//                                             $query->select('order_id')
//                                             ->from('driver_payments');
//                                         })
//                                         ->first()->mobile_pending;

//             $payment-> cash_pending = CustomerOrderPayment::query()
//                                         ->select(
//                                             DB::raw('SUM(total_amount) AS cash_pending')
//                                         )
//                                         ->whereDATE('created_at', $payment->day)
//                                         ->where('payment_type', '2')
//                                         ->whereNotIn('customer_order_payments.order_id', function ($query) {
//                                             $query->select('order_id')
//                                             ->from('driver_payments');
//                                         })
//                                         ->first()->cash_pending;                            
//                                 }
//             if ($fields['keyword']) 
//                            {
//                          $search_text=$fields['keyword'];
//                       $payments->where('customer_order_payments.total_amount',  $search_text . '%')
                            
//                                                   ;
                            
//                            }                       
//             $data = array(
//                   'payments' => $payments,
//                          );

//             $res = Response::send(true, $data, '', 200);
//       }
//        return $res;
// }
public function earnings1(Request $request)
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
       
        $payments = CustomerOrderPayment::select(DB::raw('DATE(created_at) as day' ))
                                ->where('customer_order_payments.driver_id',$fields['id'])
                                ->groupBy(DB::raw('DATE(created_at)'),)
                                ->orderBy('day','desc')
                                ->get();
         if ($fields['status'] == '0') 
         {
       
         foreach($payments as $payment){
           
            $payment-> total_pending = CustomerOrderPayment::query()
                                        ->select(
                                            DB::raw('SUM(total_amount) AS total_pending_amount')
                                        )
                                        ->whereDATE('created_at', $payment->day)
                                        ->whereNotIn('customer_order_payments.order_id', function ($query) {
                                            $query->select('order_id')
                                            ->from('driver_payments');
                                        })
                                        ->first()->total_pending_amount;

             $payment-> mobile_pending = CustomerOrderPayment::query()
                                        ->select(
                                            DB::raw('SUM(total_amount) AS mobile_pending')
                                        )
                                        ->whereDATE('created_at', $payment->day)
                                        ->where('payment_type', '1')
                                        ->whereNotIn('customer_order_payments.order_id', function ($query) {
                                            $query->select('order_id')
                                            ->from('driver_payments');
                                        })
                                        ->first()->mobile_pending;

            $payment-> cash_pending = CustomerOrderPayment::query()
                                        ->select(
                                            DB::raw('SUM(total_amount) AS cash_pending')
                                        )
                                        ->whereDATE('created_at', $payment->day)
                                        ->where('payment_type', '2')
                                        ->whereNotIn('customer_order_payments.order_id', function ($query) {
                                            $query->select('order_id')
                                            ->from('driver_payments');
                                        })
                                        ->first()->cash_pending;                            
                                }
                            }
           elseif ($fields['status'] == '1') 
                {
                          
                 foreach($payments as $payment){
                   
                $payment-> total_completed = DriverPayments::query()
                                                ->select(
                                                    DB::raw('SUM(amount) AS total_completed_amount')
                                                     )
                                                 ->whereDATE('created_at', $payment->day)
                                                ->first()->total_completed_amount;
                   
                 $payment-> mobile_completed = DriverPayments::query()
                                                        ->select(
                                                        DB::raw('SUM(amount) AS mobile_amount')
                                                        )
                                                        ->where('payment_type', '1')
                                                        ->whereDATE('created_at', $payment->day)
                                                        ->first()->mobile_amount;
                 $payment-> cash_completed = DriverPayments::query()
                                                           ->select(
                                                               DB::raw('SUM(amount) AS cash')
                                                           )
                                                           ->where('payment_type', '2')
                                                           ->whereDATE('created_at', $payment->day)
                                                           ->first()->cash;
                
                                               }}
                else{
                          
              foreach($payments as $payment){
                                                  
                $payment-> total_completed = DriverPayments::query()
                                        ->select(
                                            DB::raw('SUM(amount) AS total_completed_amount')
                                        )
                                        ->whereDATE('created_at', $payment->day)
                                        ->first()->total_completed_amount;

                 $payment-> mobile_completed = DriverPayments::query()
                                            ->select(
                                                DB::raw('SUM(amount) AS mobile_amount')
                                            )
                                            ->where('payment_type', '1')
                                            ->whereDATE('created_at', $payment->day)
                                            ->first()->mobile_amount;
            $payment-> cash_completed = DriverPayments::query()
                                        ->select(
                                            DB::raw('SUM(amount) AS cash')
                                        )
                                        ->where('payment_type', '2')
                                        ->whereDATE('created_at', $payment->day)
                                        ->first()->cash;
            $payment-> total_pending = CustomerOrderPayment::query()
                                        ->select(
                                            DB::raw('SUM(total_amount) AS total_pending_amount')
                                        )
                                        ->whereDATE('created_at', $payment->day)
                                        ->whereNotIn('customer_order_payments.order_id', function ($query) {
                                            $query->select('order_id')
                                            ->from('driver_payments');
                                        })
                                        ->first()->total_pending_amount;

            $payment-> mobile_pending = CustomerOrderPayment::query()
                                        ->select(
                                            DB::raw('SUM(total_amount) AS mobile_pending')
                                        )
                                        ->whereDATE('created_at', $payment->day)
                                        ->where('payment_type', '1')
                                        ->whereNotIn('customer_order_payments.order_id', function ($query) {
                                            $query->select('order_id')
                                            ->from('driver_payments');
                                        })
                                        ->first()->mobile_pending;

            $payment-> cash_pending = CustomerOrderPayment::query()
                                        ->select(
                                            DB::raw('SUM(total_amount) AS cash_pending')
                                        )
                                        ->whereDATE('created_at', $payment->day)
                                        ->where('payment_type', '2')
                                        ->whereNotIn('customer_order_payments.order_id', function ($query) {
                                            $query->select('order_id')
                                            ->from('driver_payments');
                                        })
                                        ->first()->cash_pending;                            
                                
                                               
                                                }}
            if ($fields['keyword']) 
                           {
                         $search_text=$fields['keyword'];
                         $payments = $payments
                         //->where('DATE(created_at)', 'LIKE', $search_text . '%')
                                            ->where('driver_payments.created_at', $search_text )
                                                  ;
                            
                           }                       
            $data = array(
                  'payments' => $payments,
                         );

            $res = Response::send(true, $data, '', 200);
      }
       return $res;
}
public function earnings(Request $request)
{
    $fields    = $request->input();
    $validator = Validator::make($request->all(), [
        'id' => 'required|numeric|exists:drivers,id',
        'limit' => 'required|numeric',
        'type' => 'nullable|numeric',

    ]);
    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res = Response::send(false, [], $message = $errors, 422);

    } else {
        $driver = DB::table('drivers')->select('*')           
                    ->where('id', $fields['id'])
                    ->orderBy('created_at','desc')
                    ->first();
        $total_earned = round($driver->total_mobile_earned + $driver->total_cash_earned, 2);
        $total_mobile_earned =  $driver->total_mobile_earned;
        $total_cash_earned = $driver->total_cash_earned;
        $cash_in_hand = round($total_earned - $driver->total_paid, 2);
        $payments = DB::table('driver_payments')->select('*')           
                    ->where('driver_id', $fields['id'])
                    ->orderBy('created_at','desc');
                    
            if ($fields['type']) 
                {
                $payments->where('driver_payments.type', $fields['type'] );
                }  
                if ($fields['keyword']) {
                    $search = $fields['keyword'];
                    $payments->where(function ($query) use ($search) {
                        $query->where('notes', 'LIKE', '%' . $search . '%')
                            ;
    
                    });
                }
            $payments = $payments->paginate($fields['limit']);
                     
            $data = array(
                'total_earned' => $total_earned,
                'total_mobile_earned' => $total_mobile_earned,
                'total_cash_earned' => $total_cash_earned,
                'cash_in_hand' => $cash_in_hand,


                  'payments' => $payments,
                         );

            $res = Response::send(true, $data, '', 200);
      }
       return $res;
}
public function getFuelStations(Request $request)
{
        $fuel_stations = DB::table('users')
                        ->select('user_id','name_en','name_so')
                        ->where('status', 1)
                        ->where('reg_status', 1)
                        ->where('role_id',5)
                        ->orderBy('user_id')->get();
        $data = array(
            'fuel_stations' => $fuel_stations,
        );
        $res = Response::send(true, $data, 'Fuel Stations found', 200);
    

    return $res;
}
public function getTrucks(Request $request)
{
    $validator = Validator::make($request->all(), [
        'fuel_station_id' => 'required|numeric|exists:fuel_stations,id',
    ]);

    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res = Response::send(false, [], $message = $errors, 422);

    } else {

        
        $trucks = DB::table('trucks')
                        ->select('id','truck_no')
                        ->where('status', 1)
                        ->where('fuel_station_id',$request->fuel_station_id)
                        ->orderBy('trucks.id')->get();
        $data = array(
            'trucks' => $trucks,
        );
        $res = Response::send(true, $data, 'Trucks found', 200);
    }

    return $res;
}
public function getDrivers(Request $request)
{
    $drivers = DB::table('users')
                        ->select('user_id','name_en','name_so','country_code_id','email','mobile')
                        ->where('status', 1)
                        ->where('reg_status', 1)
                        ->where('role_id',4)
                        ->orderBy('user_id')->get();
        $data = array(
            'drivers' => $drivers,
        );
        $res = Response::send(true, $data, 'Drivers found', 200);
    

    return $res;
}
public function getCustomers(Request $request)
{
    $customers = DB::table('users')
                        ->select('user_id','name_en','name_so','country_code_id','email','mobile')
                        ->where('status', 1)
                        ->where('reg_status', 1)
                        ->where('role_id',3)
                        ->orderBy('user_id')->get();
        $data = array(
            'customers' => $customers,
        );
        $res = Response::send(true, $data, 'customers found', 200);
    

    return $res;
}
public function getFuels(Request $request)
{
    $fuels = DB::table('fuel_types')
                        ->select('id','fuel_en','fuel_so')
                        ->where('status', 1)
                        ->orderBy('id')->get();
        $data = array(
            'fuels' => $fuels,
        );
        $res = Response::send(true, $data, 'fuels found', 200);
    

    return $res;
}
public function getRoles(Request $request)
{
    $roles = DB::table('roles')
                        ->select('id','role',)
                        ->orderBy('id')->get();
        $data = array(
            'roles' => $roles,
        );
        $res = Response::send(true, $data, 'roles found', 200);
    

    return $res;
}
public function getFuelstationDrivers(Request $request)
{
    $validator = Validator::make($request->all(), [
        'fuel_station_id' => 'required|numeric|exists:fuel_stations,id',
    ]);

    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res = Response::send(false, [], $message = $errors, 422);

    } else {

    $drivers = DB::table('users')
                        ->select('user_id','name_en','name_so','country_code_id','email','mobile')
                        ->join('drivers', 'drivers.id', '=', 'users.user_id')
                        ->where('drivers.status', 1)
                        ->where('users.reg_status', 1)
                        ->where('users.role_id',4)
                        ->where('drivers.fuel_station_id',$request->fuel_station_id)
                        ->orderBy('drivers.id')->get();
        $data = array(
            'drivers' => $drivers,
        );
        $res = Response::send(true, $data, 'Drivers found', 200);
    
    }
    return $res;
}
public function getFuelTrucks(Request $request)
{
    $validator = Validator::make($request->all(), [
        'fuel_station_id' => 'required|numeric|exists:fuel_stations,id',
    ]);

    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res = Response::send(false, [], $message = $errors, 422);

    } else {

        
        $trucks = DB::table('trucks')
                        ->join('truck_fuels', 'truck_fuels.truck_id', '=', 'trucks.id')
                        ->select('trucks.id','trucks.truck_no')
                        ->where('status', 1)
                        ->where('fuel_station_id',$request->fuel_station_id)
                        ->orderBy('trucks.id')->distinct()->get();
        $data = array(
            'trucks' => $trucks,
        );
        $res = Response::send(true, $data, 'Trucks found', 200);
    }

    return $res;
}
}

