<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryCodeSeeder extends Seeder
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
                'country_code' => '+252',
            ),
            array(
                'country_code' => '+91',
            ),
        );
        DB::table('country_codes')->insert($array);
    }
}
