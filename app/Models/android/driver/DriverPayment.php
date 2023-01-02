<?php

namespace App\Models\android\driver;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverPayment extends Model
{
    use HasFactory;

    protected $appends = [
        'converted_created_at',
        'converted_payment_type',
    ];

    public function order() {
        return $this->hasOne(CustomerOrder::class, 'id', 'order_id');
    }

    /**
    *** Accessors
    **/
    public function getConvertedCreatedAtAttribute()
    {
        return date('d M Y, h:i a', strtotime($this->created_at));
    }
    public function getConvertedPaymentTypeAttribute() {
        if($this->payment_type == 1) {
            return 'Mobile Payment';

        } else if($this->payment_type == 2) {
            return 'Cash Payment';

        } else {
            return '';
        }
    }

}
