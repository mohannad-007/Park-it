<?php

namespace App\Http\Controllers;

use App\Http\Resources\GarageSubscription as GarageSubscription;
use App\Models\garage_subscriptions;
use App\Models\garages;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GarageSubscriptionsController extends Controller
{
    public function addGarageSubscription(Request $request)
    {
        // Validate the input data
        $request->validate([
            'garage_id' => 'required|exists:garages,id',
            'subscription_id' => 'required|exists:subscriptions,id',
            'price' => 'required|numeric',
        ]);

        // Create the garage subscription
        $garageSubscription = garage_subscriptions::create([
            'garage_id' => $request->input('garage_id'),
            'subscription_id' => $request->input('subscription_id'),
            'price' => $request->input('price'),
        ]);

        return response()->json(['message' => 'Garage subscription added successfully.', 'subscription' => $garageSubscription], 201);
    }

    public function deleteGarageSubscription($id)
    {
        $garageSubscription = garage_subscriptions::find($id);

        if (!$garageSubscription) {
            return response()->json(['message' => 'Garage subscription not found.'], 404);
        }

        $garageSubscription->delete();

        return response()->json(['message' => 'Garage subscription deleted successfully.'], 200);
    }

    public function editGarageSubscription(Request $request, $id)
    {
        // Validate the input data
        $request->validate([
            'garage_id' => 'required|exists:garages,id',
            'subscription_id' => 'required|exists:subscriptions,id',
            'price' => 'required|numeric',
        ]);

        $garageSubscription = garage_subscriptions::find($id);

        if (!$garageSubscription) {
            return response()->json(['message' => 'Garage subscription not found.'], 404);
        }

        // Update the garage subscription
        $garageSubscription->garage_id = $request->input('garage_id');
        $garageSubscription->subscription_id = $request->input('subscription_id');
        $garageSubscription->price = $request->input('price');
        $garageSubscription->save();

        return response()->json(['message' => 'Garage subscription updated successfully.', 'subscription' => $garageSubscription], 200);
    }

    ////////////////////
    public function showMySubscription()
    {
        $garage = garages::find(Auth::guard('garage')->id());

        $subscriptions = $garage->garage_subscriptions;

        if ($subscriptions) {
            $subscriptionData = GarageSubscription::collection($subscriptions)->map(function ($subscription) {
                return [
                    'id'=> $subscription->id,
                     'price'=> $subscription->price,
                    'subscription'=> $subscription->subscriptions->type,

                ];
            });

            return response()->json($subscriptionData);
        } else {
            return response()->json(['message' => 'لا توجد اشتراكات متاحة']);
        }
    }


    public function addSubscription(Request $request)
    {

        $garage = garages::find(Auth::guard('garage')->id());


        $validatedData = $request->validate([
            'subscription_id' => 'required|integer|exists:subscriptions,id',
            'price'=>'required|integer'
        ]);


        try {
            $garage_subscription = new garage_subscriptions();
            $garage_subscription->garage_id = $garage->id;
            $garage_subscription->price = $validatedData['price'];
            $garage_subscription->subscription_id = $validatedData['subscription_id'];
            $garage_subscription->save();
        }catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }
        return response()->json(['message' => 'تم إضافة الاشتراك', new GarageSubscription($garage_subscription), 200]);
    }

    public function updateMySubscription(Request $request,$id): \Illuminate\Http\JsonResponse
    {

        $subscription = garage_subscriptions::find($id);

        try {
            //$garage = garages::find($id);
            //$service->image = $request->image ?? $service->image;///هون يمكن مو هيك
            $subscription->price = $request->price ?? $subscription->price;
            $subscription->save();
        } catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }
        return response()->json([
            'message' => 'subscription updated successfully', new GarageSubscription($subscription)]);
    }

    public function removeSubscription($service_id)
    {
        $garage = garages::find(Auth::guard('garage')->id());


        $services = garage_subscriptions::find($service_id);

        $services->delete();
        return response()->json(['message' => 'subscription removed successfully'], 200);
    }

    public function showMyUsersSubscription()
    {
        $garageId = garages::where('id', '=', Auth::guard('garage')->id())->value('id');
        // $garage = garages::find(Auth::guard('garage')->id());
        // $subscriptions = $garage->garage_subscriptions->user_subscriptions;

        $users = user::join('user_subscriptions', 'users.id', '=', 'user_subscriptions.user_id')
            ->join('garage_subscriptions', 'user_subscriptions.garage_subscriptions_id', '=', 'garage_subscriptions.id')
            ->join('garages', 'garage_subscriptions.garage_id', '=', 'garages.id')
            ->where('garages.id', '=', $garageId)
            ->get();

        if ($users->count()>0) {
            return response()->json([ $users ]);
        } else {
            return response()->json(['message' => 'لا يوجد مشتركين']);
        }
    }



    public function showGarageSubscriptions($garageId)
    {
        // Retrieve the garage subscriptions for the specific garage
        $garageSubscriptions = garage_subscriptions::where('garage_id', $garageId)
            ->with('subscriptions')// Load the subscription details
            ->get();
        $garage = garages::findorfail($garageId);

        return response()->json([
            'garage' => $garage,
            'garage_subscriptions' => $garageSubscriptions,
        ], 200);
    }

}
