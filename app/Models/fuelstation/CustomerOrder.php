<?php

namespace App\Models\fuelstation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
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
  use HasFactory;
}
