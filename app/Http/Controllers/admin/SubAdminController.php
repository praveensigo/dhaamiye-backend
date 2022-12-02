<?php

namespace App\Http\Controllers\admin;
use App\Models\Service\ResponseSender as Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\admin\SubAdmin;
use App\Models\admin\User;
use Illuminate\Support\Facades\DB;

use Validator;

class SubAdminController extends Controller
{
    public function add(Request $request)
    {
        $fields    = $request->input();
        $validator = Validator::make($request->all(), [
                'name_en' => 'nullable|required_without:name_so|min:3|max:100',
                'name_so' => 'nullable|required_without:name_en|min:3|max:100',
                'email'  => 'nullable|unique:users,email|email|max:200|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
                //'image'     => 'required|max:4096|mimes:png,jpg,jpeg',
                'password'    => 'required|min:6|max:16',
                'password_confirmation' => 'required|same:password|min:6|max:16',
            ],
            [
                 'name_en.required_without' => __('error2.name_en_required_without'),
                 'name_en.min' => __('error2.name_en_min'),
                 'name_en.max' =>  __('error2.name_en_max'),
                 'name_so.required_without' => __('error2.name_so_required_without'),
                 'name_so.min' => __('error2.name_so_min'),
                 'name_so.max' =>  __('error2.name_so_max'),
                'email.exists' => __('error2.email_exists'),
                'email.unique' => __('error2.email_unique'),
                'password.required' => __('error2.password_required'),
                'password_confirmation.required'=> __('error2.password_confirmation_required'),
                'password_confirmation.same' =>  __('error2.password_confirmation_same'),
                'password.min' => __('error2.password_min'),
                'password.max' => __('error2.password_max'),
                // 'image.required'=> __('error2.image_required'),
                // 'image.mimes'=> __('error2.image_mimes'),
                // 'image.max'=> __('error2.image_max'),
            ]
        );
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } 
        else
        {
            $sub_admin              = new SubAdmin;  
            $sub_admin->created_at  = date('Y-m-d H:i:s');
            $sub_admin->updated_at  = date('Y-m-d H:i:s');
            $result               = $sub_admin->save();

            if($result)
            {
                $user  = new User;
                $user->name_en        = $fields['name_en'];
                $user->name_so        = $fields['name_so'];

                    // $image_uploaded_path = '';
                    // if ($request->file('image')!=null) {
                    //     $uploadFolder = 'SubAdmin/images';
                    //     $image = $request->file('image');
                    //     $image_uploaded_path = $image->store($uploadFolder, 'public');
                    // }
                //$user->image= $image_uploaded_path;
                
                $user->email     = $fields['email'];
                $user->password  = bcrypt($fields['password']);
                $user->role_id   = 2;
                $user->user_id   = $sub_admin->id;
                $user->reg_status  = 1;
                $user->created_at= date('Y-m-d H:i:s');
                $result1 = $user->save();
                if($result1)
                {
                    
                $res    = Response::send('true', 
                $data = [], 
                $message = __('success2.create_sub_admin'), 
                $code = 200);
                }
                }
            else
            {
                $res    = sendResponse('false', 
                                       $data = [], 
                                       $message = __('error2.create_sub_admin'), 
                                       $code = 400);
            }
        }
        return $res;
    }
    public function update(Request $request)
    {
        $fields    = $request->input();
        $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:sub_admins,id',
                'name_en' => 'nullable|required_without:name_so|min:3|max:100',
                'name_so' => 'nullable|required_without:name_en|min:3|max:100',
                'email'  => 'nullable|unique:users,email|email|max:200|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
                //'image'     => 'required|max:4096|mimes:png,jpg,jpeg',
                //'password'    => 'required|min:6|max:16',
                //'password_confirmation' => 'required|same:password|min:6|max:16',
            ],
            [
                 'name_en.required_without' => __('error2.name_en_required_without'),
                 'name_en.min' => __('error2.name_en_min'),
                 'name_en.max' =>  __('error2.name_en_max'),
                 'name_so.required_without' => __('error2.name_so_required_without'),
                 'name_so.min' => __('error2.name_so_min'),
                 'name_so.max' =>  __('error2.name_so_max'),
                'email.exists' => __('error2.email_exists'),
                'email.unique' => __('error2.email_unique'),
                // 'password.required' => __('error2.password_required'),
                // 'password_confirmation.required'=> __('error2.password_confirmation_required'),
                // 'password_confirmation.same' =>  __('error2.password_confirmation_same'),
                // 'password.min' => __('error2.password_min'),
                // 'password.max' => __('error2.password_max'),
                // 'image.required'=> __('error2.image_required'),
                // 'image.mimes'=> __('error2.image_mimes'),
                // 'image.max'=> __('error2.image_max'),
            ]
        );
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } 
        else
        {               
            $sub_admin              = SubAdmin::find($fields['id']);  
            $sub_admin->created_at  = date('Y-m-d H:i:s');
            $sub_admin->updated_at  = date('Y-m-d H:i:s');
            $result               = $sub_admin->save();

            if($result)
            {                  
                $user  = User::where('user_id',$fields['id'])->where('role_id','2')->first();
                $user->name_en        = $fields['name_en'];
                $user->name_so        = $fields['name_so'];

                    // $image_uploaded_path = '';
                    // if ($request->file('image')!=null) {
                    //     $uploadFolder = 'SubAdmin/images';
                    //     $image = $request->file('image');
                    //     $image_uploaded_path = $image->store($uploadFolder, 'public');
                    // }
                //$user->image= $image_uploaded_path;
                
                $user->email     = $fields['email'];
               // $user->password  = bcrypt($fields['password']);
                $user->role_id   = 2;
                $user->user_id   = $sub_admin->id;
                $user->reg_status  = 1;
                $user->created_at= date('Y-m-d H:i:s');
                $result1 = $user->save();
                if($result1)
                {
                    
                $res    = Response::send('true', 
                $data = [], 
                $message = __('success2.update_sub_admin'), 
                $code = 200);
                }
                }
            else
            {
                $res    = sendResponse('false', 
                                       $data = [], 
                                       $message = __('error2.update_sub_admin'), 
                                       $code = 400);
            }
        }
        return $res;
    }  
    public function status(Request $request)
    {
        $fields    = $request->input();
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|numeric|exists:sub_admins,id',
                'status' => 'required|numeric',
            ],
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
    
        } else {
            $sub_admin = SubAdmin::find($fields['id']);
            $sub_admin->status = $fields['status'];
            $result = $sub_admin->save();
    
            if ($result) {
                $user  = User::where('user_id',$fields['id'])->where('role_id','2')->first();
                $user->status=$fields['status'];
                $user->save(); 
                if ($request->status == 1) {
                    $error_message = __('success2.block_sub_admin');
                } else {
                    $error_message = __('error2.block_sub_admin');
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
    public function index(Request $request)
    {
            $subadmin = User::select('users.*')
                                ->where('users.role_id','2')
                                ->where('users.reg_status','1')
                                ->orderBy('users.user_id')->get();
                            
                $data = array(
                      'subadmin' => $subadmin,
                             );

                $res = Response::send(true, $data, '', 200);
          
           return $res;
    } 
    public function addModules(Request $request)
    {
        $fields    = $request->input();
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|numeric|exists:sub_admins,id',
                'modules' => 'required',
            ],
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
    
        } else {
           
    
            if ($fields['modules']) {
               /*DELETE CURRENT MODULES*/
               DB::table('subadmin_modules')->where('subadmin_id',$fields['id'])->delete();
               /*INSERT NEW SPECIALIZATIONS*/
               $json_modules  = json_decode($fields['modules'], true);
               $i=0;
               foreach ($json_modules as $module)
               {   
                   DB::table('subadmin_modules')->insert(
                                        array(
                                               'subadmin_id'         =>$fields['id'], 
                                               'module_id' =>$module,
                                               'created_at'        =>date('Y-m-d H:i:s'),
                                        )
                                   ); 
                   $i++;
               }

              
                $res    = Response::send('true', 
                               [], 
                               $message = __('success2.add_sub_admin_modules'), 
                               $code = 200);
            } else {
                $res    = Response::send('false', 
                               [], 
                               $message = __('error2.add_sub_admin_modules'), 
                               $code = 400);
            }
        }
        return $res;
    }     
      
}