<?php

namespace App\Http\Controllers;

use App\Http\Resources\Floors as FloorsResource;
use App\Http\Resources\Garages as GaragesResources;
use App\Models\floors;
use App\Models\garage_location;
use App\Models\garages;
use App\Models\parkings;
use App\Models\reservations;
use App\Models\status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GaragesController extends Controller
{

    public function getAccountInfo()
    {
        $garage = garages::find(Auth::guard('garage')->id());

        try {
            $garage->makeHidden(['password', 'garage_locations_id']); // تمرير اسم العلاقة لتخفيها
            return response()->json($garage);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()]);
        }
    }




    public function updateAccountInfo(Request $request): \Illuminate\Http\JsonResponse
    {
        $garage = garages::find(Auth::guard('garage')->id());
        //$garage = garages::find($id);
        try {
            //$garage = garages::find($id);
            $garage->name = $request->name ?? $garage->name;
            $garage->email = $request->email ?? $garage->email;
            $garage->password = bcrypt($request->password) ?? $garage->password;
            // $garage->floor_number = $request->floor_number ?? $garage->floor_number;
             $garage->is_open = $request->is_open ?? $garage->is_open;
            $garage->price_per_hour = $request->price_per_hour ?? $garage->price_per_hour;
            //$garage->parks_number = $request->parks_number ?? $garage->parks_number;
            $garage->time_open = $request->time_open ?? $garage->time_open;
            $garage->time_close = $request->time_close ?? $garage->time_close;
            $garage->garage_information = $request->garage_information ?? $garage->garage_information;

//            if($request->parks_number) {
//                $newNumberOfRows = $request->parks_number;
//                if ($newNumberOfRows > $garage->parks_number) {
//                    $numberOfNewRows = $newNumberOfRows - $garage->parks_number;
//                    $state = status::find(1);
//                    for ($i = 1; $i <= $numberOfNewRows; $i++) {
//                        $newRow = new parkings([
//                            'number' => ($garage->parks_number + $i),
//                            //'number_of_parking_spaces' => $garage->number_of_parking_spaces,
//                            'floors_id' => null,
//                            'status_id' => $state,
//                            'garage_id' => $garage->id,
//                        ]);
//                        $newRow->save();
//                    }
//                }
//
//            }

            $garage->parks_number = $request->parks_number ?? $garage->parks_number;

//            if($request->floor_number)
//            {
//                $newNumberOfFloors = $request->floor_number;
//                if ($newNumberOfFloors > $garage->floor_number) {
//                    $numberOfNewFloors = $newNumberOfFloors - $garage->floor_number;
//                    for ($i = 1; $i <= $numberOfNewFloors; $i++) {
//                        $newfloor = new floors([
//                            'floors' => ($garage->floor_number + $i),
//                            'garage_id' => $garage->id,
//                        ]);
//                        $newfloor->save();
//                    }
//                }
//
//
//            }
            $garage->floor_number = $request->floor_number ?? $garage->floor_number;
            $garage->save();
        } catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }
        return response()->json([
            'message' => 'Account updated successfully', new GaragesResources($garage)]);
    }

    public function open_close(Request $request)
    {
        $garage = garages::find(Auth::guard('garage')->id());
        $garage->is_open = $request->is_open ?? $garage->is_open;
        $garage->save();
        if($garage->is_open==1)
            return Response()->json(['message' => "garage opened"]);
        else
            return Response()->json(['message' => "garage closed"]);
    }

    public function getAllFloorsAndAttributes()
    {
        $garageId = garages::where('id', '=', Auth::guard('garage')->id())->value('id');
        $floors = floors::with('parkings')
            ->where('garage_id', $garageId)
            ->orderBy('id')
            ->get();

        $resource = new FloorsResource($floors);

        $this->collection = $floors;

        //return $resource->toArray(request());
        return response()->json([
            $resource->toArray(request())
        ]);
    }

    public function showAccountUser($id)
    {
        // $garage = garages::find(Auth::guard('garage')->id());

        $user = user::findOrFail($id);

        return response()->json($user);
    }

    public function showReservationsOnMyGarage()
    {
        $garageId = garages::where('id', '=', Auth::guard('garage')->id())->value('id');
        $reservations = reservations::whereHas('garages', function ($query) use ($garageId) {
            $query->where('garage_id', '=', $garageId);
        })->get();

        return response()->json($reservations);
    }


    public function search(Request $request)
    {
        $query = $request->get('query');

        // Perform the search using the 'name' column
        $garages = garages::where('name', 'LIKE', '%' . $query . '%')->get();
        if ($garages->isEmpty()) {
            return response()->json(['message' => 'No employees found with this name in this garage'], 404);
        }
        // Return the search results
        return response()->json([
            'results' => $garages,
        ], 200);
    }


//    public function availableGarages()
//    {
//        $availableGarages = garages::where('is_open', 1)->with('floors.parkings.status')->get();
//
//        // Filter garages with available parking
//        $garagesWithAvailableParking = $availableGarages->filter(function ($garage) {
//            foreach ($garage->floors as $floor) {
//                foreach ($floor->parkings as $parking) {
//                    if ($parking->status->name === 'available') {
//                        return true;
//                    }
//                }
//            }
//            return false;
//        });
//
//        return response()->json(['available_garages' => $garagesWithAvailableParking], 200);
//    }

    public function availableAndNonAvailabelGarages()
    {
        $availableGarages = garages::with('floors.parkings.status')->get();
        $garage_location = garages::with('garage_location')->get();
        // Separate available and non-available garages
        $availableGaragesArray = [];
        $nonAvailableGaragesArray = [];

        foreach ($availableGarages as $garage) {
            $hasAvailableParking = false;
            foreach ($garage->floors as $floor) {
                foreach ($floor->parkings as $parking) {
                    if ($parking->status->name === 'available') {
                        $hasAvailableParking = true;
                        break; // No need to check other parkings in this garage
                    }
                }
                if ($hasAvailableParking) {
                    break; // No need to check other floors in this garage
                }
            }

            $garageData = $garage->toArray();

            // Fetch and attach the garage_location information
            $garageLocation = garage_location::find($garage->garage_locations_id);
            $garageData['garage_location'] = $garageLocation;

            // Add the garage to the corresponding array based on availability
            if ($hasAvailableParking) {
//                $garageData['garage_location_id'] = $garage->garage_location;
//                $availableGaragesArray[] = $garage;
                $availableGaragesArray[] = $garageData;
            } else {
//                $garage->garage_location_id = $garage;
//                $nonAvailableGaragesArray[] = $garage;
                $nonAvailableGaragesArray[] = $garageData;
            }
        }

        return response()->json([
            'available_garages' => $availableGaragesArray,
            'non_available_garages' => $nonAvailableGaragesArray,
//            'garage_location' => $garage_location,
        ], 200);
    }

    /**
     * Display a listing of the resource.
     */
//    public function index()
//    {
//        $garages = garages::all();
//            return response()->json([
//                'garages' => $garages,
//            ], 200);
//    }


    /**
     * Display the specified resource.
     */
    public function show($garage_id)
    {
        $garage = garages::with('garage_location')->find($garage_id);

        if (!$garage) {
            return response()->json(['message' => 'Garage not found.'], 404);
        }

        return response()->json(['garage' => $garage], 200);

    }

    public function getAllGarages()
    {
        $garages = garages::get();

        $garages->each(function ($garage) {
            $garage->makeHidden(['password', 'garage_locations_id']); // تمرير اسم العلاقة لتخفيها
        });

        return response()->json($garages);
    }





}
