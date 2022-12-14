<?php

namespace App\Models\fuelstation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    public function fuels()
    {
        return $this->belongsToMany(FuelType::class, 'truck_fuels', 'truck_id','fuel_type_id');
   
    }
    public function driver()
   {
       return $this->hasOne(Driver::class, 'truck_id', 'id');
   } use HasFactory;
}
