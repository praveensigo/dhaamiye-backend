<?php

namespace App\Models\service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseSender extends Model
{
    

        public static function send($status, $data = [], $message = null, $code = null)
        {
            $code = is_null($code) ? ($status ? 200 : 400) : $code;
            $message = is_null($message) ? ($status ? 'Success' : 'Something went wrong, please try again later or contact administrator') : $message;
    
            $response = array(
                'status' => $status,
                'data' => $data,
                'message' => $message,
            );
    
            return response()->json($response, $code);
        }
    }