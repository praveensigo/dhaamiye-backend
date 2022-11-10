<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            CountryCodeSeeder::class,
            SubAdminSeeder::class,
            CustomerSeeder::class,
            FuelStationSeeder::class,
            TruckSeeder::class,
            DriverSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            FuelTypeSeeder::class,
        ]);
    }
}
