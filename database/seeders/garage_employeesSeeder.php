<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class garage_employeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i =0 ; $i<5;$i++) {
            DB::table('garage_employees')->insert([
                'name' => Str::random(8),
                'phone_number' => rand(1000000,9999999),
                'email' => Str::random(10).'@gmail.com',
                'password' => 12345678,
                'address' => Str::random(12),
                'garage_id' => rand(1, 5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
