<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class w_invoicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i =0 ; $i<5;$i++){
            $faker = Faker::create();
            $date = $faker->dateTimeBetween('-6 month', '+6 month')->format('Y-m-d');
            DB::table('w_invoices')->insert([
                'price' => rand(5000,10000),
                'wallet_id' => rand(1,5),
                'user_id' => rand(1,5),
                'w_parks_id' => rand(1,5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
