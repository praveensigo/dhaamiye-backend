<?php

namespace App\Models\android\customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerFavoriteStation extends Model
{
    use HasFactory;

    public function fuel_station()
    {
        return $this->belongsTo(FuelStation::class, 'fuel_station_id', 'id');
    }


    public function scopeDescending($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
