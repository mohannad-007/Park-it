<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\walletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{
//    public function user_new_account(Request $request){
//        $validator = Validator::make($request->all(), [
//            'name' => 'required|string|max:255',
//            'nickname' => 'required|string|max:255',
//            'date_of_birthday' => 'date',
//            'phone_number' => 'required',
//            'email' => 'required|string|email|max:255|unique:users',
//            'password' => 'required|string|min:8',
//            'gender' => 'required',
//        ]);
//
//        if($validator->fails()){
//            return response()->json($validator->errors()->toJson(), 400);
//        }
//        $wallet =  walletes::create([
//            'price' => 0,
//        ]);
////        $wallet->save();
//        try {
//        $user=User::create([
//            'name' => $request->name,
//            'nickname' => $request->nickname,
//            'date_of_birthday' => $request->date_of_birthday,
//            'phone_number' => $request->phone_number,
//            'email' => $request->email,
//            'password' => bcrypt($request->password),
//            'gender' => $request->gender,
//            'wallet_id' => $wallet->id,
//        ]);
//            $token = $user->createToken('appToken')->accessToken;
//        } catch (\Exception $exception) {
//            return Response()->json(['message' => $exception->getMessage()]);
//        }
//
////        $user->save();
//
//        return response()->json([
//            'message' => 'Successfully created user!',
//            'user'=>$user,
//            'token'=>$token
//        ], 201);
//    }

    public function user_new_account(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
            'date_of_birthday' => 'date',
            'phone_number' => 'required|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'gender' => 'required',
            // 'image_link' => 'nullable|string|max:255',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $wallet =  walletes::create([
            'price' => 0,
        ]);
//        $wallet->save();
        try {
            $user= User::create([
                'name' => $request->name,
                'nickname' => $request->nickname,
                'date_of_birthday' => $request->date_of_birthday,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'gender' => $request->gender,
                'wallet_id' => $wallet->id,
            ]);
            $token['token'] = $user->createToken('appToken')->accessToken;
            if($request->file('image_link')) {
                $file = $request->file('image_link');
                $filename = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $path = $file->move(public_path('users-images'), $filename);
                $user->image_link = url('users-images/' . $filename);
            }
        } catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }
        $user->save();


        // Modify the user subscription object before adding it to the response
        $userData = $user->toArray();
        unset($userData['id']); // Remove the 'id' field
        $userData = ['id' => $user->id] + $userData;
        return response()->json([
            'message' => 'Successfully created user!',
            'user'=>$userData,
            'token'=>$token
        ], 201);
    }


    public function user_login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        if (Auth::attempt(['email' => request('email'), 'password' => request('password')]))
        {
            // successfull authentication
            $user = User::find(Auth::user()->id);

            $user_token['token'] = $user->createToken('appToken')->accessToken;

            return response()->json([
                'success' => true,
                'user' => $user,
                'token' => $user_token,
            ], 200);
        } else {
            // failure to authenticate
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate.',
            ], 401);
        }
    }


    public function user_logout(Request $request)
    {
        if (Auth::user()) {
            $request->user()->token()->revoke();
            $user = User::find(Auth::user()->id);
            return response()->json([
                'success' => true,
                'message' =>  $user->name.' Logged out successfully',
            ], 200);
        }
    }
}
