<?php

namespace App\Console\Commands;

use App\Models\user_subscriptions;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:delete-expired';
    protected $description = 'Delete expired subscriptions';

    public function handle()
    {
        // Delete expired subscriptions
        $expiredSubscriptions = user_subscriptions::where('end_date_sub', '<', Carbon::now())->get();
        foreach ($expiredSubscriptions as $subscription) {
            $subscription->delete();
        }

        $this->info('Expired subscriptions have been deleted.');
    }
}
