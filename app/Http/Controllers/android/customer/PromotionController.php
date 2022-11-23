<?php

namespace App\Http\Controllers\android\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Validator;

class PromotionController extends Controller
{
    public function index($request) {
        $promotions = DB::table('coupons')
                    ->select('*')
                    ->where('status', 1)
                    ->get();

        $data = array(
                'promotions' => $promotions,
        );

        $res = Response::send(true, $data, 'Data found', 200);
    }
}
