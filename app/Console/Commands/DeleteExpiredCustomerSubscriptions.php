<?php

namespace App\Console\Commands;

use App\Models\customer_subscriptions;
use Illuminate\Console\Command;
use Carbon\Carbon;

class DeleteExpiredCustomerSubscriptions extends Command
{
//    protected $signature = 'subscriptions:delete-customer-expired';
//
//    protected $description = 'Delete expired customer subscriptions';
//
//    public function __construct()
//    {
//        parent::__construct();
//    }
//
//    public function handle()
//    {
//        $now = Carbon::now();
//
//        $expiredSubscriptions = customer_subscriptions::where('end_date_sub', '<', Carbon::now())->get();
//        foreach ($expiredSubscriptions as $subscription) {
//            $subscription->delete();
//        }
//
//        $this->info('Expired customer subscriptions deleted successfully.');
//    }
}
