<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class garagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i =0 ; $i<5;$i++){
            $faker = Faker::create();
            $name = Str::random(10);
            DB::table('garages')->insert([
                'name' => $name,
                'email' => Str::random(10).'@gmail.com',
                'password' => 12345678,
                'floor_number' => rand(1,3),
                'is_open' => true,
                'price_per_hour' => 5000,
                'parks_number' => rand(1,10),
                'time_open' => date('H:i:s', rand(0, 86400)),
                'time_close' => '22:00:00',
                'garage_information' => 'This is '.$name,
                'created_at' => now(),
                'updated_at' => now(),
                'garage_locations_id' => rand(1,5),
            ]);
        }
    }
}
