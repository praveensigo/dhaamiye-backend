<?php

namespace App\Http\Controllers\fuelstation;

use App\Http\Controllers\Controller;
use App\Models\fuelstation\Notification;
use App\Models\service\ResponseSender as Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class NotificationController extends Controller

//GET RECIEVED NOTIFICATION

{
    public function receivedIndex(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
        ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $user = auth('sanctum')->user();
            $id = $user->user_id;
            $created_date = $user->created_at;

            $notification_date = DB::table('notifications')->select('notifications.date')
                ->leftjoin('customer_orders', 'customer_orders.id', '=', 'notifications.order_id', 'left outer')
                ->whereIn('notifications.type', ['5', '1'])
                ->where(function ($query) use ($id) {
                    $query->where('notifications.user_id', '=', $id)
                        ->orWhereNull('notifications.user_id');
                })
                ->where('notifications.created_at', '>=', $created_date)
                ->orderBy('notifications.date', 'desc')
                ->distinct()->paginate($fields['limit'], ['notifications.date']);
            foreach ($notification_date as $date) {
                $date->notifications = DB::table('notifications')->select('notifications.id','notifications.type','notifications.created_at as notification_created_at', 'title_en', 'title_en', 'description_en', 'description_so', 'notifications.date', 'notifications.time', 'customer_orders.created_at as customer_order_created_at')
                    ->leftjoin('customer_orders', 'customer_orders.id', '=', 'notifications.order_id', 'left outer')
                    ->whereIn('notifications.type', ['5', '1'])
                    ->where(function ($query) use ($id) {
                        $query->where('notifications.user_id', '=', $id)
                            ->orWhereNull('notifications.user_id');
                    })
                    ->where('notifications.date', $date->date)
                    ->where('notifications.created_at', '>', $created_date)
                    ->orderBy('notifications.date', 'desc')
                    ->orderBy('notifications.time', 'desc')
                    ->get();
                foreach ($date->notifications as $not) {
                    $not->datetime = $not->date . ' ' . $not->time;
                }
            }
            $res = Response::send('true',
                $data = [
                    'notification_date' => $notification_date,

                ],
                $message = 'Success',
                $code = 200);
        }
        return $res;
    }
//GET SEND NOTIFICATION

    public function sendIndex(Request $request)
    {$user = auth('sanctum')->user();
        $user_id = $user->user_id;

        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'keyword' => 'nullable',

        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {

            $notifications = DB::table('notifications')->select('notifications.id', 'notifications.title_en', 'notifications.title_so', 'notifications.user_id', 'users.name_en', 'users.name_so', 'users.email', 'users.mobile', 'users.country_code_id', 'country_codes.country_code', 'notifications.order_id', 'users.role_id', 'notifications.type', 'notifications.description_en', 'notifications.description_so', 'notifications.status', 'notifications.date', 'notifications.time', 'notifications.created_at')
                ->leftjoin("users", function ($join) {
                    $join->on("users.user_id", "=", "notifications.user_id")
                        ->on("users.role_id", "=", "notifications.type");
                })
                ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
                ->where('notifications.type', 4)

                ->where('notifications.added_by', 5)
                ->where('notifications.added_user', $user_id)

                ->orderBy('notifications.id', 'desc');

            // SEARCH BY KEYWORD
            if ($request->keyword) {
                $notifications->where(function ($query) use ($request) {
                    $query->where('name_en', 'LIKE', '%' . $request->keyword . '%')
                        ->where('name_so', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('mobile', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('title_en', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('title_so', 'LIKE', '%' . $request->keyword . '%')

                    ;});
            }

            // PAGINATE
            $notifications = $notifications->paginate($request->limit);

            $data = array(
                'notifications' => $notifications,
            );

            $res = Response::send(true, $data, '', 200);
        }
        return $res;
    }

    //ADD NOTIFICATION
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(),

            ['title_en' => 'nullable|required_without:title_so|string|min:3|max:70',
                'title_so' => 'nullable||required_without:title_en|string|min:3|max:70',
                'user_id' => 'nullable',
                'description_en' => 'nullable|required_without:description_so|string|min:3|max:150',
                'description_so' => 'nullable|required_without:description_en|string|min:3|max:150',
                //'description' => 'nullable|starts_with_alphanumeric',

            ],
            ['title_en.required_without' => __('admin.title_en_required_without'),
                'title_so.required_without' => __('admin.title_so_required_without'),
                'description_en.required_without' => __('admin.description_en_required_without'),
                'description_so.required_without' => __('admin.description_so_required_without'),
                'title_en.min' => __('admin.title_min'),
                'title_so.min' => __('admin.title_min'),
                'title_en.max' => __('admin.title_max'),
                'title_so.max' => __('admin.title_max'),
                'description_en.min' => __('admin.description_min'),
                'description_so.min' => __('admin.description_min'),
                'description_en.max' => __('admin.description_max'),
                'description_so.max' => __('admin.description_max'),
                'description.required' => 'Please enter the description',

                //'description.starts_with_alphanumeric' => __('admin.notification_description_starts_with_alphanumeric'),
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $notification = new Notification;
            $notification->title_en = $request->title_en;
            $notification->title_so = $request->title_so;
            $notification->type = 4;
            $notification->user_id = $request->user_id;
            $role_id = auth('sanctum')->user()->role_id;
            $user_id = auth('sanctum')->user()->user_id;
            $notification->added_by = $role_id;
            $notification->added_user = $user_id;
            $notification->description_en = $request->description_en;
            $notification->description_so = $request->description_so;
            $currentTime = Carbon::now();
            $notification->date = $currentTime->toDateString();
            $notification->time = $currentTime->toTimeString();
            $result = $notification->save();
            if ($result) {
                $title = $notification->title;
                $content = $notification->description;
                if (($notification->type == 1)) 
                    {
                       $fcms = DB::table('users')
                        ->where('users.role_id', 4)
                        ->select('fcm')
                                    ->get();

                    foreach ($fcms as $fcm) 
                    {
                        if ($fcm->fcm) 
                            {

                                $this->sendDriverNotification($title, $content, $fcm->fcm);
                                    
                            }
                    }
                } else {
                    if ($notification->user_id) {
                         
                        $fcm = DB::table('users')
                        ->where('users.role_id', 4)
                        ->select('fcm')
                        ->where('user_id', $notification->user_id)->first();
                                $this->sendDriverNotification($title, $content, $fcm->fcm);
                            
                            }

                     else {

                        $fcms = DB::table('users')->select('fcm')
                        ->where('users.role_id', 4)
                        ->where('users.role_id', $notification->type)
                        ->get();
                            foreach ($fcms as $fcm) 
                            {
                            if ($fcm->fcm) 
                            {
                                    $this->sendDriverNotification($title, $content, $fcm->fcm);
                                    }
                            }}
                    }
                
                $res = Response::send(true, [], __('admin.notification_created'), 200);
            } else {
                $res = Response::send(false, [], __('admin.notification_creationfailed'), 400);
            }
        }
        return $res;
    }
    //SEND DRIVER NOTIFICATION
    
    public function sendDriverNotification($title, $body, $fcm)
    {

        $SERVER_API_KEY = "";

        $data = [
            "registration_ids" => array($fcm),
            "notification" => [
                "title" => $title,
                "body" => $body,
            ],
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        $err = curl_error($ch);

        curl_close($ch);

        return true;
    }

} 

           