<?php

namespace App\Models\android\customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
   use HasFactory, SoftDeletes;

   const ACTIVE = 1;
   const BLOCKED = 2;

   protected $appends = [
      'converted_created_at',
      'converted_status',
   ];
   

   /**
   * * Accessors
   */
   public function getConvertedStatusAttribute() {
      return $this->status == self::ACTIVE ? 'Active' : 'Blocked';
   }

   public function getConvertedCreatedAtAttribute() {
      return date('d M Y, h:i a', strtotime($this->created_at));
   }
}
