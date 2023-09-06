<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class reservationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i =0 ; $i<5;$i++){
            $faker = Faker::create();
            $date = $faker->dateTimeBetween('-6 month', '+6 month')->format('Y-m-d');
            DB::table('reservations')->insert([
                'date' => $date,
                'time_begin' => date('H:i:s', rand(0, 86400)),
                'time_end' => '22:00:00',
                'time_reservation' => date('H:i:s', rand(0, 86400)),
                'price' => rand(5000,10000),
                'user_id' => rand(1,5),
                'parking_id' => rand(1,5),
                'garage_id' => rand(1,5),
                'floor_id' => rand(1,5),
                'car_id' => rand(1,5),
                'pay' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
