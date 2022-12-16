<?php

namespace App\Http\Controllers\admin;
use App\Models\service\ResponseSender as Response;
use App\Models\admin\Truck;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;

class TruckController extends Controller
{
    public function add(Request $request)
    {   
        $auth_user            = Auth::user();
        $role_id = $auth_user->role_id;
        $user_id = $auth_user->id;

        $fields    = $request->input();
        $validator = Validator::make($request->all(), [
                'truck_no'              => 'required|min:3|unique:trucks,truck_no',
                'manufacturer'          => 'required|min:3|max:100',
                'manufactured_year'     => 'required|digits:4|integer|max:'.(date('Y')),
                'model'                 => 'required|min:3|max:100',
                'color'                 => 'required|min:3|max:100',
                'chassis_no'            => 'required|min:3|max:100',
                'engine_no'             => 'required|min:3|max:100',
                'fuel_station'          => 'required|numeric|exists:fuel_stations,id',
                'mot_certificate'       => 'required|max:4096|mimes:png,jpg,jpeg',
                'insurance_certificate' => 'required|max:4096|mimes:png,jpg,jpeg',
                'truck_certificate'     => 'required|max:4096|mimes:png,jpg,jpeg',

            ],
            [
                'truck_no.required'          =>  __('error2.truck_no_required'),
                'truck_no.unique'          =>  __('error2.truck_no_unique'),

                // 'truck_no.min'               => __('error2.truck_no_min'),
                // 'truck_no.max'               =>  __('error2.truck_no_max'),
                'manufacturer.required'      =>  __('error2.manufacturer_required'),
                'manufacturer.min'           => __('error2.manufacturer_min'),
                'manufacturer.max'           =>  __('error2.manufacturer_max'),
                'manufactured_year.required' =>  __('error2.manufactured_year_required'),
                'model.required'             =>  __('error2.model_required'),
                'color.required'             =>  __('error2.color_required'),
                'chassis_no.required'        =>  __('error2.chassis_no_required'),
                'engine_no.required'         =>  __('error2.engine_no_required'),
                'fuel_station.required'      =>  __('error2.fuel_station_required'),
                'mot_certificate.required'   =>  __('error2.mot_certificate_required'),
                'insurance_certificate.required' =>  __('error2.insurance_certificate_required'),
                'truck_certificate.required' =>  __('error2.truck_certificate_required'),

            ]
        );
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } 
        else
        {
            $truck                     = new Truck;  
            $truck->truck_no           = $fields['truck_no'];
            $truck->manufacturer       = $fields['manufacturer'];
            $truck->manufactured_year  = $fields['manufactured_year'];
            $truck->model           = $fields['model'];
            $truck->color           = $fields['color'];
            $truck->chassis_no           = $fields['chassis_no'];
            $truck->engine_no           = $fields['engine_no'];
            $truck->fuel_station_id           = $fields['fuel_station'];
            $truck->added_by        = $role_id;
            $truck->added_user        = $user_id;
            $truck->approval_by        = $role_id;
            $truck->approval_user        = $user_id;
            $truck->reg_status           = '1';
            $truck->created_at  = date('Y-m-d H:i:s');
            $truck->updated_at  = date('Y-m-d H:i:s');

            $mot_uploaded_path = '';
            if ($request->file('mot_certificate')!=null) {
                $uploadFolder = 'Trucks/mot_certificates';
                $image = $request->file('mot_certificate');
                $mot_uploaded_path = $image->store($uploadFolder, 'public');
            }
            $truck->mot_certificate_url= $mot_uploaded_path;


            $insurance_uploaded_path = '';
            if ($request->file('insurance_certificate')!=null) {
                $uploadFolder = 'Trucks/insurance_certificates';
                $image = $request->file('insurance_certificate');
                $insurance_uploaded_path = $image->store($uploadFolder, 'public');
            }
            $truck->insurance_certificate_url= $insurance_uploaded_path;


           
            $certificate_uploaded_path = '';
            if ($request->file('truck_certificate')!=null) {
                $uploadFolder = 'Trucks/truck_certificates';
                $image = $request->file('truck_certificate');
                $certificate_uploaded_path = $image->store($uploadFolder, 'public');
            }
            $truck->truck_certificate_url= $certificate_uploaded_path;

            $result               = $truck->save();
            
                $res    = Response::send('true', 
                                       $data =[] , 
                                       $message = __('success2.register_success'), 
                                       $code = 200);

                
            }
            return $res;
        }

        public function update(Request $request)
        {    $auth_user            = Auth::user();
            $role_id = $auth_user->role_id;
            $user_id = $auth_user->id;
            $fields    = $request->input();
            $validator = Validator::make($request->all(), [
                    'id' => 'required|numeric|exists:trucks,id',
                    'truck_no' => ['required','min:3',            
                                Rule::unique('trucks','truck_no')->ignore($fields['id'], 'id'),
                            ],
                    'manufacturer'          => 'required|min:3|max:100',
                    'manufactured_year'     => 'required|digits:4|integer|max:'.(date('Y')),
                    'model'                 => 'required|min:3|max:100',
                    'color'                 => 'required|min:3|max:100',
                    'chassis_no'            => 'required|min:3|max:100',
                    'engine_no'             => 'required|min:3|max:100',
                    'fuel_station'          => 'required|numeric|exists:fuel_stations,id',
                    'mot_certificate'       => 'nullable|max:4096|mimes:png,jpg,jpeg',
                    'insurance_certificate' => 'nullable|max:4096|mimes:png,jpg,jpeg',
                    'truck_certificate'     => 'nullable|max:4096|mimes:png,jpg,jpeg',
    
                ],
                [
                    'truck_no.required'          =>  __('error2.truck_no_required'),
                    // 'truck_no.min'               => __('error2.truck_no_min'),
                    // 'truck_no.max'               =>  __('error2.truck_no_max'),
                    'manufacturer.required'      =>  __('error2.manufacturer_required'),
                    'manufacturer.min'           => __('error2.manufacturer_min'),
                    'manufacturer.max'           =>  __('error2.manufacturer_max'),
                    'manufactured_year.required' =>  __('error2.manufactured_year_required'),
                    'model.required'             =>  __('error2.model_required'),
                    'color.required'             =>  __('error2.color_required'),
                    'chassis_no.required'        =>  __('error2.chassis_no_required'),
                    'engine_no.required'         =>  __('error2.engine_no_required'),
                    'fuel_station.required'      =>  __('error2.fuel_station_required'),
                    'mot_certificate.required'   =>  __('error2.mot_certificate_required'),
                    'insurance_certificate.required' =>  __('error2.insurance_certificate_required'),
                    'truck_certificate.required' =>  __('error2.truck_certificate_required'),
    
                ]
            );
            if ($validator->fails()) 
            {
                $errors = collect($validator->errors());
                $res = Response::send('false', $data = [], $message = $errors, $code = 422);
            } 
            else
            {
                $truck                     = Truck::find($fields['id']); 
                $truck->truck_no           = $fields['truck_no'];
                $truck->manufacturer       = $fields['manufacturer'];
                $truck->manufactured_year  = $fields['manufactured_year'];
                $truck->model               = $fields['model'];
                $truck->color               = $fields['color'];
                $truck->chassis_no          = $fields['chassis_no'];
                $truck->engine_no           = $fields['engine_no'];
                $truck->fuel_station_id      = $fields['fuel_station'];
                $truck->updated_by  = $role_id;
                $truck->updated_user  = $user_id;
                $truck->updated_at  = date('Y-m-d H:i:s');
                $truck->reg_status           = '1';

                $mot_uploaded_path = '';
                if ($request->file('mot_certificate')!=null) {
                    $uploadFolder = 'Trucks/mot_certificates';
                    $image = $request->file('mot_certificate');
                    $mot_uploaded_path = $image->store($uploadFolder, 'public');
                    $truck->mot_certificate_url= $mot_uploaded_path;

                }
    
                $insurance_uploaded_path = '';
                if ($request->file('insurance_certificate')!=null) {
                    $uploadFolder = 'Trucks/insurance_certificates';
                    $image = $request->file('insurance_certificate');
                    $insurance_uploaded_path = $image->store($uploadFolder, 'public');
                    $truck->insurance_certificate_url= $insurance_uploaded_path;

                }
    
                $certificate_uploaded_path = '';
                if ($request->file('truck_certificate')!=null) {
                    $uploadFolder = 'Trucks/truck_certificates';
                    $image = $request->file('truck_certificate');
                    $certificate_uploaded_path = $image->store($uploadFolder, 'public');
                    $truck->truck_certificate_url= $certificate_uploaded_path;

                }
    
                    $result               = $truck->save();
                    
                     
                    $res    = Response::send('true', 
                                           $data = [], 
                                           $message = __('success2.truck_update'), 
                                           $code = 200);
    
                    
                }
                return $res;
            }
     public function index(Request $request)
     {
            $validator = Validator::make($request->all(),
                 [
                    'limit' => 'required|numeric',
                    'keyword' => 'nullable',
                    'status' => 'nullable',

                  ]);
            if ($validator->fails()) 
                {
                    $errors = collect($validator->errors());
                    $res = Response::send(false, [], $message = $errors, 422);

                } else
                    {

                  
                $trucks = Truck::select('trucks.*','users.name_en as fuel_station_name_en','users.name_so as fuel_station_name_so','users.email as fuel_station_email','users.image as fuel_station_image','users.country_code_id as fuel_station_country_code_id ','users.mobile  as fuel_station_mobile','users.role_id   as fuel_station_role_id ','users.user_id  as fuel_station_user_id','users.status  as fuel_station_status','users.reg_status  as fuel_station_reg_status') 
                                ->leftjoin('users', 'users.user_id', '=', 'trucks.fuel_station_id')
                                ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                                ->with([
                                        'fuel_station' 
                                        ])
                                ->where('users.role_id', '5')
                                ->where('trucks.reg_status','1')
                                ->orderBy('trucks.id', 'desc');


                     if ($request->keyword) 
                        {
                             $trucks->where(function ($query) use ($request) 
                                {

                                    $query->where('trucks.truck_no', 'LIKE', $request->keyword . '%')
                                            ->orWhere('trucks.manufacturer', 'LIKE', $request->keyword . '%')
                                            ->orWhere('trucks.chassis_no', 'LIKE', $request->keyword . '%')
                                            ->orWhere('trucks.engine_no', 'LIKE', $request->keyword . '%')
                                            ->orWhere('trucks.color', 'LIKE', $request->keyword . '%')
                                            ->orWhere('trucks.model', 'LIKE', $request->keyword . '%')
                                            ->orWhere('users.name_en', 'LIKE', $request->keyword . '%')
                                            ->orWhere('users.name_so', 'LIKE', $request->keyword . '%');


                                });
                         }
                       
 


                         if ($request->status != '' && $request->status != null) 
                         {
                            if($request->status == 1)
                              {
                               $trucks->where('trucks.status', $request->status);
                              
                              }
                              if($request->status == 2)
                              {
                               $trucks->where('trucks.status', $request->status);
                              
                              }
                             }
   
        
                $trucks = $trucks->paginate($request->limit);
           
                $data = array(
                      'trucks' => $trucks,
                             );

                $res = Response::send(true, $data, '', 200);
          }
           return $res;
    }

    public function details(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|exists:trucks,id',
            ],
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);
        } else {

            $trucks = Truck::select('trucks.*','users.name_en as fuel_station_name_en','users.name_so as fuel_station_name_so','users.email as fuel_station_email','users.image as fuel_station_image','users.country_code_id as fuel_station_country_code_id ','users.mobile  as fuel_station_mobile','users.role_id   as fuel_station_role_id ','users.user_id  as fuel_station_user_id','users.status  as fuel_station_status','users.reg_status  as fuel_station_reg_status') 
                            ->leftjoin('users', 'users.user_id', '=', 'trucks.fuel_station_id')
                            ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                            ->with([

                                    'fuel_station','fuels'  => function ($query) {
                                        return $query->select('truck_id','fuel_types.fuel_en','capacity', 'stock', );
                                    },

                                    ])
                            ->where('users.role_id', '5')
                            ->where('trucks.id', $request->id)
                            ->where('trucks.reg_status','1')
                            ->first();


                        $data = array(
                            'trucks' => $trucks,
                                );
                        $res = Response::send(true, $data, 'Truck found', 200);
                    }

        return $res;
    }


/*UPDATE STATUS*/
public function status(Request $request)
{
    $fields    = $request->input();
    $validator = Validator::make($request->all(),
        [
            'id' => 'required|numeric|exists:trucks,id',
            'status' => 'required|numeric',
        ],
    );
    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res    = Response::send('false', $data = [], $message = $errors, $code = 422);

    } else {
        $truck = Truck::find($fields['id']);
        $truck->status = $fields['status'];
        $result = $truck->save();

       
            if ($request->status == 1) {
                $error_message = __('success2.block_truck');
            } else {
                $error_message = __('error2.block_truck');
            }
            $res    = Response::send('true', 
                           [], 
                           $message = $error_message, 
                           $code = 200);
       
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
            'id' => 'required|exists:trucks,id',

        ],
        [
            'id.exists' => __('error2.id_exists'),
        ]
          );
    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res = Response::send(false, [], $message = $errors, 422);
             } else{

        $truck   = Truck::where('id',$fields['id'])->first();


        if ($truck->reg_status == 0) 
        {
        $truck->reg_status = 1;

        $truck->approval_by        = $role_id;
        $truck->approval_user        = $user_id;

        $result = $truck->save();
        $dmessage = 'Approved';
                
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


public function PendingIndex(Request $request)
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
                       
         $trucks = Truck::select('trucks.*','users.name_en as fuel_station_name_en','users.name_so as fuel_station_name_so','users.email as fuel_station_email','users.image as fuel_station_image','users.country_code_id as fuel_station_country_code_id ','users.mobile  as fuel_station_mobile','users.role_id   as fuel_station_role_id ','users.user_id  as fuel_station_user_id','users.status  as fuel_station_status','users.reg_status  as fuel_station_reg_status') 
                        ->leftjoin('users', 'users.user_id', '=', 'trucks.fuel_station_id')
                        ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                        ->with([
                                'fuel_station' 
                                ])
                        ->where('users.role_id', '5')
                        ->where('trucks.reg_status','0')
                        ->orderBy('trucks.id', 'desc');

                     if ($request->keyword) 
                        {
                             $trucks->where(function ($query) use ($request) 
                                {
                                    $query->where('trucks.truck_no', 'LIKE', $request->keyword . '%')
                                          ->orWhere('trucks.manufacturer', 'LIKE', $request->keyword . '%')
                                          ->orWhere('trucks.chassis_no', 'LIKE', $request->keyword . '%')
                                          ->orWhere('trucks.engine_no', 'LIKE', $request->keyword . '%')
                                          ->orWhere('trucks.color', 'LIKE', $request->keyword . '%')
                                          ->orWhere('trucks.model', 'LIKE', $request->keyword . '%')
                                          ->orWhere('users.name_en', 'LIKE', $request->keyword . '%')
                                          ->orWhere('users.name_so', 'LIKE', $request->keyword . '%');

                                });
                         }
                       
 
                $trucks = $trucks->paginate($request->limit);
           
                $data = array(
                      'trucks' => $trucks,
                             );

                $res = Response::send(true, $data, '', 200);
          }
           return $res;
    }

    public function pendingDetails(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|exists:trucks,id',
            ],
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);
        } else {    
            $trucks = Truck::select('trucks.*','users.name_en as fuel_station_name_en','users.name_so as fuel_station_name_so','users.email as fuel_station_email','users.image as fuel_station_image','users.country_code_id as fuel_station_country_code_id ','users.mobile  as fuel_station_mobile','users.role_id   as fuel_station_role_id ','users.user_id  as fuel_station_user_id','users.status  as fuel_station_status','users.reg_status  as fuel_station_reg_status') 
                            ->leftjoin('users', 'users.user_id', '=', 'trucks.fuel_station_id')
                            ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                            ->with([
                                    'fuel_station' 
                                    ])
                            ->where('users.role_id', '5')
                            ->where('trucks.id', $request->id)
                            ->where('trucks.reg_status','0')
                            ->first();
                        $data = array(
                            'trucks' => $trucks,
                                );
                        $res = Response::send(true, $data, 'Truck found', 200);
                    }

        return $res;
    }


   


        }