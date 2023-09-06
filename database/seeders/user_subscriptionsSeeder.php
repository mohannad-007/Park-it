<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class user_subscriptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i =0 ; $i<5;$i++){
            $faker = Faker::create();
            $date = $faker->dateTimeBetween('-6 month', '+6 month')->format('Y-m-d');
            DB::table('user_subscriptions')->insert([
                'date_subscription' => $date,
                'start_time' => date('H:i:s', rand(0, 86400)),
                'end_time' => date('H:i:s', rand(0, 86400)),
                'user_id' => rand(1,5),
                'garage_subscriptions_id' => rand(1,5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
