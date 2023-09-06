<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\floors;
use App\Models\garage_employees;
use App\Models\garage_location;
use App\Models\garages;
use App\Models\parkings;
use App\Models\status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class GarageAuthController extends Controller
{

    public function employee_new_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:garage_employees',
            'email' => 'required|string|email|max:255|unique:garage_employees',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|unique:garage_employees',
            'address' => 'required|string|max:255',
//            'garage_id' => 'required|exists:garages,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $employee=garage_employees::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'garage_id'=> auth()->guard('garage')->user()->id,
        ]);
        if($request->file('image')) {
            $file = $request->file('image');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $path = $file->move(public_path('employees-images'), $filename);
            $employee->image = url('employees-images/' . $filename);
            $employee->save();
        }
        $employee->makeHidden('password');
        return response()->json([
            'message' => 'Successfully created employee!',
            'employee' => $employee
        ], 201);
    }

    public function garage_new_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:garages',
            'password' => 'required|string|min:8',
            'floor_number' => 'required|integer',
            'is_open' => 'required|boolean',
            'price_per_hour' => 'required|numeric',
            'parks_number' => 'required|integer',
            'time_open' => 'required|date_format:H:i',
            'time_close' => 'required|date_format:H:i',
            'garage_information' => 'required|string',
            'location'=>'required',
            'city'=>'required|string',
            'country'=> 'required|string',
            'street'=> 'required|string',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }


        try {

            $location = $request->location;

            $parts = explode(",", $location);
            $x = floatval($parts[0]);
            $y = floatval($parts[1]);

            $location =  garage_location::create([
                'Longitude_lines' =>$x,
                'Latitude_lines'=>$y,
                'city'=>$request->city,
                'country'=>$request->country,
                'street'=>$request->street,
            ]);

            $garage=garages::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'floor_number' => $request->floor_number,
                'is_open' => $request->is_open,
                'price_per_hour' => $request->price_per_hour,
                'parks_number' => $request->parks_number,
                'time_open' => $request->time_open,
                'time_close' => $request->time_close,
                'garage_information' => $request->garage_information,
                'garage_locations_id'=> $location->id,
            ]);

            $token = $garage->createToken('appToken')->accessToken;

            $newNumberOfFloors = $request->floor_number;
            $startingParkingNumber = 1; // أرقام المواقف للطابق الأول
            for ($i = 1; $i <= $newNumberOfFloors; $i++) {
                $newfloor = new floors([
                    'number' => ($i),
                    'garage_id' => $garage->id,
                ]);
                $newfloor->save();

                $rowsPerFloor = ceil($request->parks_number / $request->floor_number);

                $state = status::find(1);

                $parkingNumber = $startingParkingNumber; // تعيين بداية أرقام المواقف لهذا الطابق
                $floorId = floors::where('number', $i)
                    ->where('garage_id', $garage->id)
                    ->value('id');

                for ($j = 1; $j <= $rowsPerFloor; $j++) {
                    if ($parkingNumber > $request->parks_number) {
                        break;
                    }

                    $newRow = new parkings([
                        'number' => "P-" . $parkingNumber,
                        'floors_id' => $floorId,
                        'status_id' => $state->id,
                        'garage_id' => $garage->id,
                    ]);
                    $newRow->save();

                    $parkingNumber++;
                }

                $startingParkingNumber = $parkingNumber; // تحديث بداية أرقام المواقف للطابق القادم
            }






        }catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }
        $garage->makeHidden('password');
        return response()->json([
            'message' => "تم اضافة الكراج بنجاح",
            'garages' => $garage,
            'location'=>$garage->garage_locations_id,
            'token'=>$token
        ], 201);
    }

    public function garage_login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $garage = garages::where('email', $request->email)->first();

        if (!$garage || !Hash::check($request->password, $garage->password)) {
            // Failure to authenticate
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate.',
            ], 401);
        }

        $garage_token['token'] = $garage->createToken('appToken')->accessToken;

        return response()->json([
            'success' => true,
            'token' => $garage_token,
            'garage' => $garage,
        ], 200);

    }


    public function garage_logout(Request $request)
    {
        if (Auth::guard('garage'))
        {
            $request->user('garage')->token()->revoke();
            $garage = garages::find(Auth::guard('garage')->id());
            // if (Auth::garage()) {
            //  $request->garage()->token()->revoke();
            //  $garage_employees = garages::find(Auth::garage()->id);
            return response()->json([
                'success' => true,
                'message' =>  $garage->name.' Logged out successfully',
            ], 200);
        }
    }


}
