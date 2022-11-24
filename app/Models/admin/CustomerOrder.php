<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    use HasFactory;
     
    protected $appends = [
        'converted_created_at',

    ];
  
    public function getConvertedCreatedAtAttribute()
    {
        return date('d M Y, h:i a', strtotime($this->created_at));

    }
      public function orders()
    {
        return $this->belongsTo(CustomerOrder::class, 'id', 'driver_id');
    }
    public function fuel()
    {
        return $this->belongsToMany(FuelType::class, 'customer_order_fuels', 'order_id','fuel_type_id');
    }
    // public function fuel_station()
    // {
    //     return $this->belongsTo(FuelStation::class, 'fuel_station_id', 'id');
    // }
    public function fuel_station()
    {
        return $this->belongsTo(User::class, 'fuel_station_id', 'user_id')
                                    ->where('users.role_id','5')
                                    ->where('users.reg_status','1');
    }
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'user_id')
                                    ->where('users.role_id','3')
                                    ->where('users.reg_status','1');
    }
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'user_id')
                                    ->where('users.role_id','4')
                                    ->where('users.reg_status','1');
    }
}
