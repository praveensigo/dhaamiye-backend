<?php

namespace App\Http\Controllers\android\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\android\customer\CustomerOrder;
use App\Models\service\ResponseSender as Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Validator;

class NotificationController extends Controller
{
   public function index(Request $request)    {

      $validator  = Validator::make($request->all(), [
         'limit' => 'required|numeric',
      ]);

      if($validator->fails())  {   
         $errors = collect($validator->errors());
         $res    = sendResponse('false', $data = [], $message = $errors, $code = 422);

      } else {
         $auth_user  = auth('sanctum')->user();
         $user_id = $auth_user->user_id;
         $created_date = $auth_user->created_at;

         $notification_date = DB::table('notifications')->select('notifications.date')
            
            ->whereIn('notifications.type',[3, 1])
            ->where(function($query) use ($user_id){
                $query->where('notifications.user_id','=',$user_id)
            ->orWhereNull('notifications.user_id');
         })
         ->where('notifications.created_at','>=',$created_date)
         ->orderBy('notifications.date','desc')
         ->distinct()->paginate($request->limit, ['notifications.date']);

         foreach($notification_date as $date)  {

            $date->notifications = DB::table('notifications')->select('notifications.id', 'title_en', 'title_so', 'description_en', 'description_so', 'notifications.date', 'notifications.time','customer_orders.created_at')
               ->join('customer_orders','customer_orders.id','=','notifications.order_id','left outer')
               ->whereIn('notifications.type',['3','1'])
               ->where(function($query) use ($id){
                  $query->where('notifications.user_id','=',$user_id)
                  ->orWhereNull('notifications.user_id');
               })
               ->where('notifications.date',$date->date)
               ->where('notifications.created_at','>',$created_date)
               ->orderBy('notifications.date','desc')
               ->orderBy('notifications.time','desc')
               ->get(); 

            foreach($date->notifications as $not)   {
               $not->datetime = $not->date.' '.$not->time;
            }  
         }
         
         $data = [
            'notifications' => $notifications,

         ];  
         $res = Response::send(true, $data, '', 200); 
      }
      return $res;
   }
}
