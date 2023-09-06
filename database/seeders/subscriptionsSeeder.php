<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class subscriptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i =0 ; $i<5;$i++) {
            $faker = Faker::create();
            DB::table('subscriptions')->insert([
                'type' => Str::random(8),
                'price' => $faker->longitude(0,1000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
