<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelStation extends Model
{
    use HasFactory, SoftDeletes;
    // Status constant
 const ACTIVE = 1;
 const BLOCKED = 2;

 protected $appends = [
 'converted_created_at',
     'converted_status',
 ];
 public function scopeActive($query)
 {
     return $query->where('fuel_stations.status', self::ACTIVE);
 }

 public function scopeBlocked($query)
 {
     return $query->where('fuel_stations.status', self::BLOCKED);
 }
/**
  * * Accessors
  */
 public function getConvertedStatusAttribute()
 {
     return $this->status == self::ACTIVE ? 'Active' : 'Blocked';
 }
 public function getConvertedCreatedAtAttribute()
 {
     return date('d M Y, h:i a', strtotime($this->created_at));

 }
}
