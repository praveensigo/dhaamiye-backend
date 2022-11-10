<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelStationStock extends Model
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
    // public function fuel_station()
    // {
    //     return $this->belongsTo(FuelStation::class, 'fuel_station_id', 'id');
    // }
    // public function fuel_station()
    // {
    //     return $this->belongsTo(User::class, 'user_id', 'id')
    //                 ->leftjoin('country_codes', 'country_codes.id', '=', 'users.country_code_id')
    //                 ->where('users.role_id', '5')
    //                 ->where('users.reg_status', '1');
    // }
    public function fuel()
    {
        return $this->belongsTo(FuelType::class, 'fuel_type_id', 'id');
    }
}
