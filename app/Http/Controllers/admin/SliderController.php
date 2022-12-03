<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\Slider;
use App\Models\service\ResponseSender as Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class SliderController extends Controller
{
/*GET SLIDERS*/
    public function index(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric',
            'status' => 'nullable|numeric|in:1,2', //1:Active, 2:Blocked
        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);
        } else {
            $sliders = DB::table('sliders')->select('id', 'image', 'status', 'created_at')
                ->orderBy('id', 'desc');

            if ($fields['status'] != '' && $fields['status'] != null) {
                $sliders->where('status', $fields['status']);
            }
            $sliders = $sliders->paginate($fields['limit']);

            $data = array(
                'sliders' => $sliders,
            );

            $res = Response::send('true',
                $data,
                $message = 'Success',
                $code = 200);
        }
        return $res;
    }

/*ADD SLIDERS*/
    public function add(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(),
            [
                'image' => 'required|max:4096|mimes:png,jpg,jpeg,gif',
            ],
            [
                'image.mimes' => 'Image should be in jpg,jpeg,gif or png format.',
                'image.max' => 'Please upload an image with size less than 4MB.',
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);

        } else {
            $slider = new Slider;
            if ($request->file('image') != null) {
                $image = $request->file('image');
                $uploadFolder = 'admin/sliders';
                $image_uploaded_path = $image->store($uploadFolder, 'public');
                $slider->image = $image_uploaded_path;
            } else {
                $slider->image = '';
            }
            $result = $slider->save();

            if ($result) {
                $res = Response::send('true',
                    [],
                    $message = 'Slider image added successfully.',
                    $code = 200);
            } else {
                $res = Response::send('false',
                    [],
                    $message = 'Failed to add slider image.',
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
            [
                'id' => 'required|numeric|exists:sliders,id',
                'status' => 'required|numeric|in:1,2',
            ],
            [
                'status.in' => __('error.status_in'),
                'id.exists' => __('error.id_exists'),
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);

        } else {
            $slider = Slider::find($fields['id']);
            $slider->status = $fields['status'];
            $result = $slider->save();

            if ($result) {
                if ($request->status == 1) {
                    $error_message = 'Slider image published successfully.';
                } else {
                    $error_message = 'Slider image unpublished successfully.';
                }
                $res = Response::send('true',
                    [],
                    $message = $error_message,
                    $code = 200);
            } else {
                $res = Response::send('false',
                    [],
                    $message = $error_message,
                    $code = 400);
            }
        }
        return $res;
    }

/*DELETE SLIDER*/
    public function delete(Request $request)
    {
        $fields = $request->input();
        $validator = Validator::make($request->all(),
            [
                'id' => 'required|numeric|exists:sliders,id',
            ],
            [
                'id.exists' => __('error.id_exists'),
            ]
        );
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send('false', $data = [], $message = $errors, $code = 422);

        } else {
            $result = Slider::where('id', $fields['id'])->delete();

            if ($result) {
                $res = Response::send('true',
                    [],
                    $message = 'Slider image delete successfully.',
                    $code = 200);
            } else {
                $res = Response::send('false',
                    [],
                    $message = 'Failed to delete slider image.',
                    $code = 400);
            }
        }
        return $res;
    }
}
