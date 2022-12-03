<?php

namespace App\Models\android\customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
    *** Relations
    **/
    public function order()
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id', 'id');
    }
}
