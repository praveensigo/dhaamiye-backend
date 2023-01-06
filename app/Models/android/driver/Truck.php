<?php

namespace App\Models\android\driver;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    use HasFactory;

    public function fuels()
    {
        return $this->belongsToMany(FuelType::class, 'truck_fuels', 'truck_id', 'fuel_type_id')
        ->withPivot('capacity', 'stock');       
    }
}
