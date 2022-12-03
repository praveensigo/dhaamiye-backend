<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\service\ResponseSender as Response;
use Validator;

class GeneralController extends Controller
{
    /*
     * get all country codes
     * @params: null
     */
    public function getCountryCodes()
    {
        $country_codes =  DB::table('country_codes')
                        ->select('country_code')
                        ->get();   

        $data = array(
            'country_codes' => $country_codes,
        );

        return Response::send(true, $data, 'Country codes found', 200);
    }
}
