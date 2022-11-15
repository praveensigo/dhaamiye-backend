<?php

namespace App\Models\android\customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const PENDING = 0;
    const ACCEPTED = 1;
    const ONGOING = 2;
    const SCHEDULED = 3;
    const DELIVERED = 4;
    const CANCELLED = 5;
    const MISSED = 6;


    public function fuel_station()
    {
        return $this->belongsTo(FuelStation::class, 'fuel_station_id', 'id');
    }
}
