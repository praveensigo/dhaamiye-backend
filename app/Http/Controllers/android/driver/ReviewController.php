<?php

namespace App\Http\Controllers\android\driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\android\driver\CustomerOrder;
use App\Models\android\driver\Rating;
use Illuminate\Support\Facades\DB;
use App\Models\service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Validator;

class ReviewController extends Controller
{
    /*************
    Add rating and review
    @params: order_id, customer_id, rating, review
    **************/
    public function add(Request $request)
    {
        $auth_user = auth('sanctum')->user();
        $lang =   [
                'rating.required' => __('customer-error.rating_required_en'),
                'review.required' => __('customer-error.review_required_en'),
        ];

        if($request->lang == 2) {
            $lang =   [
                'rating.required' => __('customer-error.rating_required_so'),
                'review.required' => __('customer-error.review_required_so'),
            ];
        }
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'order_id' => 'required|exists:customer_orders,id',
            'rating' => 'required|numeric|in:1,2,3,4,5',
            'review' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
            $order = CustomerOrder::find($request->order_id);

            $user = DB::table('users')
                    ->select('id', 'user_id')
                    ->where('user_id', $request->customer_id)
                    ->where('role_id', 3)
                    ->first();

            if($order->status == 5) {
                $rating = new Rating;
                
                $rating->order_id = $request->order_id;
                $rating->user_id = $user->id;
                $rating->role_id = 3;
                $rating->star_rating = $request->rating;
                $rating->review = $request->review;                
                $rating->created_at = date('Y-m-d H:i:s');
                $rating->updated_at = date('Y-m-d H:i:s');
                
                if($rating->save()) {

                    $message = __('customer-success.add_review_en');

                    if($request->lang  == 2) {
                        $message = __('customer-success.add_review_so');
                    }

                    $res = Response::send(true, [], $message, 200);

                } else {
                    $message = __('customer-error.add_review_en');
                    if($request->lang  == 2) {
                        $message = __('customer-error.add_review_so');
                    }

                    $res = Response::send(false, [], $message, 400);
                }
            } else {
                $message = __('customer-error.add_review_status_en');
                if($request->lang  == 2) {
                    $message = __('customer-error.add_review_status_so');
                }

                $res = Response::send(false, [], $message, 400);
            }
        }
        return $res;
    }
}
