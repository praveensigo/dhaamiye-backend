<?php

namespace App\Models\android\customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    use HasFactory;

    const REQUESTED = 0;
    const PENDING = 1;
    const ACCEPTED = 2;
    const ONGOING = 3;
    const SCHEDULED = 4;
    const DELIVERED = 5;
    const CANCELLED = 6;
    const MISSED = 7;

    protected $appends = [
        'converted_created_at',
        'converted_status',
    ];

    /**
    *** Relations
    **/
    public function fuel_station()
    {
        return $this->belongsTo(FuelStation::class, 'fuel_station_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
                    // ->join('users', 'customers.id', '=', 'users.user_id')
                    // ->where('role_id', 3);
    }
    

    public function fuels() {
        return $this->hasMany(CustomerOrderFuel::class, 'order_id', 'id')
                ->join('fuel_types', 'fuel_types.id', '=', 'customer_order_fuels.fuel_type_id');
    }

    public function review() {
        return $this->hasMany(Rating::class, 'order_id', 'id');
    }

    /**
    *** Scope
    **/

    public function scopeStatus($query, $status)
    {       
        if ($status != '' && $status != null) {

            if($status == 1) {
                return $query->where('customer_orders.status', self::PENDING);

            } elseif($status == 2) {
                return $query->where('customer_orders.status', self::ACCEPTED);

            } elseif($status == 3) {
                return $query->where('customer_orders.status', self::ONGOING);

            } elseif($status == 4) {
                return $query->where('customer_orders.status', self::SCHEDULED);

            } elseif($status == 5) {
                return $query->where('customer_orders.status', self::DELIVERED);

            } elseif($status == 6) {
                return $query->where('customer_orders.status', self::CANCELLED);

            } elseif($status == 7) {
                return $query->where('customer_orders.status', self::MISSED);

            }
        }
        
        return $query;
    }

    public function scopeDescending($query)
    {
        return $query->orderBy('customer_orders.created_at', 'desc');
    }

    /**
    *** Accessors
    **/
    public function getConvertedStatusAttribute()
    {
        if($this->status == self::REQUESTED) {
            return 'Requested';

        } else if($this->status == self::PENDING) {
            return 'Pending';

        } else if($this->status == self::ACCEPTED) {
            return 'Accepted';

        } else if($this->status == self::ONGOING) {
            return 'Ongoing';

        } else if($this->status == self::SCHEDULED) {
            return 'Scheduled';

        } else if($this->status == self::DELIVERED) {
            return 'Delivered';

        }  else if($this->status == self::CANCELLED) {
            return 'Cancelled';

        }  else if($this->status == self::MISSED) {
            return 'Missed';
        } 
    }
    public function getConvertedCreatedAtAttribute()
    {
        return date('d M Y, h:i a', strtotime($this->created_at));
    }
}
