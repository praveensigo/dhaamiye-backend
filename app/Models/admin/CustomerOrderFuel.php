<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrderFuel extends Model
{
    use HasFactory;
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'user_id')
                                    ->where('users.role_id','3')
                                    ->where('users.reg_status','1');
    }
    public function fuel()
    {
        return $this->belongsTo(FuelType::class, 'fuel_type_id', 'id');
    }
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'user_id')
                                    ->where('users.role_id','4')
                                    ->where('users.reg_status','1');
    }
}
