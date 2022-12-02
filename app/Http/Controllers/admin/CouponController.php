<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service\ResponseSender as Response;
use App\Models\admin\Coupon;
use Illuminate\Validation\Rule;
use Validator;

class CouponController extends Controller
{   
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',
            'status' => 'nullable|numeric',

        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $coupons = Coupon::select('id', 'coupon_name','type', 'coupon_code', 'amount', 'count','used_count','expiry_date', 'status', 'created_at')
            ->orderBy('id','desc');
            // SEARCH
            if ($request->keyword) {
                $coupons->where(function ($query) use ($request) {
                    $query->where('coupon_name', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('coupon_code', 'LIKE', '%' . $request->keyword . '%');

                });
            }
            if ($request->status != '' && $request->status != null) 
            {
               if($request->status == 1)
                 {
                  $coupons->where('coupons.status', $request->status);
                 }
                 if($request->status == 2)
                 {
                  $coupons->where('coupons.status', $request->status);
                 }
                }

          
            // PAGINATE
            $coupons = $coupons->paginate($request->limit);

            $data = array(
                'coupons' => $coupons,
            );

            $res = Response::send(true, $data, '', 200);
        }
        return $res;
    }

    public function add(Request $request)
    {
        $fields    = $request->input();
        $validator = Validator::make($request->all(),
            [
                'coupon_name' => 'required|min:3|max:15|unique:coupons,coupon_name',
                'type' =>'required|numeric|in:1,2,3',
                'coupon_code' => 'required|min:6|max:15|unique:coupons,coupon_code',
                'count' => 'required|integer|min:1',
                'expiry_date' => 'required|date|date_format:Y-m-d|after:today',
                'amount' => 'nullable|required_if:type,1,3',
                'percentage' => 'nullable|required_if:type,2',

            ],
            [
                'coupon_name.required' => __('error2.coupon_name_required'),
                'coupon_name.min' => __('error2.coupon_name_min'),
                'coupon_name.max' => __('error2.coupon_name_max'),
                'coupon_name.unique' => __('error2.coupon_name_unique'),

                'type.required' => __('error2.type_required'),
                'type.in' => __('error2.type_in'),

                'coupon_code.required' => __('error2.coupon_code_required'),
                'coupon_code.min' => __('error2.coupon_code_min'),
                'coupon_code.max' => __('error2.coupon_code_max'),
                'coupon_code.unique' => __('error2.coupon_code_unique'),

                'count.required' => __('error2.count_required'),
                'count.min' => __('error2.count_min'),
                'count.integer' => __('error2.count_integer'),

                'expiry_date.required' => __('error2.expiry_date_required'),
                'expiry_date.date' => __('error2.expiry_date_date'),
                'expiry_date.date_format' => __('error2.expiry_date_date_format'),
                'expiry_date.after' => __('error2.expiry_date_after'),

                'amount.required_if' => __('error2.amount_required'),
                'percentage.required_if' => __('error2.percentage_required'),

            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);
        }else
        {
            $coupons = new Coupon;
            $coupons->coupon_name = $fields['coupon_name'];
            $coupons->type = $fields['type'];
            $coupons->coupon_code = $fields['coupon_code'];
            $coupons->count = $fields['count'];
            $coupons->expiry_date = $fields['expiry_date'];
            if(($fields['type'])==1 )
            {
                $coupons->amount = $fields['amount'];

            }if(($fields['type'])==2 )
            {
            $coupons->amount = $fields['percentage'];
            }
            if(($fields['type'])==3 )
            {
            $coupons->amount = $fields['amount'];
            }
    $result = $coupons->save();
    if ($result) 
        {

         $res = Response::send('true', [],  __('success2.create_coupons'), 
                $code = 200);
        } 
            
    else 
        {
            $res    = Response::send('false', [], 
            __('error2.create_coupons'), 
                      $code = 400);
        }
        }
    return $res;
    }
    public function update(Request $request)
    {
        $fields    = $request->input();

        $validator = Validator::make($request->all(),
            [
                'id' => 'required|numeric|exists:coupons,id',

                    'coupon_name' => ['required','min:3','max:15',            
                                Rule::unique('coupons', 'coupon_name')->ignore($fields['id'], 'id'),
                ],
                    'type' =>'required|numeric|in:1,2,3',
                    'coupon_code' =>  ['required','min:3','max:15',
                        Rule::unique('coupons', 'coupon_code')->ignore($fields['id'], 'id'),],

                    'count' => 'required|integer|min:1',
                    'expiry_date' => 'required|date|date_format:Y-m-d|after:today',
                    'amount' => 'nullable|required_if:type,1,3',
                    'percentage' => 'nullable|required_if:type,2',
                    
    
                ],
                [ 
                'coupon_name.required' => __('error2.coupon_name_required'),
                'coupon_name.min' => __('error2.coupon_name_min'),
                'coupon_name.max' => __('error2.coupon_name_max'),
                'coupon_name.unique' => __('error2.coupon_name_unique'),

                'type.required' => __('error2.type_required'),
                'type.in' => __('error2.type_in'),

                'coupon_code.required' => __('error2.coupon_code_required'),
                'coupon_code.min' => __('error2.coupon_code_min'),
                'coupon_code.max' => __('error2.coupon_code_max'),
                'coupon_code.unique' => __('error2.coupon_code_unique'),

                'count.required' => __('error2.count_required'),
                'count.min' => __('error2.count_min'),
                'count.integer' => __('error2.count_integer'),

                'expiry_date.required' => __('error2.expiry_date_required'),
                'expiry_date.date' => __('error2.expiry_date_date'),
                'expiry_date.date_format' => __('error2.expiry_date_date_format'),
                'expiry_date.after' => __('error2.expiry_date_after'),

                'amount.required_if' => __('error2.amount_required'),
                'percentage.required_if' => __('error2.percentage_required'),
                ]
            );
            if ($validator->fails()) {
                $errors = collect($validator->errors());
                $res = Response::send(false, [], $message = $errors, 422);
            }else
            { 
                $coupons = Coupon::find($fields['id']);
                $coupons->coupon_name = $fields['coupon_name'];
                $coupons->type = $fields['type'];
                $coupons->coupon_code = $fields['coupon_code'];
                $coupons->count = $fields['count'];
                $coupons->expiry_date = $fields['expiry_date'];
                if(($fields['type'])==1 )
                {
                    $coupons->amount = $fields['amount'];
    
                }if(($fields['type'])==2 )
                {
                $coupons->amount = $fields['percentage'];
                }
                if(($fields['type'])==3 )
                {
                $coupons->amount = $fields['amount'];
                }
        $result = $coupons->save();
        if ($result) 
            {
    
             $res = Response::send('true', [],  __('success2.update_coupons'), 
                    $code = 200);
            } 
                
        else 
            {
                $res    = Response::send('false', [], 
                __('error2.update_coupons'), 
                          $code = 400);
            }
            }
    return $res;

}
public function status(Request $request)
{
    $validator = Validator::make($request->all(),
        [
            'id' => 'required|numeric|exists:coupons,id',
            'status' => 'numeric|in:1,2',
        ],
        [
            'status.in' => __('error2.status_in'),
            'id.exists' => __('error2.id_exists'),
        ]
    );
    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res = Response::send(false, [], $message = $errors, 422);

    } else {
        $coupon = Coupon::find($request->id);
        $coupon->status = $request->status;
        $result = $coupon->save();

        if ($result) {
            if ($request->status == 1) {
                $message = __('success2.publish_coupon');
            } else {
                $message = __('success2.unpublish_coupon');
            }
            $res = Response::send(true, [], $message, 200);

        } else {
            if ($request->status == 1) {
                $message = __('error2.publish_coupon');
            } else {
                $message = __('error2.unpublish_coupon');
            }
            $res =Response::send(false, [], $message, 400);
        }
    }

    return $res;
}
}
