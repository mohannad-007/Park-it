<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class garage_locationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        for ($i = 0; $i < 5; $i++) {
            $faker = Faker::create();
            DB::table('garage_locations')->insert([
                'Longitude_lines' => $faker->longitude(0,10900),
                'Latitude_lines' =>  $faker->latitude(0,10900),
                'city' => Str::random(8),
                'country' => Str::random(8),
                'street' => Str::random(8),
            ]);
        }
    }
}
