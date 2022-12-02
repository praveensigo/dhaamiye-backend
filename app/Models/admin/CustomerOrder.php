<?php

namespace App\Models\admin;

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
        }}
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
      public function orders()
    {
        return $this->belongsTo(CustomerOrder::class, 'id', 'driver_id');
    }
    public function fuel()
    {
        return $this->belongsToMany(FuelType::class, 'customer_order_fuels', 'order_id','fuel_type_id');
    }
    // public function fuel_station()
    // {
    //     return $this->belongsTo(FuelStation::class, 'fuel_station_id', 'id');
    // }
    public function fuel_station()
    {
        return $this->belongsTo(User::class, 'fuel_station_id', 'user_id')
                                    ->where('users.role_id','5')
                                    ->where('users.reg_status','1');
    }

    public function drivers()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function trucks()
    {
        return $this->belongsTo(Truck::class, 'truck_id', 'id');
    }

    public function fuel_stations()
    {
        return $this->belongsTo(FuelStation::class, 'fuel_station_id', 'id');
    }
    public function address()
    {
        return $this->hasOne(CustomerOrderAddress::class, 'id', 'order_id');
    }
 



    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'user_id')
                                    ->where('users.role_id','3')
                                    ->where('users.reg_status','1');
    }
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'user_id')
                                    ->where('users.role_id','4')
                                    ->where('users.reg_status','1');
    }
   public function fuels() {
        return $this->hasMany(CustomerOrderFuel::class, 'order_id', 'id')
                ->join('fuel_types', 'fuel_types.id', '=', 'customer_order_fuels.fuel_type_id');
    }
}
