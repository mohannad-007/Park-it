<?php

namespace App\Http\Controllers;

use App\Http\Resources\Subscription as SubscriptionResource;
use App\Models\garages;
use App\Models\subscriptions;
use App\Models\user_subscriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionsController extends Controller
{
    public function createSubscriptionType(Request $request)
    {
        // Validate the request data (you can adjust the validation rules as needed)
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:monthly,yearly|unique:subscriptions',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Create the new subscription type
        $subscription = subscriptions::create([
            'type' => $request->input('type'),
        ]);

        // Return a success response
        return response()->json(['message' => 'Subscription type added successfully.', 'subscription' => $subscription], 201);
    }

    public function showSubscriptions()
    {
        //$garage = garages::find(Auth::guard('garage')->id());
        $Subscription = subscriptions::all();
        return response()->json( SubscriptionResource::collection($Subscription));

    }

    public function getUsersSubscribedInGarage($garageId)
    {
//        $garageId = $request->input('garage_id');
        // Fetch the garage by ID
//        $garage = garages::with('garage_subscriptions.user_subscriptions.users')->find($garageId);
//
//        // Check if the garage exists
//        if (!$garage) {
//            return response()->json(['message' => 'Garage not found.'], 404);
//        }
//
//        // Get the subscribed users
////        $subscribedUsers = $garage->garage_subscriptions->flatMap->user_subscriptions;
//        $subscribedUsers = $garage;

        // Find the garage by ID
        $garage = garages::with('garage_subscriptions.user_subscriptions.users')
            ->findOrFail($garageId);

        // Check if the garage has subscriptions
        if ($garage->garageSubscriptions) {
            // Sort the garage subscriptions and user subscriptions by oldest start_date_sub
            $garage->garage_subscriptions = $garage->garage_subscriptions->sortBy(function ($subscription) {
                return $subscription->userSubscriptions->isEmpty()
                    ? null
                    : $subscription->userSubscriptions->first()->start_date_sub;
            });
        }

        // Return the subscribed users as a response
        return response()->json(['subscribed_users on spicific garage' => $garage], 200);
    }


    public function deleteUserSubscription($id)
    {
        // Find the user subscription based on user_id and garage_subscriptions_id
        $userSubscription = user_subscriptions::where('user_id', $id)
            ->first();

        if (!$userSubscription) {
            return response()->json(['message' => 'User subscription not found.'], 404);
        }

        // Delete the user subscription
        $userSubscription->delete();

        return response()->json(['message' => 'User subscription deleted successfully.'], 200);
    }

}
