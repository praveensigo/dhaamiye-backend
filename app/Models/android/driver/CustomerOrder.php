<?php

namespace App\Models\android\driver;

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
        'converted_payment_type',
        'distance',
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
        return $this->belongsTo(User::class, 'customer_id', 'user_id')
                     ->where('role_id', 3);
    }
    

    public function fuels() {
        return $this->hasMany(CustomerOrderFuel::class, 'order_id', 'id')
                ->join('fuel_types', 'fuel_types.id', '=', 'customer_order_fuels.fuel_type_id');
    }

    public function meter_readings() {
        return $this->hasMany(MeterImage::class, 'order_id', 'id');
    }

    public function review() {
        return $this->hasMany(Rating::class, 'order_id', 'id')
                ->where('role_id', 4);
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
    public function getConvertedPaymentTypeAttribute() {
        if($this->payment_type == 1) {
            return 'Mobile Payment';

        } else if($this->payment_type == 2) {
            return 'Cash Payment';

        } else {
            return '';
        }
    }

    public function getDistanceAttribute()
    {
        //$origin_lat = 10.3070105;
        //$origin_lng = 76.3340589;

        $origin_lat = $this->station_latitude;
        $origin_lng = $this->station_longitude;

        $destination_lat = $this->order_latitude;
        $destination_lng = $this->order_longitude;

        $distance = $this->GetDrivingDistance($origin_lat, $destination_lat, $origin_lng, $destination_lng);
        return $distance;
    }

    function GetDrivingDistance($lat1, $lat2, $long1,$long2)
    {
        $key = config('constants.google_map_key');
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2."%2C".$long2."&mode=driving&language=pl-PL&key=" . $key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response, true);        
    
        //return array('distance' => $dist, 'time' => $time);

        if($response_a['rows'] && array_key_exists('distance', $response_a['rows'][0]['elements'][0]) ) {
            //$dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
            $dist = $response_a['rows'][0]['elements'][0]['distance']['value'];
            $time = $response_a['rows'][0]['elements'][0]['duration']['text'];
        
            //$array = array('distance' => $dist, 'time' => $time);
            //$exploded = explode(' ', $array['distance']);
            //$distance   = intval($exploded[0]);
            $distance = round($dist/1000, 2);
            return $distance;
        } else {
            return null;
        }
    }  
}
