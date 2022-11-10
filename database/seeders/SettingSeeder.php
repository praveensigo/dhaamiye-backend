<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
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
                'fuel_delivery_range' => '10',
                'commision' => '10',
                'tax' => '20',
                'min_fuel_level'  => '300',
                'android_version_driver' => '1.2',
                'android_version_customer' => '1.1',
                'ios_version_driver' => '1.1',
                'ios_version_customer' =>'1.2',
                'maintenance_customer' =>0,
                'maintenance_driver'=>0,
                'maintenance_reason_customer_en' =>null,
                'maintenance_reason_customer_so' =>null,
                'maintenance_reason_driver_en' =>null,
                'maintenance_reason_driver_so' =>null,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            )
        );
        DB::table('settings')->insert($array);
    }
}
