<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CoponController extends Controller
{
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
                'coupon_name.required' => __('error.coupon_name_required'),
                'coupon_name.min' => __('error.coupon_name_min'),
                'coupon_name.max' => __('error.coupon_name_max'),
                'coupon_name.unique' => __('error.coupon_name_unique'),

                'type.required' => __('error.type_required'),
                'type.in' => __('error.type_in'),

                'coupon_code.required' => __('error.coupon_code_required'),
                'coupon_code.min' => __('error.coupon_code_min'),
                'coupon_code.max' => __('error.coupon_code_max'),
                'coupon_code.unique' => __('error.coupon_code_unique'),

                'count.required' => __('error.count_required'),
                'count.min' => __('error.count_min'),
                'count.integer' => __('error.count_integer'),

                'expiry_date.required' => __('error.expiry_date_required'),
                'expiry_date.date' => __('error.expiry_date_date'),
                'expiry_date.date_format' => __('error.expiry_date_date_format'),
                'expiry_date.after' => __('error.expiry_date_after'),

                'amount.required_if' => __('error.amount_required'),
                'percentage.required_if' => __('error.percentage_required'),

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

         $res = Response::send('true', [],  __('success.create_coupons'), 
                $code = 200);
        } 
            
    else 
        {
            $res    = Response::send('false', [], 
            __('error.create_coupons'), 
                      $code = 400);
        }
        }
return $res;
}
}
