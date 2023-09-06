<?php

namespace App\Http\Controllers;

use App\Models\customer_subscriptions;
use App\Models\garage_employees;
use App\Models\garage_subscriptions;
use App\Models\garages;
use App\Models\subscriptions;
use App\Models\User;
use App\Models\user_subscriptions;
use App\Models\walletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserSubscriptionsController extends Controller
{

    public function getUserSubscriptions($id)
    {
        $subscriptions = user_subscriptions::with('users')->where('user_id',$id)->get();
        $garage = user_subscriptions::with('garage_subscriptions.garages')->where('user_id',$id)->get();

        foreach ($garage as $subscription) {
            $subscriptionId = $subscription->garage_subscriptions->subscription_id;
            $subscriptionInfo = subscriptions::find($subscriptionId);

            $subscription->garage_subscriptions->subscription = $subscriptionInfo;
        }

        return response()->json([
            'subscriptions' => $subscriptions,
            'garage'=>$garage,
        ], 200);
    }


    public function subscriptions_with_garage(Request $request)
    {
        $user = Auth::user()->load('walletes');
        $garageId = $request->input('garage_id');
        $subscriptionType = $request->input('subscription_type');
        $numberOfMonths = $request->input('number_of_months'); // Assuming this is the input for the number of months

        $garage = garages::with('garage_subscriptions.subscriptions')->findOrFail($garageId);
        if (!$garage) {
            return response()->json(['message' => 'Garage not found.'], 404);
        }

        // Check if the subscription type exists in the "garage_subscriptions" table
        $subscription = garage_subscriptions::whereHas('subscriptions', function ($query) use ($subscriptionType) {
            $query->where('type', $subscriptionType);
        })->first();

        if (!$subscription) {
            return response()->json(['message' => 'Invalid subscription type.'], 400);
        }

        // Check if there is an existing subscription for the same customer and garage with the same start date
        $existingSubscription = user_subscriptions::where('user_id', $user->id)
            ->where('start_date_sub', Carbon::now()->toDateString())
            ->first();

        if ($existingSubscription) {
            return response()->json(['message' => 'Subscription already exists for the same date.'], 400);
        }

        // Fetch the correct price from the "garage_subscriptions" table
        $pricePerMonth = $subscription->price;
        $totalPrice = $pricePerMonth * $numberOfMonths;

        // Check the user's wallet balance
        $userWallet = $user->walletes;
        if (!$userWallet) {
            return response()->json(['message' => 'User wallet not found.'], 404);
        }

        if ($userWallet->price < $totalPrice) {
            return response()->json(['message' => 'Insufficient funds in the wallet.'], 400);
        }

        // Proceed with the subscription if all conditions are met
        // You may want to handle any payment or transaction logic here if applicable
        DB::beginTransaction();
//            return $user;
        try {
            $userWallet->update(['price' => $userWallet->price - $totalPrice]);
            // Create the user subscription
//            $userSubscription = new user_subscriptions;
//            $userSubscription->start_date_sub = Carbon::now()->toDateString();
//            $userSubscription->end_date_sub = $subscriptionType === 'monthly'
//                    ? Carbon::now()->addMonths($numberOfMonths)->toDateString()
//                    : Carbon::now()->addYears($numberOfMonths)->toDateString();
//            $userSubscription->user_id = $user->id;
//            $userSubscription->garage_subscriptions_id = $subscription->id;
//            $userSubscription->save();

            // Create the user subscription
            $userSubscription = user_subscriptions::create([
                'start_date_sub' => Carbon::now()->toDateString(),
                'end_date_sub' => $subscriptionType === 'monthly'
                    ? Carbon::now()->addMonths($numberOfMonths)->toDateString()
                    : Carbon::now()->addYears($numberOfMonths)->toDateString(),
                'user_id' => $user->id,
                'garage_subscriptions_id' => $subscription->id,
            ]);
            DB::commit();



            // Modify the user subscription object before adding it to the response
            $userSubscriptionData = $userSubscription->toArray();
            unset($userSubscriptionData['id']); // Remove the 'id' field
            $userSubscriptionData = ['id' => $userSubscription->id] + $userSubscriptionData;

            return response()->json([
                'totalPrice' => $totalPrice,
                'message' => 'Subscription successful.',
                'user_subscription' => $userSubscriptionData,
                'user' => $user,
                'garage' => $garage,
            ], 200);

//            return response()->json([
//                'totalPrice' => $totalPrice,
//                'message' => 'Subscription successful.',
////                'user subscription_id' => $userSubscription->id,
//                'user subscription' => $userSubscription,
//                'user'=>$user,
//                'garage'=>$garage,
//            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'An error occurred while processing the subscription.',$e->getMessage()], 500);
        }
    }


    public function update_subscriptions_with_garage(Request $request)
    {
        $user = Auth::user()->load('walletes');
        $garageId = $request->input('garage_id');
        $subscriptionType = $request->input('subscription_type');
        $numberOfMonths = $request->input('number_of_months'); // Assuming this is the input for the number of months

        $garage = garages::find($garageId);
        if (!$garage) {
            return response()->json(['message' => 'Garage not found.'], 404);
        }

        // Check if the subscription type exists in the "garage_subscriptions" table
        $subscription = garage_subscriptions::whereHas('subscriptions', function ($query) use ($subscriptionType) {
            $query->where('type', $subscriptionType);
        })->first();

        if (!$subscription) {
            return response()->json(['message' => 'Invalid subscription type.'], 400);
        }

        // Fetch the correct price from the "garage_subscriptions" table
        $pricePerMonth = $subscription->price;
        $totalPrice = $pricePerMonth * $numberOfMonths;

        // Check the user's wallet balance
        $userWallet = $user->walletes;
        if (!$userWallet) {
            return response()->json(['message' => 'User wallet not found.'], 404);
        }

        if ($userWallet->price < $totalPrice) {
            return response()->json(['message' => 'Insufficient funds in the wallet.'], 400);
        }

        // Check if the user has an active subscription for the same garage and subscription type
        $activeSubscription = user_subscriptions::where('user_id', $user->id)
            ->where('garage_subscriptions_id', $subscription->id)
            ->where('end_date_sub', '>=', Carbon::now()->toDateString())
            ->first();

        DB::beginTransaction();
        try {
            if ($activeSubscription) {
                // Renew the existing subscription by updating its end date
                $activeSubscription->update([
                    'end_date_sub' => $subscriptionType === 'monthly'
                        ? Carbon::parse($activeSubscription->end_date_sub)->addMonths($numberOfMonths)->toDateString()
                        : Carbon::parse($activeSubscription->end_date_sub)->addYears($numberOfMonths)->toDateString(),
                ]);
            } else {
                // Create the user subscription since it doesn't exist
                $userSubscription = user_subscriptions::create([
                    'start_date_sub' => Carbon::now()->toDateString(),
                    'end_date_sub' => $subscriptionType === 'monthly'
                        ? Carbon::now()->addMonths($numberOfMonths)->toDateString()
                        : Carbon::now()->addYears($numberOfMonths)->toDateString(),
                    'user_id' => $user->id,
                    'garage_subscriptions_id' => $subscription->id,
                ]);
            }
            // Deduct the total price from the user's wallet
            $userWallet->update(['price' => $userWallet->price - $totalPrice]);
            DB::commit();

            return response()->json([
                'totalPrice' => $totalPrice,
                'message' => 'Subscription renewed successfully.',
                'subscription' => $userSubscription ?? $activeSubscription,
                'user'=>$user,
                'garage'=>$garage,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'An error occurred while processing the subscription.',
                $e->getMessage()
            ], 500);
        }
    }


//    public function filterUserSubscriptions()
//    {
////        $garage_employees = garages::find(Auth::guard('garage_employee'))->id();
//        $garage_employees = garage_employees::find(Auth::guard('garage_employee')->id());
////        return$garage_employees;
//
////        $subscriptions = user_subscriptions::with( 'garage_subscriptions.garages')
////            ->orderBy('start_date_sub', 'asc')
////            ->get();
//        $subscriptions = user_subscriptions::with('garage_subscriptions.garages')
//            ->whereHas('garage_subscriptions.garages', function ($query) use ($garage_employees) {
//                $query->where('garage_employees_id', $garage_employees);
//            })
//            ->orderBy('start_date_sub', 'asc')
//            ->get();
//
//        return response()->json([
//            'subscriptions' => $subscriptions,
//        ], 200);
//    }


//    public function filterUserSubscriptions($id)
//    {
//        // Find the garage employee based on the provided ID
////        $garage_employees = garage_employees::findOrFail($id);
//        $garage_employees = Auth::guard('garage_employee')->id();
//
//        // Retrieve the garage associated with the garage employee
//        $garage = $garage_employees->garage;
//
//        // Retrieve user subscriptions for the specified garage
////        $userSubscriptions = user_subscriptions::with('users', 'garage_subscriptions.garages')
////            ->whereHas('garage_subscriptions', function ($query) use ($garage) {
////                $query->where('garage_id', $garage->id);
////            })
////            ->orderBy('start_date_sub', 'asc')
////            ->get();
//
//        $userSubscriptions = user_subscriptions::with('users', 'garageSubscriptions.garage')
//            ->whereHas('garageSubscriptions.garage', function ($query) use ($garage) {
//                $query->where('id', $garage->id);
//            })
//            ->orderBy('start_date_sub', 'asc')
//            ->get();
//
//        return response()->json([
//            'user_subscriptions' => $userSubscriptions,
//            'garage_employee' => $garage_employees,
//            'garage' => $garage,
//        ], 200);
//    }








}
