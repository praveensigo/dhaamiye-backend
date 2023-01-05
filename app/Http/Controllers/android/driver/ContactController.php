<?php

namespace App\Http\Controllers\android\driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Validator;

class ContactController extends Controller
{
    /*
     * get all issue types
     * @params: null
     */
    public function getIssueTypes()
    {
        $issue_types =  DB::table('issue_types')
                        ->select('id', 'issue_en', 'issue_so')
                        ->where('status', 1)
                        ->get();  
        $contact =  DB::table('settings')
                        ->select('country_code_id', 'country_code', 'mobile', 'email')
                        ->join('country_codes','country_codes.id','=','settings.country_code_id') 
                        ->first();  

        $data = array(
            'contact' => $contact,
            'issue_types' => $issue_types,
        );

        return Response::send(true, $data, 'Issue types found', 200);
    }

    /*************
    Contact - Add Enquiry
    @params: issue_type_id, comment
    **************/
    public function addEnquiry(Request $request)
    {
        $auth_user = auth('sanctum')->user();
        $lang =   [
                'issue_type_id.required' => __('customer-error.issue_type_required_en'),
                'comment.required' => __('customer-error.comment_required_en'),
        ];

        if($request->lang == 2) {
            $lang =   [
                'issue_type_id.required' => __('customer-error.issue_type_required_so'),
                'comment.required' => __('customer-error.comment_required_so'),
            ];
        }
        $validator = Validator::make($request->all(), [
            'issue_type_id' => 'required|exists:issue_types,id',            
            'comment' => 'required',
        ], $lang);

        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {

            $result = DB::table('comments')->insert(array(
                'user_id' => $auth_user->user_id,
                'role_id' => 4,
                'issue_type_id' => $request->issue_type_id,
                'comment' => $request->comment,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            if($result) {           

                $message = __('customer-success.add_comment_en');

                if($request->lang  == 2) {
                    $message = __('customer-success.add_comment_so');
                }

                $res = Response::send(true, [], $message, 200);

            } else {
                $message = __('customer-error.add_comment_en');
                if($request->lang  == 2) {
                    $message = __('customer-error.add_comment_so');
                }

                $res = Response::send(false, [], $message, 400);
            }
            
        }
        return $res;
    }
}
