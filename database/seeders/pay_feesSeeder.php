<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class pay_feesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i =0 ; $i<5;$i++){
            $faker = Faker::create();
            $date = $faker->dateTimeBetween('-6 month', '+6 month')->format('Y-m-d');
            DB::table('pay_fees')->insert([
                'price' => rand(5000,10000),
                'user_id' => rand(1,5),
                'reservation_id' => rand(1,5),
                'wallet_id' => rand(1,5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
