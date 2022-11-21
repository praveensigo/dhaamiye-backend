<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\admin\Setting;
use App\Models\admin\User;
use App\Models\Service\ResponseSender as Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Validator;

class SettingsController extends Controller
{
    public function index()
    {
       $settings = Setting::select('settings.*')->get(); 
 
        $admins = DB::table('users')
                    ->select('id','name_en','name_so','image','email')
                    ->where('role_id', '1')
                    ->where('user_id', '1')
                    ->get();

        $data = array(
            'settings' => $settings,
            'admins' => $admins,
                     );

        $res    = Response::send('true', 
                               $data, 
                               $message ='Success', 
                            $code = 200);
        return $res;

    }  

    public function updateAdmin(Request $request) 
    {
    	$fields    = $request->input();
        $validator = Validator::make($request->all(),
            [
                'name_en' => 'nullable|required_without:name_so|min:3|max:100',
                'name_so' => 'nullable|required_without:name_en|min:3|max:100',
                'image'   => 'nullable|max:4096|mimes:png,jpg,jpeg' ,
                'email'   => ['nullable','email',            
                                Rule::unique('users', 'email')->ignore($fields['email'], 'email'),
                             ],
            ],
            
            [
                'name_en.required_without' => __('error2.name_en_required_without'),
                'name_en.min' => __('error2.name_en_min'),
                'name_en.max' =>  __('error2.name_en_max'),
                'name_so.required_without' => __('error2.name_so_required_without'),
                'name_so.min' => __('error2.name_so_min'),
                'name_so.max' =>  __('error2.name_so_max'),
                'image.mimes'    =>  __('error2.image_mimes'),
                'image.max'      =>  __('error2.image_max'),
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);

        } else {
        	$admin_id = auth('sanctum')->user()->id;
            $admin = User::find($admin_id);
            $admin->name_so     = $fields['name_so'];
            $admin->name_en     = $fields['name_en'];
            $admin->email     = $fields['email'];

            $image_uploaded_path = '';
           
            if ($request->file('image')!=null) 
                {
                    $uploadFolder = 'admin/admin';
                    $image = $request->file('image');
                    $image_uploaded_path = $image->store($uploadFolder, 'public');
                    $admin->image= $image_uploaded_path; 
                }
            
            $result = $admin->save();
           if( $result)
            {
                $res    = Response::send('true', 
                                $data    = [ ],
                               $message =__('success2.update_admin'), 
                               $code = 200);
            }else{
           
                $res    = Response::send('false', 
                               [], 
                               $message =__('error2.update_admin'), 
                               $code = 400);
            
        }
    }
        return $res;
    }  
    public function changePassword(Request $request)
    {
        $fields     = $request->input();
        $validator  = Validator::make($request->all(), 
            [
                 
                 'password'         => 'required|min:6|max:16',
                 'password_confirmation' => 'required|same:password',
            ],
            [
              'password.required'=> __('error2.password_required'),
              'password_confirmation.required' => __('error2.password_confirmation_required'),
              'password_confirmation.same' =>  __('error2.password_confirmation_same')
            ]);
        if($validator->fails())
        {   
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
            return $res;
        }
        else
        { 
        	$user_id = auth('sanctum')->user()->id;        
            $user    = User::find($user_id);
        	$user->password = bcrypt($fields['password']);           
            $result  = $user->save();
            if($result) {
                    $res    = Response::send('true', 
                                 $data    = [], 
                                 $message =__('success2.change_password'), 
                                 $code    = 200); 
            } else {
                   $res    = Response::send('false', 
                                 $data    = [], 
                                 $message = __('error2.change_password'), 
                                 $code    = 400); 
            }
        }
        return $res;
    }  

    public function updateCharges(Request $request)
    
    {
        $fields    = $request->input();
        $validator = Validator::make($request->all(),
            [
                'fuel_delivery_range' => 'required|numeric|max:100',
                'commision'  => 'required|numeric|max:100',
                'min_fuel_level' => 'required|numeric|max:100',
            ],
            [
                'fuel_delivery_range.required' =>  __('error2.fuel_delivery_range_required'),
                'commision.required' =>  __('error2.commision_required'),
                'min_fuel_level.required' =>  __('error2.min_fuel_level_required'),
            ]
            );
            if ($validator->fails()) 
                {
                    $errors = collect($validator->errors());
                    $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
                } else 
                {
                  $charges = Setting::find(1);
                  $charges->fuel_delivery_range = $fields['fuel_delivery_range'];
                  $charges->commision = $fields['commision'];
                  $charges->min_fuel_level = $fields['min_fuel_level'];
                  $result = $charges->save();
                  if ($result) 
                    {
                    $res  = Response::send('true', [], $message = __('success2.update_charges'), $code = 200);
                    } 
                  else 
                    {
                    $res = Response::send('false',[], $message = __('error2.update_charges'), $code = 400);
                    }
                } 
                return $res;
                }
    public function updateMaintenance(Request $request)
        {
            $fields    = $request->input();
            $validator = Validator::make($request->all(),
            [
                'customer_maintenance' => 'required|numeric',
                'customer_maintenance_reason_en'=>'required_if:customer_maintenance,1',
                'customer_maintenance_reason_so'=>'required_if:customer_maintenance,1',
                'driver_maintenance' => 'required|numeric',
                'driver_maintenance_reason_en'=>'required_if:driver_maintenance,1',
                'driver_maintenance_reason_so'=>'required_if:driver_maintenance,1',

            ],
            [
                'customer_maintenance.required' => __('error2.maintenance_required'),
                'customer_maintenance_reason_en.required_if' =>  __('error2.maintenance_reason_en_required_if'),
                'customer_maintenance_reason_so.required_if' =>  __('error2.maintenance_reason_so_required_if'), 
                'driver_maintenance.required' =>  __('error2.maintenance_required'),
                'driver_maintenance_reason_en.required_if' => __('error2.maintenance_reason_en_required_if'),
                'driver_maintenance_reason_so.required_if' =>  __('error2.maintenance_reason_so_required_if'),            
            ]
                    );
                    if ($validator->fails()) {
                        $errors = collect($validator->errors());
                        $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
                
                    } else {
                        $setting = Setting::find(1);
                        $setting->maintenance_customer = $fields['customer_maintenance'];
                        $setting->maintenance_reason_customer_en = $fields['customer_maintenance_reason_en'];
                        $setting->maintenance_reason_customer_so = $fields['customer_maintenance_reason_so'];
                        $setting->maintenance_driver = $fields['driver_maintenance'];
                        $setting->maintenance_reason_driver_en = $fields['driver_maintenance_reason_en'];
                        $setting->maintenance_reason_driver_so = $fields['driver_maintenance_reason_so'];
                        $result = $setting->save();
                
                        if ($result) {
                            $res    = Response::send('true', 
                                           [], 
                                           $message = __('success2.update_maintenance'), 
                                           $code = 200);
                        } else {
                            $res    = Response::send('false', 
                                           [], 
                                           $message = __('error2.update_maintenance'), 
                                           $code = 400);
                        }
                    }
                
                    return $res;
                }  
        public function updateVersionControl(Request $request)
            {
                 $fields    = $request->input();
                 $validator = Validator::make($request->all(),
                        [
                        'android_customer_version' => 'required|max:100',
                        'android_driver_version'  => 'required|max:100',
                        ]);
                     if ($validator->fails()) 
                      {
                        $errors = collect($validator->errors());
                        $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
                
                      }
                    else 
                        {
                            $versioncontrol = Setting::find(1);
                            $versioncontrol->android_version_customer = $fields['android_customer_version'];
                            $versioncontrol->android_version_driver = $fields['android_driver_version'];
                            $result = $versioncontrol->save();
                            if ($result) 
                                {
                                    $res = Response::send('true', [], 
                                            $message =__('success2.update_version_control'),  
                                            $code = 200);
                                }   
                            else 
                                {
                                    $res    = Response::send('false', [], 
                                              $message =__('error2.update_version_control'),  
                                              $code = 400);
                                }
                            }  
             return $res;  
             }
        
                      
}