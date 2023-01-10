<?php

namespace App\Models\android\customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FuelStation extends Model
{
    use HasFactory;

    const ACTIVE = 1;
    const BLOCKED = 2;

    protected $appends = [
        'distance',
    ];

    public function fuels()
    {
        return $this->belongsToMany(FuelType::class, 'fuel_station_stocks', 'fuel_station_id', 'fuel_type_id');
    }

    public function favorites()
    {
        return $this->belongsToMany(Customer::class, 'customer_favorite_stations', 'fuel_station_id', 'customer_id')
                ->join('users', 'users.user_id', '=', 'customers.id')
                ->where('role_id', 3);
    }

    /**
    *** Accessors
    **/
    public function getDistanceAttribute()
    {
        //$origin_lat = 10.3070105;
        //$origin_lng = 76.3340589;

        $request = resolve(Request::class);
        $origin_lat = $request->get('latitude', '');
        $origin_lng = $request->get('longitude', '');

        $destination_lat = $this->latitude;
        $destination_lng = $this->longitude;

        $distance = $this->GetDrivingDistance($origin_lat, $destination_lat, $origin_lng, $destination_lng);
        return $distance;
    }


    /**
     * * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('fuel_stations.status', self::ACTIVE);
    }

    public function scopeBlocked($query)
    {
        return $query->where('fuel_stations.status', self::BLOCKED);
    }

    public function scopeDescending($query)
    {
        return $query->orderBy('fuel_stations.created_at', 'desc');
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

        if($response_a && $response_a['rows'] && array_key_exists('distance', $response_a['rows'][0]['elements'][0]) ) {
            //$dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
            $dist = $response_a['rows'][0]['elements'][0]['distance']['value'];
            $time = $response_a['rows'][0]['elements'][0]['duration']['text'];
        
            // $array = array('distance' => $dist, 'time' => $time);
            // $exploded = explode(' ', $array['distance']);
            // $distance   = intval($exploded[0]);
            $distance = round($dist/1000, 2);
            return $distance;
        } else {
            return null;
        }
    }  
}
