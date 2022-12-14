<?php

namespace App\Models\fuelstation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelStationPaymentLog extends Model
{
    use HasFactory;
    protected $appends = [
        'converted_created_at',
       
    ];

    public function scopeBlocked($query)
    {
        return $query->where('status', self::BLOCKED);
    }
   
    public function getConvertedCreatedAtAttribute()
    {
        return date('d M Y, h:i a', strtotime($this->created_at));

    }
}
