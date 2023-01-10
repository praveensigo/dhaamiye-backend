<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\service\ResponseSender as Response;
use App\Models\User;
use App\Models\admin\FuelType;
use Illuminate\Support\Facades\DB;
use Validator;

class FuelTypeController extends Controller
{
/*GET LANGUAGES*/
    public function index(Request $request)
    {
    	$fields    = $request->input();
        $validator = Validator::make($request->all(), [
            'limit'   => 'required|numeric',
            'keyword' => 'nullable',
            'status' => 'nullable|numeric|in:1,2', //1:Active, 2:Blocked
        ]);
        if ($validator->fails()) 
        {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else 
        {
            $fuels = DB::table('fuel_types')->select('id','fuel_en','fuel_so', 'status', 'created_at')
                ->orderBy('id','desc');
            
            if ($fields['keyword']) {
                $fuels->where('fuel_en', 'LIKE', $fields['keyword'] . '%')
                      ->orWhere('fuel_so', 'LIKE', $fields['keyword'] . '%');

            }

            if ($fields['status'] != '' && $fields['status'] != null) {
                $fuels->where('status',$fields['status']);
            }

            $fuels = $fuels->paginate($fields['limit']);

            $data = array(
                'fuels' => $fuels,
            );

            $res    = Response::send('true', 
                               $data, 
                               $message ='Success', 
                               $code = 200);  
        }
        return $res;
    }  

/*CREATE FUELS*/
    public function add(Request $request)
    {
    	$fields    = $request->input();
        $validator = Validator::make($request->all(),
            [
                'fuel_en' => 'nullable|required_without:fuel_so|min:3|max:100',
            'fuel_so' => 'nullable|required_without:fuel_en|min:3|max:100',
                        ],
                    [
                'fuel_en.required_without' => __('error.fuel_en_required_without'),
                'fuel_so.required_without' => __('error.fuel_so_required_without'),
                'fuel_en.min' => __('error.fuel_min'),
                'fuel_en.max' => __('error.fuel_max'),
                'fuel_so.min' => __('error.fuel_min'),
                'fuel_so.max' => __('error.fuel_max'),
            // 'fuels.contains_alphabets' => 'Fuel should be contains alphabets.',
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);

        } else {
            $fuels = new FuelType;
            $fuels->fuel_en = $fields['fuel_en'];
            $fuels->fuel_so = $fields['fuel_so'];
            $result = $fuels->save();

            if ($result) {
                $res    = Response::send('true', 
                               [], 
                               $message ='Fuel created successfully.', 
                               $code = 200);
            } else {
                $res    = Response::send('false', 
                               [], 
                               $message ='Failed to create fuel.', 
                               $code = 400);
            }
        }

        return $res;
    } 

/*UPDATE FUEL*/
    public function update(Request $request)
    {
    	$fields    = $request->input();
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|numeric|exists:fuel_types,id',
                'fuel_en' => 'nullable|required_without:fuel_so|min:3|max:100',
                'fuel_so' => 'nullable|required_without:fuel_en|min:3|max:100',
                  ],
            [
                'fuel_en.required_without' => __('error.fuel_en_required_without'),
                'fuel_so.required_without' => __('error.fuel_so_required_without'),
                'fuel_en.min' => __('error.fuel_min'),
                'fuel_en.max' => __('error.fuel_max'),
                'fuel_so.min' => __('error.fuel_min'),
                'fuel_so.max' => __('error.fuel_max'),
             ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);

        } else {
            $fuels = FuelType::find($fields['id']);
            $fuels->fuel_en = $fields['fuel_en'];
            $fuels->fuel_so = $fields['fuel_so'];
            $result = $fuels->save();

            if ($result) {
                $res    = Response::send('true', 
                               [], 
                               $message ='Fuel updated successfully.', 
                               $code = 200);
            } else {
                $res    = Response::send('false', 
                               [], 
                               $message ='Failed to update fuel.', 
                               $code = 400);
            }
        }

        return $res;
    }  

/*UPDATE STATUS*/
    public function status(Request $request)
    {
    	$fields    = $request->input();
        $validator = Validator::make($request->all(),
        [
            'id' => 'required|numeric|exists:fuel_types,id',
            'status' => 'required|numeric|in:1,2',
        ],
        [
            'status.in' => __('error.status_in'),
            'id.exists' => __('error.id_exists'),
        ]

        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res    = Response::send('false', $data = [], $message = $errors, $code = 422);

        } else {
            $fuel = FuelType::find($fields['id']);
            $fuel->status = $fields['status'];
            $result = $fuel->save();

            if ($result) {
            	if ($request->status == 1) {
                    $error_message = 'Fuel published successfully.';
                } else {
                    $error_message = 'Fuel unpublished successfully.';
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
}
