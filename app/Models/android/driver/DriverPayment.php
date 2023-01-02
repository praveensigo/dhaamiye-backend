<?php

namespace App\Models\android\driver;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverPayment extends Model
{
    use HasFactory;

    public function order() {
        return $this->hasOne(CustomerOrder::class, 'order_id', 'id');
    }

}
