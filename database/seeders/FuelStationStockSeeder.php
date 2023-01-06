<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FuelStationStockSeeder extends Seeder
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
                'fuel_station_id' => 1,
                'fuel_type_id' => 1,
                'price' => 10,
                'stock' => 500,
                'added_by' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ),
            array(
                'fuel_station_id' => 1,
                'fuel_type_id' => 2,
                'price' => 15,
                'stock' => 800,
                'added_by' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            )
        );
        DB::table('fuel_station_stocks')->insert($array);
    }
}
