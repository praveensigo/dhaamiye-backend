<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TruckFuelSeeder extends Seeder
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
                'truck_id' => '1',
                'fuel_type_id' => '1',
                'capacity' => '1000',
                'stock' => '500',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ),
            array(
                'truck_id' => '1',
                'fuel_type_id' => '2',
                'capacity' => '1000',
                'stock' => '500',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            )
        );
        DB::table('truck_fuels')->insert($array);
    }
}
