<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AboutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $content_en = '<p>Dhaamiye is an online fuel delivery station that brings fuel directly to you and your equipment anytime, day or night. Customers can order fuel online using smartphone and get the fuel delivered at their preferred location with convenience of online and pay on delivery options. Fuel station owners and fuel distributors can use the app to grow their business and to get access with huge customer base at anytime and anywhere. Dhaamiye app is built to provide end-to-end fuel delivery platform that delivers the fuel directly to individuals and businesses, eliminating the need to ever stop at the fuel station. This will save time, energy and money with confidence. </p><p>Whether customers need fuel for construction generators, hotels, hospitals, schools apartments or refueling company vehicles and fleets, dhaamiye can customize a fuel delivery strategy that will help them set up a refueling program that works best for their business. Dhaamiye platform is pretty simple, just in three easy steps customers can schedule their fuel deliveries in a matter of minutes.</p><h4>DHAAMIYE APP</h4><p>We strive to help make fuel delivery effortless, affordable and accessible to everyone. Dhaamiye technology gives customers access to their favorite fuel station so they can order fuel from anywhere.</p>';
        $content_so = '<p>Somali: Dhaamiye is an online fuel delivery station that brings fuel directly to you and your equipment anytime, day or night. Customers can order fuel online using smartphone and get the fuel delivered at their preferred location with convenience of online and pay on delivery options. Fuel station owners and fuel distributors can use the app to grow their business and to get access with huge customer base at anytime and anywhere. Dhaamiye app is built to provide end-to-end fuel delivery platform that delivers the fuel directly to individuals and businesses, eliminating the need to ever stop at the fuel station. This will save time, energy and money with confidence. </p><p>Whether customers need fuel for construction generators, hotels, hospitals, schools apartments or refueling company vehicles and fleets, dhaamiye can customize a fuel delivery strategy that will help them set up a refueling program that works best for their business. Dhaamiye platform is pretty simple, just in three easy steps customers can schedule their fuel deliveries in a matter of minutes.</p><h4>DHAAMIYE APP</h4><p>We strive to help make fuel delivery effortless, affordable and accessible to everyone. Dhaamiye technology gives customers access to their favorite fuel station so they can order fuel from anywhere.</p>';
        $array = array(
            array(
                'title_en' => 'About Dhaamiye',
                'title_so' => 'About Dhaamiye Somali',
                'content_en' => $content_en,
                'content_so'  => $content_so,                
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            )
        );
        DB::table('about')->insert($array);
    }
}
