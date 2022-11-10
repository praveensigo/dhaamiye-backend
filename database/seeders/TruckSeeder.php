<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TruckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array = array(
            array(
                'truck_no' => '123456',
                'fuel_station_id' => '1',
                'manufacturer'    => 'Honda',
                'manufactured_year'  => '2018',
                'model' => 'XYZ',
                'color' => 'White',
                'chassis_no' => '5666', 
                'engine_no'  => '878787',
                'mot_certificate_url' => null,
                'mot_certificate_expiry' => null,
                'insurance_certificate_url' => null,
                'insurance_certificate_expiry' => null,
                'truck_certificate_url' => null,
                'truck_certificate_expiry' => null,
                'added_by' => '1',
                'reg_status' => '1',
                'status' => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            )
        );
        DB::table('trucks')->insert($array);
    }
}
