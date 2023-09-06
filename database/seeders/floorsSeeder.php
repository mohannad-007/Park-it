<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class floorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        for($i =0 ; $i<5;$i++) {
            DB::table('floors')->insert([
                'floors' => 4,
                'garage_id' => rand(1, 5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    }
}
