<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
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
                'role' => 'Admin',
            ),
            array(
                'role' => 'Sub admin',
            ),
            array(
                'role' => 'Customer',
            ),
            array(
                'role' => 'Driver',
            ),
           array(
                'role' => 'Fuel station',
            ),
        );
        DB::table('roles')->insert($array);
    }
}
