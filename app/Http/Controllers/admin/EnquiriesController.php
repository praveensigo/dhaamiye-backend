<?php

namespace App\Http\Controllers\admin;
use App\Models\Service\ResponseSender as Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\admin\Comment;
use Validator;

class EnquiriesController extends Controller
{
    public function index(Request $request)
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

                 
    $comments = Comment::select('comments.*','issue_types.issue_en','issue_types.issue_so','users.name_en','users.name_so','users.email','users.country_code_id','users.mobile','users.image') 
                        ->leftjoin('issue_types', 'issue_types.id', '=', 'comments.issue_type_id')
                        ->leftJoin('users', function($join)
                            {
                                $join->on('comments.user_id', '=', 'users.id');
                                $join->on('comments.role_id','=','users.role_id');
                                        
                            })
                        ->where('users.reg_status',1)
                        ->orderBy('comments.id', 'desc');

                    if ($request->keyword) 
                       {
                            $comments->where(function ($query) use ($request) 
                               {
                                   $query->where('issue_types.issue_en', 'LIKE', $request->keyword . '%')
                                           ->orWhere('issue_types.issue_so', 'LIKE', $request->keyword . '%')
                                           ->orWhere('comments.comment', 'LIKE', $request->keyword . '%')
                                           ->orWhere('users.name_en', 'LIKE', $request->keyword . '%')
                                           ->orWhere('users.name_so', 'LIKE', $request->keyword . '%')
                                           ->orWhere('users.email', 'LIKE', $request->keyword . '%')
                                           ->orWhere('users.mobile', 'LIKE', $request->keyword . '%')
                                           ->orWhere('users.name_so', 'LIKE', $request->keyword . '%');
                               });
                        }
                      



                      
               $comments = $comments->paginate($request->limit);
          
               $data = array(
                     'comments' => $comments,
                            );

               $res = Response::send(true, $data, '', 200);
         }
          return $res;
   }

}
