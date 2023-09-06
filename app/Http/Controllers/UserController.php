<?php

namespace App\Http\Controllers;

use App\Models\required_services;
use App\Models\services;
use App\Models\walletes;
//use Illuminate\Foundation\Auth\User;
use  App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function get_user_info(){ // must logged in use token
        $user = User::find(Auth::user()->id)->load('walletes');
        return response()->json([
            'user' => $user,
        ], 200);
    }

    public function get_all_user_info(){ // must logged in use token
        $user = User::all()->load('walletes');
        return response()->json([
            'user' => $user,
        ], 200);
    }




    public function update_user_info(Request $request)
    {
        // Get the currently authenticated user
        $user = Auth::user();

        try {
            //$garage = garages::find($id);
            $user->name = $request->name ?? $user->name;
            $user->nickname = $request->nickname ?? $user->nickname;
            $user->date_of_birthday = $request->date_of_birthday ?? $user->date_of_birthday;
            $user->password = bcrypt($request->password) ?? $user->password;
            $user->email = $request->email ?? $user->email;
            $user->gender = $request->gender ?? $user->gender;

            if($request->file('image_link')) {
                $file = $request->file('image_link');
                $filename = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $path = $file->move(public_path('users-images'), $filename);
                $user->image_link = url('users-images/' . $filename);
                //  return Response()->json( $user->image_link);
            }

        } catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }
        $user->save();


        // Return a success response
        return response()->json([
            'message' => 'User information updated successfully.',
            'user' => $user,
        ], 200);
    }

    public function get_required_services()
    {
        $user = Auth::user();
        $requiredServices = required_services::with('services.garages')->where('user_id', $user->id)->get();

        return response()->json([
            'required_services' => $requiredServices,
            'user' => $user,
        ], 200);
    }

    public function requestServices(Request $request)
    {
        $user = auth()->user();
        $serviceId = $request->input('service_id');

        // Validate if the user has provided the service_id
        if (!$serviceId) {
            return response()->json(['message' => 'Invalid service request.'], 400);
        }

        // Retrieve the service details
        $service = services::with('garages.garage_location')->find($serviceId);

        if (!$service) {
            return response()->json(['message' => 'Service not found.'], 404);
        }

        // Calculate the total price for the service request
        $totalPrice = $service->price;

        // Check if the user has enough balance in the wallet
//        $userWallet = $user->walletes;
//        if (!$userWallet) {
//            return response()->json(['message' => 'User wallet not found.'], 404);
//        }

//        if ($userWallet->price < $totalPrice) {
//            return response()->json(['message' => 'Insufficient funds in the wallet.'], 400);
//        }

        // Deduct the total price from the user's wallet
//        $userWallet->update(['price' => $userWallet->price - $totalPrice]);

        // Create the required_services record
        $user->required_services()->create([
            'services_id' => $serviceId,
        ]);

        return response()->json([
            'totalPrice' => $totalPrice,
            'message' => 'Service requested successfully.',
            'services'=>$service
        ], 200);
    }
    public function getWalletBalance($userId)
    {
        $user = user::findOrFail($userId);
        $walletBalance = $user->getWalletBalance();

        return response()->json(['wallet_balance' => $walletBalance]);
    }

    public static function searchByNameforUser(Request $request)
    {
        $name = $request->get('name');
        $users = User::where('name', 'LIKE', '%' . $name . '%')->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No user found with this name'], 404);
        }

        $usersData = [];
        foreach ($users as $user) {
            $usersData[] = [
                'id' => $user->id,
                'name' => $user->name,
            ];
        }

        return response()->json($usersData);
    }

    public function getUsersIdsAndNames()
    {
        $users = User::select('id', 'name')->get();

        $response = [
            'data' => $users
        ];

        return response()->json($response);
    }

    public function addTOWalletBalance(Request $request,$userId)
    {
        $user = user::findOrFail($userId);
        $add=$request->get('money');
        $userWallet = $user->walletes;
        $userWallet->update(['price' => $userWallet->price + $add]);
        return response()->json(['wallet_balance' => $userWallet, 'message' => ' تم اضافة المال الى المحفظة']);
    }


}
