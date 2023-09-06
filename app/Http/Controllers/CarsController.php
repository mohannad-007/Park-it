<?php

namespace App\Http\Controllers;

use App\Models\car_types;
use App\Models\cars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Picqer\Barcode\BarcodeGeneratorJPG;

class CarsController extends Controller
{
    function MakeCarBarcode(int $car,int $user)
    {

        try {
            $generator = new BarcodeGeneratorJPG();

            // $barcode_value = $request->car_id . '-' . $request->user_id;
            $barcode_value = $car . '-' . $user;
            $barcode = $generator->getBarcode($barcode_value, $generator::TYPE_CODE_128);
            $filename = $barcode_value . 'barcode.jpg';

            file_put_contents(' barcode-images', $filename);
            $barcode_path = public_path('barcode-images/' . $filename);
            file_put_contents($barcode_path, $barcode);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()]);
        }
        return url('barcode-images/' . $filename);
//        return response()->json([
//            'barcode' =>url('barcode-images/' . $filename),
////
//        ]);
    }

    public function add_user_car(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'number' => 'required|integer|unique:cars',
            'image' => 'required|image',
        ]);
//        $car_types =  car_types::create([
//            'image' =>$request->image,
//        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            $car = new cars;

            $car->name = $request->name;
            $car->number = $request->number;
            $car->user_id = $user->id;

            $file = $request->file('image');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename =  time() . '.' . $extension;
            $path = $file->move(public_path('cars-images'), $filename);
            $car->image = url('cars-images/' . $filename);
            $car->save();
        }catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }
        try {
            $car->barcode = $this->MakeCarBarcode($car->id,$user->id);
        }catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }
        $car->save();

        return response()->json([
            'message' => "success add new car",
            'car' => $car,
            'user' => $user,
        ], 201);
    }

    public function get_user_cars() //must logged in used token
    {
        // Get the currently authenticated user
        $user = Auth::user();
        // Get the cars associated with the user
        $userCars = $user->cars;
        // Return the cars for the user in JSON format
        return response()->json([
            'user_cars' => $userCars,
        ], 200);
    }

    public function delete_user_car($carId)
    {
        // Get the currently authenticated user
        $user = Auth::user();
        // Find the car associated with the user by its ID
        $car = $user->cars()->find($carId);
        if (!$car) {
            return response()->json(['message' => 'Car not found.'], 404);
        }
        // Delete the car
        $car->delete();

        // Return a success response
        return response()->json(['message' => 'Car deleted successfully.'], 200);
    }


    public function update_user_car(Request $request,$car_id)
    {
        // Get the currently authenticated user
        $user = Auth::user();

        // Validate the request data (you can adjust the validation rules as needed)
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'number' => 'required|integer',
            'image' => 'required|image',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Find the car associated with the user by its ID
        $car = $user->cars()->find($request->car_id);

        if (!$car) {
            return response()->json(['message' => 'Car not found.'], 404);
        }

        // Update the car's information
        $car->update([
            'name' => $request->name,
            'number' => $request->number,
        ]);

        if( $request->image)
        {
            $file = $request->file('image');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename =  time() . '.' . $extension;
            $path = $file->move(public_path('cars-images'), $filename);
            $car->image = url('cars-images/' . $filename);
        }

        $car->barcode = $this->MakeCarBarcode($car->id,$user->id);
        // Find or create the car type based on the provided image
        //  $carType = car_types::update(['image' => $request->image]);

        // Associate the updated car with the car type
        // $car->car_types()->associate($carType);
        $car->save();

        // Return a success response
        return response()->json([
            'message' => 'Car information updated successfully.',
            'car' => $car,
        ], 200);
    }



}
