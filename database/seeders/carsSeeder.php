<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class carsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i =0 ; $i<5;$i++) {
            $name = Str::random(10);
            DB::table('cars')->insert([
                'name' => $name,
                'number' => rand(10,49),
                'code_number' => rand(1000,10000),
                'user_id' =>rand(1,5),
                'car_types_id' => rand(1,5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
