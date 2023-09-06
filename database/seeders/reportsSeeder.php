<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class reportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i =0 ; $i<5;$i++){
            $faker = Faker::create();
            $date = $faker->dateTimeBetween('-6 month', '+6 month')->format('Y-m-d');
            DB::table('reports')->insert([
                'report_date' => $date,
                'report_test' => Str::random(8),
                'reservation_id' => rand(1,5),
                'required_service_id' => rand(1,5),
                'user_subscription_id' => rand(1,5),
                'pay_fees_id' => rand(1,5),
                'garage_id' => rand(1,5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
