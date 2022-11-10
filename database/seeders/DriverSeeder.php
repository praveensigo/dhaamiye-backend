<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverSeeder extends Seeder
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
                'fuel_station_id' => '1',
                'truck_id' => '1',
                'reg_status' => '1',
                'added_by' => '1',
                'status' => '1',
                'reg_status' => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ),
            array(
                'fuel_station_id' => '1',
                'truck_id' => '1',
                'reg_status' => '1',
                'added_by' => '1',
                'status' => '1',
                'reg_status' => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            )
        );
        DB::table('drivers')->insert($array);
    }
}
