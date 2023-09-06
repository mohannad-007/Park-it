<?php

namespace App\Http\Controllers;

use App\Http\Resources\Parkings as ParkingsResource;
use App\Models\floors;
use App\Models\garages;
use App\Models\parkings;
use App\Models\status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ParkingsController extends Controller
{
    public function showParkingForGarages($id)
    {
        // Fetch the garage along with its floors and parking spaces
        $garage = garages::with('floors.parkings.status')->find($id);

        if (!$garage) {
            return response()->json(['message' => 'Garage not found.'], 404);
        }

        return response()->json(['garage' => $garage], 200);
    }


    public function showInfoParkingForGarages($garageId, $parkingId)
    {
        // Fetch the garage along with its floors and the specific parking
        $garage = garages::with(['floors' => function ($query) use ($parkingId) {
            $query->with(['parkings' => function ($query) use ($parkingId) {
                $query->where('id', $parkingId);
            }]);
        }])->find($garageId);

//        $garage = garages::with('floors.parkings')->find($garageId);

        if (!$garage) {
            return response()->json(['message' => 'Garage not found.'], 404);
        }

        // Check if the parking space exists in the garage
        $parking = $garage->floors->flatMap->parkings->where('id', $parkingId)->first();
        if (!$parking) {
            return response()->json(['message' => 'Parking not found in this garage.'], 404);
        }

        $p1 = parkings::with('floors')->find($parking);
        $p2 = parkings::with('status')->find($parking);

        return response()->json([
            'parking' => $parking,
            'parking floor'=>$p1,
            'parking status'=>$p2,
        ], 200);
    }

    //////////////////////////
    public function addparking(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'number' => 'required|integer',
            'floors_id' => 'required|integer|exists:floors,id',

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $parking = new Parkings;
            $floor = floors::find($request->floors_id);
            $state= status::find(1);
            $parking->number = $request->number;
            $state = status::find(1);
            $parking->status_id = $state->id;
            $floor->pakings()->save($parking);


        } catch (\Exception $exception) {
            return response()->json(['message' => 'Unable to add parking', 'error' => $exception->getMessage()], 500);
        }
        return response()->json(['message' => 'Parking added successfully',
            new ParkingsResource($parking), 201]);
    }

    public function updateParkingInfo(Request $request,$id): \Illuminate\Http\JsonResponse
    {

        $parking = parkings::find($id);

        try {
            //$garage = garages::find($id);
            $parking->number = $request->number ?? $parking->number;
            $parking->status_id = $request->status_id ?? $parking->status_id;
            $parking->floors_id = $request->floors_id ?? $parking->floors_id;
            $parking->save();
        } catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }
        return response()->json([
            'message' => 'Account updated successfully', new ParkingsResource($parking)]);
    }

    public function removeParking($parking_id)
    {
        $garage = garages::find(Auth::guard('garage')->id());


        $parking = parkings::find($parking_id);
        if (!$parking) {
            return response()->json(['message' => 'Attribute not found'], 404);
        }


//        foreach ($garage->floors as $floor) {
//            foreach ($floor->pakings as $parkings) {
//                if($parkings->id()==$parking_id)
//                    $floor->parkings()->detach($parking_id);
//            }
//            }

        $parking->delete();

        return response()->json(['message' => 'Attribute removed from account successfully'], 200);
    }


}
