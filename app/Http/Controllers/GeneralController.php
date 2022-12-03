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
                        ->select('id', 'country_code')
                        ->get();   

        $data = array(
            'country_codes' => $country_codes,
        );

        return Response::send(true, $data, 'Country codes found', 200);
    }

    /*
     * get privacy policy
     * @params: null
     */
    public function getPrivacyPolicy() {
        $privacy_policy   = DB::table('privacy_policy')->select('title_en', 'title_so', 'policy_en', 'policy_so')->first();

        $data = array (
            'privacy_policy' => $privacy_policy
        );
       
        return Response::send(true, $data, 'Privacy policy found', 200);
    }

    /*
     * get terms and conditions
     * @params: null
     */
    public function getTermsandConditions() {
        $terms   = DB::table('terms')->select('title_en', 'title_so', 'term_en', 'term_so')->first();

        $data = array (
            'terms' => $terms
        );
       
        return Response::send(true, $data, 'Terms and conditions found', 200);
    }

    /*
     * get about
     * @params: null
     */
    public function getAbout() {
        $about   = DB::table('terms')->select('title_en', 'title_so', 'content_en', 'content_so')->first();

        $data = array (
            'about' => $about
        );
       
        return Response::send(true, $data, 'About found', 200);
    }
}
