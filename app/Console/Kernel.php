<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        // ...
        \App\Console\Commands\DeleteExpiredSubscriptions::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Add the following line to schedule the command to run daily at midnight (you can change the schedule as per your needs)
        $schedule->command('subscriptions:delete-expired')->dailyAt('00:00');

//        $schedule->command('subscriptions:delete-customer-expired')->daily();

    }


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'\App\Console\Commands\DeleteExpiredSubscriptions::class,');

        require base_path('routes/console.php');
    }





}
