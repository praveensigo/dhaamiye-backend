<?php

namespace App\Models\fuelstation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Driver extends Model
{
    use HasFactory;
    public function truck()
    {
        return $this->belongsTo(Truck::class, 'truck_id', 'id');
    }
    public function fuel_station()
    {
        return $this->belongsTo(User::class, 'fuel_station_id', 'user_id')
                                    ->where('users.role_id','5')
                                    ->where('users.reg_status','1');
    }
  
    public function orders()
    {
        return $this->hasMany(CustomerOrder::class, 'driver_id', 'id');

    }
}
