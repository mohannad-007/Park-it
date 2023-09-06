<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\garage_employees;
use App\Models\garages;
use App\Models\User;
use App\Models\walletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeeAuthController extends Controller
{

    public function employee_login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $employee = garage_employees::where('email', $request->email)->first();

        if (!$employee || !Hash::check($request->password, $employee->password)) {
            // Failure to authenticate
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate.',
            ], 401);
        }

        $garage_token['token'] = $employee->createToken('appToken')->accessToken;

        return response()->json([
            'success' => true,
            'garage' => $employee,
            'token' => $garage_token,
        ], 200);
    }



    public function employee_logout(Request $request)
    {
        if (Auth::guard('garage_employee'))
        {
            $request->user('garage_employee')->token()->revoke();
            $garage_employee = garage_employees::find(Auth::guard('garage_employee')->id());
            return response()->json([
                'success' => true,
                'message' =>  $garage_employee->name.' Logged out successfully',
            ], 200);
        }
//        if (Auth::user()) {
//            $request->user()->token()->revoke();
//            $garage_employees = garage_employees::find(Auth::user()->id);
//            return response()->json([
//                'success' => true,
//                'message' =>  $garage_employees->name.' Logged out successfully',
//            ], 200);
//        }

    }


}
