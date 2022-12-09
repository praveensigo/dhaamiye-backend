<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FuelStationSeeder extends Seeder
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
                'place' => 'Calicut, Kerala',
                'latitude' => '11.2588',
                'longitude' => '75.7804',
                'address'  => 'Calicut, Kerala, India',
                'added_by' => '1',
                'status' => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ),
            array(
                'place' => 'Chalakudy, Kerala',
                'latitude' => '10.3070105',
                'longitude' => '76.3340589',
                'address'  => 'Chalakudy, Kerala, India',
                'added_by' => '1',
                'status' => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            )
        );
        DB::table('fuel_stations')->insert($array);
    }
}
