<?php

namespace App\Http\Controllers\android\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Validator;

class PromotionController extends Controller
{
    public function index() {

        $promotions = DB::table('coupons')
                    ->select('coupons.*')
                    ->where('status', 1)
                    ->where('expiry_date', '>=', date('Y-m-d'))
                    ->whereRaw('used_count < count')
                    ->get();

        $data = array(
                'promotions' => $promotions,
        );

        $res = Response::send(true, $data, 'Data found', 200);

        return $res;
    }
}
