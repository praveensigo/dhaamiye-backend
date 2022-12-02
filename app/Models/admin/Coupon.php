<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
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
}
