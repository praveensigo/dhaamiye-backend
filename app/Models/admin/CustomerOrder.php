<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    use HasFactory;
      public function orders()
    {
        return $this->belongsTo(CustomerOrder::class, 'id', 'driver_id');
    }
}
