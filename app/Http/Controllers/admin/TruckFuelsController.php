<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\service\ResponseSender as Response;
use Validator;

class TruckFuelsController extends Controller
{
    public function Index(Request $request)
    {
       
        $fields = $request->input();
        $validator = Validator::make($request->all(), [
            'truck_id' => 'required|numeric|exists:trucks,id',

        ]);
        if ($validator->fails()) {
            $errors = collect($validator->errors());
            $res = Response::send(false, [], $message = $errors, 422);

        } else {
        $truck_fuels = DB::table('truck_fuels')
                         ->leftjoin('fuel_types', 'fuel_types.id', '=', 'truck_fuels.fuel_type_id')
                            ->select('truck_fuels.*','fuel_types.fuel_en','fuel_so')
                            ->where('truck_id',$fields['truck_id'])
                            ->get();
            $data = array(
                'truck_fuels' => $truck_fuels,
            );
            $res = Response::send(true, $data, 'Fuels found', 200);
        
    
      
    }
    return $res;
}

}