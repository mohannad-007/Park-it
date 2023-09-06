<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(walletesSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(statusesSeeder::class);
        $this->call(garage_locationsSeeder::class);
        $this->call(garagesSeeder::class);
        $this->call(floorsSeeder::class);
        $this->call(car_typesSeeder::class);
        $this->call(carsSeeder::class);
        $this->call(parkingsSeeder::class);
        $this->call(servicesSeeder::class);
        $this->call(garage_employeesSeeder::class);
        $this->call(subscriptionsSeeder::class);
        $this->call(fav_garagesSeeder::class);
        $this->call(active_usersSeeder::class);
        $this->call(customersSeeder::class);
        $this->call(required_servicesSeeder::class);
        $this->call(reservationsSeeder::class);
        $this->call(w_parksSeeder::class);
        $this->call(garage_subscriptionsSeeder::class);
        $this->call(user_subscriptionsSeeder::class);
        $this->call(pay_feesSeeder::class);
        $this->call(reportsSeeder::class);
        $this->call(w_invoicesSeeder::class);

    }
}
