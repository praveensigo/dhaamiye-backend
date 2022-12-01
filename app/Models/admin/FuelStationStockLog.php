<?php

namespace App\Models\admin;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelStationStockLog extends Model
{
    public function fuel_station()
    {
        return $this->belongsTo(FuelStation::class, 'fuel_station_id', 'id');
    }
    public function fuel_type()
    {
        return $this->belongsTo(FuelType::class, 'fuel_type_id', 'id');
    }
    use HasFactory; use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class, 'fuel_station_id', 'user_id')
                    ->leftjoin('country_codes','country_codes.id', '=',  'users.country_code_id')
                    ->where('users.role_id',5)
                    ;
   

    }
   
}
