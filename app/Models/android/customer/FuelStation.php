<?php

namespace App\Models\android\customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelStation extends Model
{
    use HasFactory;

    const ACTIVE = 1;
    const BLOCKED = 2;

    public function fuels()
    {
        return $this->belongsToMany(FuelType::class, 'fuel_station_stocks', 'fuel_station_id', 'fuel_type_id');
    }


    /**
     * * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('fuel_stations.status', self::ACTIVE);
    }

    public function scopeBlocked($query)
    {
        return $query->where('fuel_stations.status', self::BLOCKED);
    }

    public function scopeDescending($query)
    {
        return $query->orderBy('fuel_stations.created_at', 'desc');
    }
}
