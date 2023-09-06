<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
//use Illuminate\Foundation\Auth\User;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

//use Database\Factories\UserFactory;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i =0 ; $i<5;$i++){
        DB::table('users')->insert([
            'name' => Str::random(10),
            'nickname' => Str::random(10),
            'date_of_birthday' => now(),
            'phone_number' => rand(1000000, 9999999),
            'email' => Str::random(10).'@gmail.com',
            'password' => 12345678,
            'gender' => "male",
            'wallet_id' => rand(1,5),
        ]);
        }


//        User::factory()
//            ->count(50)
//            ->create();
    }
}
