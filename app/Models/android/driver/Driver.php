<?php

namespace App\Models\android\driver;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    public function fuel_station()
    {
        return $this->belongsTo(User::class, 'fuel_station_id', 'user_id')
                     ->where('role_id', 5);
    }
}
