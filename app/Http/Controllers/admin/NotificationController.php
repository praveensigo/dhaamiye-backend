<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\Notification;
use App\Models\Service\ResponseSender as Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class NotificationController extends Controller

//GET NOTIFICATION

{public function index(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'limit' => 'required|numeric',
        'keyword' => 'nullable',
        'type' => 'nullable|numeric|in:1,2,3,4,5',

    ]);
    if ($validator->fails()) {
        $errors = collect($validator->errors());
        $res = Response::send('false', $data = [], $message = $errors, $code = 422);
    } else {

        $notifications = DB::table('notifications')->select('notifications.id', 'notifications.title_en', 'notifications.title_so', 'notifications.user_id', 'users.name_en', 'users.name_so','users.email','users.mobile', 'users.country_code_id', 'country_codes.country_code', 'notifications.order_id', 'users.role_id', 'notifications.type', 'notifications.description_en', 'notifications.description_so', 'notifications.status', 'notifications.date', 'notifications.time', 'notifications.created_at')

            ->leftjoin("users", function ($join) {
                $join->on("users.id", "=", "notifications.user_id")
                    ->on("users.role_id", "=", "notifications.type");
            })
            ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')

            ->orderBy('id', 'desc');
        if ($request->type != '' && $request->type != null) {
            if ($request->type == 1) {
                $notifications->where('notifications.type', $request->type);
            }
            if ($request->type == 2) {
                $notifications->where('notifications.type', $request->type);
            }
            if ($request->type == 3) {
                $notifications->where('notifications.type', $request->type);
            }
            if ($request->type == 4) {
                $notifications->where('notifications.type', $request->type);
            }

            if ($request->type == 5) {
                $notifications->where('notifications.type', $request->type);
            }

        }

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
                'type' => 'required|in:1,2,3,4,5',
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
        
                'type.required' => 'Please select the type,1:All users,2:Sub admins,3:Customers,4:Drivers,5:Fuel stations',
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
            $notification->type = $request->type;
            $notification->user_id = $request->user_id;
            $role_id = auth('sanctum')->user()->role_id;
            $user_id = auth('sanctum')->user()->id;
            $notification->added_by = $role_id;
            $notification->added_user = $user_id;
            $notification->description_en = $request->description_en;
            $notification->description_so = $request->description_so;
            $currentTime = Carbon::now();
            $notification->date = $currentTime->toDateString();
            $notification->time = $currentTime->toTimeString();
            $result = $notification->save();

            if ($result) {
                $res = Response::send(true, [], __('admin.notification_created'), 200);
            } else {
                $res = Response::send(false, [], __('admin.notification_creation_failed'), 400);
            }
        }
        return $res;
    }

/*UPDATE NOTIFICATION*/

    public function update(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(),
            ['id' => 'required|numeric|exists:notifications,id',
                'title_en' => 'nullable|required_without:title_so|string|min:3|max:70',
                'title_so' => 'nullable||required_without:title_en|string|min:3|max:70',
                'type' => 'required|in:1,2,3,4,5',
                'user_id' => 'nullable',
                'description_en' => 'nullable|required_without:description_so|string|min:3|max:150',
                'description_so' => 'nullable|required_without:description_en|string|min:3|max:150',
                //'description' => 'nullable|starts_with_alphanumeric',

            ],
            [
                'title_en.required_without' => __('admin.title_en_required_without'),
                'title_so.required_without' => __('admin.title_so_required_without'),
                'description_en.required_without' => __('admin.description_en_required_without'),
                'description_so.required_without' => __('admin.description_so_required_without'),
                
                'type.required' => 'Please select the type,1:All users,2:Sub admins,3:Customers,4:Drivers,5:Fuel stations',
                'title_en.min' => __('admin.title_min'),
                'title_so.min' => __('admin.title_min'),
                'title_en.max' => __('admin.title_max'),
                'title_so.max' => __('admin.title_max'),
                'description_en.min' => __('admin.description_min'),
                'description_so.min' => __('admin.description_min'),
                'description_en.max' => __('admin.description_max'),
                'description_so.max' => __('admin.description_max'),
            
                //'description.starts_with_alphanumeric' => __('admin.notification_description_starts_with_alphanumeric'),
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);

        } else {
            $notification = Notification::find($fields['id']);
            $notification->title_en = $request->title_en;
            $notification->title_so = $request->title_so;
            $notification->type = $request->type;
            $notification->user_id = $request->user_id;
            $notification->description_en = $request->description_en;
            $notification->description_so = $request->description_so;
            $currentTime = Carbon::now();
            $notification->date = $currentTime->toDateString();
            $notification->time = $currentTime->toTimeString();

            $result = $notification->save();

            if ($result) {
                $res = Response::send('true',
                    [],
                    $message = __('admin.Notification_updated'),
                    $code = 200);
            } else {
                $res = Response::send('false',
                    [],
                    $message = __('admin.Notification_update_failed'),
                    $code = 400);
            }
        }

        return $res;
    }
/*UPDATE STATUS*/
    public function status(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(),
            ['id' => 'required|numeric|exists:notifications,id',
                'status' => 'required|numeric|in:1,2',
            ], [
                'status.in' => __('admin.status_in'),
                'id.exists' => __('admin.notification_not_found'),
            ]

        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);

        } else {
            $notification = Notification::find($fields['id']);
            $notification->status = $fields['status'];

            $result = $notification->save();

            if ($result) {
                if ($request->status == 1) {
                    $message = __('admin.notification_published');
                } else {
                    $message = __('admin.notification_unpublished');
                }
                $res = Response::send(true, [], $message, 200);
            } else {
                $res = Response::send(false, [], __('admin.notification_status_failed'), 400);
            }

        }
        return $res;
    }
/*DELETE NOTIFICATIONS*/

    public function delete(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|numeric|exists:notifications,id',
            ],
            [
                'id.exists' => __('admin.notification_not_found'),
            ]

        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);

        } else {
            $result = Notification::where('id', $fields['id'])->delete();

            if ($result) {
                $res = Response::send('true',
                    [],
                    $message = __('admin.notification_deleted'),
                    $code = 200);
            } else {
                $res = Response::send('false',
                    [],
                    $message = __('admin.notification_delete_failed'),
                    $code = 400);
            }
        }
        return $res;
    }
}
