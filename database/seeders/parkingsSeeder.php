<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class parkingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i =0 ; $i<5;$i++) {
            DB::table('parkings')->insert([
                'number' => Str::random(10),
                'floors_id' =>rand(1,3),
                'status_id' => rand(1,3),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
