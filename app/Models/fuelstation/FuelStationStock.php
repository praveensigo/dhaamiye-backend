<?php

namespace App\Models\fuelstation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelStationStock extends Model
{
    use HasFactory;
    public function fuel_station()
    {
        return $this->belongsTo(FuelStation::class, 'fuel_station_id', 'id');
    }
    public function fuel_type()
    {
        return $this->belongsTo(FuelType::class, 'fuel_type_id', 'id');
    }
    
}
