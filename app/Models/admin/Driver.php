<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\admin\User;
use App\Models\admin\Truck;

class Driver extends Model
{
    use HasFactory;
    const ACTIVE = 1;
    const BLOCKED = 2;

    protected $appends = [
        'converted_created_at',
        'converted_status',
    ];
    public function scopeActive($query)
    {
        return $query->where('status', self::ACTIVE);
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', self::BLOCKED);
    }
    public function getConvertedStatusAttribute()
    {
        return $this->status == self::ACTIVE ? 'Active' : 'Blocked';
    }
    public function getConvertedCreatedAtAttribute()
    {
        return date('d M Y, h:i a', strtotime($this->created_at));

    }
    public function truck()
    {
        return $this->belongsTo(Truck::class, 'truck_id', 'id');
    }
    public function fuel_station()
    {
        return $this->belongsTo(FuelStation::class, 'fuel_station_id', 'id');
    }
  
    public function orders()
    {
        return $this->hasMany(CustomerOrder::class, 'driver_id', 'id');

    }
    // public function orders()
    // {
    //     return $this->belongsTo(CustomerOrder::class, 'driver_id', 'id');
    // }
   
}
