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
    public function fuels()
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
    public function drivers()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function trucks()
    {
        return $this->belongsTo(Truck::class, 'truck_id', 'id');
    }

    public function fuel_stations()
    {
        return $this->belongsTo(FuelStation::class, 'fuel_station_id', 'id');
    }
    public function address()
    {
        return $this->hasOne(CustomerOrderAddress::class, 'id', 'order_id');
    }
 


}
