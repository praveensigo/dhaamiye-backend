<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;  

class UserSeeder extends Seeder
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
            	'name_en' => 'admin',
                'name_so' => 'maamulka',
                'image' => null,
                'country_code_id' => NULL,
            	'mobile'   => NULL,               
                'email'    => 'admin@dhaamiye.com',
                'password' => bcrypt('admin@dhaamiye'),
                'role_id'  => '1',
                'user_id'  => '1',
                'reg_status' => '1',
                'status' => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ),
            array(
                'name_en' => 'John',
                'name_so' =>'John',
                'image' =>null,
                'country_code_id' => '1',
                'mobile'   => '92230010',               
                'email'    => 'john@gmail.com',
                'password' => bcrypt('test2020'),
                'role_id'  => '3',
                'user_id'  => '1',
                'reg_status' => '1',
                'status' => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ),
            array(
                'name_en' => 'Jose',
                'name_so' =>'Jose',
                'image' =>null,
                'country_code_id' => '1',
                'mobile'   => '93230011',               
                'email'    => 'jose@gmail.com',
                'password' => bcrypt('test2020'),
                'role_id'  => '3',
                'user_id'  => '2',
                'reg_status' => '1',
                'status' => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ),
            array(
                'name_en' => 'Driver1',
                'name_so' =>'Darawalka1',
                'image' =>null,
                'country_code_id' => '1',
                'mobile'   => '93230013',               
                'email'    => 'driver1@gmail.com',
                'password' => bcrypt('test2020'),
                'role_id'  => '4',
                'user_id'  => '1',
                'reg_status' => '1',
                'status' => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ),
            array(
                'name_en' => 'Driver2',
                'name_so' =>'Darawalka2',
                'image' =>null,
                'country_code_id' => '1',
                'mobile'   => '93230015',               
                'email'    => 'driver2@gmail.com',
                'password' => bcrypt('test2020'),
                'role_id'  => '4',
                'user_id'  => '2',
                'reg_status' => '1',
                'status' => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ),
            array(
                'name_en' => 'Fuel Station 1',
                'name_so' =>'Kaalinta Shidaalka 1',
                'image' =>null,
                'country_code_id' => '1',
                'mobile'   => '93230111',               
                'email'    => 'station1@gmail.com',
                'password' => bcrypt('test2020'),
                'role_id'  => '5',
                'user_id'  => '1',
                'reg_status' => '1',
                'status' => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ),
            array(
                'name_en' => 'Fuel Station 2',
                'name_so' =>'Kaalinta Shidaalka 2',
                'image' =>null,
                'country_code_id' => '1',
                'mobile'   => '93233011',               
                'email'    => 'station2@gmail.com',
                'password' => bcrypt('test2020'),
                'role_id'  => '5',
                'user_id'  => '2',
                'reg_status' => '1',
                'status' => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ),
            array(
                'name_en' => 'Sub Admin 1',
                'name_so' =>'Maamul hoosaadka 1',
                'image' =>null,
                'country_code_id' => NULL,
                'mobile'   => NULL,               
                'email'    => 'subadmin1@gmail.com',
                'password' => bcrypt('test2020'),
                'role_id'  => '2',
                'user_id'  => '1',
                'reg_status' => '1',
                'status' => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ),
        );
        DB::table('users')->insert($array);
    }
}
