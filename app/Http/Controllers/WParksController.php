<?php

namespace App\Http\Controllers;

use App\Models\active_users;
use App\Models\cars;
use App\Models\floors;
use App\Models\garage_employees;
use App\Models\garages;
use App\Models\parkings;
use App\Models\reservations;
use App\Models\User;
use App\Models\user_subscriptions;
use App\Models\w_parks;
use App\Models\w_parks_customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WParksController extends Controller
{

    public function getWParksForUser()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Query the WPark model to get the reservations for the user
        $wParks = w_parks::where('user_id', $user->id)->first();
//        $wParks = w_parks::where('user_id', $user->id)->get();
        if (!$wParks){
            return response()->json(['message' => 'no w-parks']);
        }

//        $parking = w_parks::where('parking_id',$wParks->parkings->parking_id);
//        $parkInfo=$parking->parkings->parking_id;
//        $garage = garages::find($garageId);
        // Return the reservations as a JSON response
        return response()->json(['w_parks' => $wParks , 'user'=>$user]);
    }

    public function getWParksForGarage($garageId)
    {
        $garage = garages::with('w_parks')->find($garageId);
        if (!$garage) {
            return response()->json(['message' => 'Garage not found'], 404);
        }
        $garageName = $garage->name;
        $wParks = $garage->w_parks;
        return response()->json([
            'garage_name' => $garageName,
            'w_parks' => $wParks,
        ]);
    }

    public function getActiveUserForGarage($garageId)
    {
        $garage = garages::with('active_users.cars.user')->find($garageId);
        if (!$garage) {
            // Handle the case when the garage is not found
            return response()->json(['message' => 'Garage not found'], 404);
        }

        $garageinfo = $garage;

//        $active_users = $garage->active_users;
//        $u = active_users::with('users')->find($active_users);
//        $user = $u->users;
//        return $user;
        return response()->json([
            'garage_info' => $garageinfo,
//            'active_user' => $active_users,
        ]);
    }















    public function wParkuser(Request $request)
    {

        $garage_employees = garage_employees::find(Auth::guard('garage_employee')->id());
//        $userId, $reservationId
        $userId = $request->user_id;
        $user = User::find($userId);

        // Fetch the existing reservation if it belongs to the user
        $reservation = reservations::where('user_id', $userId)
            ->find($request->reservation_id);

        if ($reservation){
            $parking = parkings::find($reservation->parking_id);
            $wpark = w_parks::create([
                'date' => $reservation->date,
                'time_begin' => $reservation->time_begin,
                'time_end' => $reservation->time_end,
                'time_reservation' => $reservation->time_reservation,
                'price' => 0,
                'pay' => $reservation->pay,
                'user_id' => $reservation->user_id,
                'parking_id' => $reservation->parking_id,
                'garage_id' => $reservation->garage_id,
                'floor_id' => $reservation->floor_id,
                'car_id' => $reservation->car_id,
            ]);

            $parking->update ([
                'status_id' => 2,
            ]);

            $wpark =  $wpark->with('user','cars','parkings.floors.garages')->get();
            return response()->json([
                'w-park-user-with-reservation' => $wpark,
            ], 200);

        }
//    return  'im here';

//         Validate the request data
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'time_begin' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_begin',
            'parking_id' => 'required|exists:parkings,id',
            'floor_id' => 'required|exists:floors,id',
//            'garage_id' => 'required|exists:garages,id',
            'car_id' => 'required|exists:cars,id',
        ]);
//
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

//         Extract the input data from the request
        $user2 = $request->input('user_id');
        $timeBegin = $request->input('time_begin');
        $timeEnd = $request->input('time_end');
        $reservationDate = $request->input('date');
        $parkingId = $request->input('parking_id');
        $floorId = $request->input('floor_id');
//        $garageId = $request->input('garage_id');
        $carId = $request->input('car_id');

        // Fetch the parking space
        $parking = parkings::find($parkingId);

        // Check if the parking space exists
        if (!$parking) {
            return response()->json(['message' => 'Parking space not found.'], 404);
        }

        // Check the parking status
        $parkingStatus = $parking->status->name;
//        return $parkingStatus;
        if ($parkingStatus !== 'available') {
            return response()->json(['message' => 'Parking space is not available for reservation.'], 400);
        }

        // Check if the parking space is available for the specified time and date
        $isAvailable = $this->checkParkingAvailability($parkingId, $timeBegin, $timeEnd, $reservationDate);
        if (!$isAvailable) {
            return response()->json(['message' => '1 Parking space not available for the specified time and date.'], 400);
        }

        $isAvailable3 = $this->checkParkingAvailability3($parkingId, $timeBegin, $timeEnd, $reservationDate);
        if (!$isAvailable3) {
            return response()->json(['message' => '3 Parking space not available for the specified time and date.'], 400);
        }

        $isAvailable4 = $this->checkParkingAvailability4($parkingId, $timeBegin, $timeEnd, $reservationDate);
        if (!$isAvailable4) {
            return response()->json(['message' => '4 Parking space not available for the specified time and date.'], 400);
        }

        // Calculate the total price based on the time duration and garage price_per_hour
//        $garage = garages::find($garageId);
        $garage = garages::find($garage_employees->garage_id);
        $startTimeObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeBegin");
        $endTimeObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeEnd");



        $hoursReserved = $startTimeObj->diffInHours($endTimeObj);
//        $hoursReserved2 = $startTimeObj->diffInMinutes($endTimeObj);
        $minutesReserved = $startTimeObj->diffInMinutes($endTimeObj) % 60;
//        $totalPrice = $hoursReserved * $garage->price_per_hour;
        $timeReservationFormatted = sprintf('%02d:%02d', $hoursReserved, $minutesReserved);

        $totalPrice = ($hoursReserved * 60 + $minutesReserved) * ($garage->price_per_hour / 60);

        // Check if the user has a subscription for the garage
        $userSubscription = user_subscriptions::where('user_id', $userId)
            ->where('garage_subscriptions_id', $garage_employees->garage_id)
            ->where('start_date_sub', '<=', $reservationDate)
            ->where('end_date_sub', '>=', $reservationDate)
            ->first();

        // If the user has a subscription, the reservation is free
        if ($userSubscription) {
            $totalPrice = 0;
        }

        // Check if the user has enough funds in their wallet
//        $userWallet = $user->walletes;
//        if (!$userWallet || $userWallet->price < $totalPrice) {
//            return response()->json(['message' => 'Insufficient funds in the wallet.'], 400);
//        }


//        $car = cars::find($carId);
//        $floor = floors::find($floorId);

        // Proceed with the reservation
        DB::beginTransaction();
        try {
//            if (!$userSubscription) {
//                $userWallet->update(['price' => $userWallet->price - $totalPrice]);
//            }
            // Create the reservation
            $wpark2 = w_parks::create([
                'date' => $reservationDate,
                'time_begin' =>  $timeBegin,
                'time_end' => $timeEnd,
                'time_reservation' => $timeReservationFormatted,
                'price' => $totalPrice,
                'pay'=>true,
                'user_id' => $user->id,
                'parking_id' => $parkingId,
                'car_id' => $carId,
                'floor_id' => $floorId,
                'garage_id' => $garage_employees->garage_id,
            ]);
            $parking->update ([
                'status_id' => 2,
            ]);

            $wpark2 = $wpark2->with('user','cars','parkings.floors.garages')->first();


//            $reservation->barcode = $this->MakeReservationBarcode($reservation->id,$user->id);
//            $barcode= $reservation->barcode;
//            $reservation->save();

            // Deduct the amount from the user's wallet

            DB::commit();

            return response()->json(['message' => 'w-park-user successful.',
                'w-park-user-without-reservation' => $wpark2 ,
//                'user'=> $user,
//                'garage'=> $garage,
//                'floor'=> $floor,
//                'car'=> $car,
//                'parking'=> $parking,
//                'barcode'=>$barcode,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    //reservation table
    private function checkParkingAvailability($parkingId, $timeBegin, $timeEnd, $reservationDate)
    {
        $timeBeginObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeBegin");
        $timeEndObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeEnd");

        $existingReservations = reservations::where('parking_id', $parkingId)
            ->where('date', $reservationDate)
            ->where(function ($query) use ($timeBeginObj, $timeEndObj) {
                $query->where(function ($query) use ($timeBeginObj, $timeEndObj) {
                    $query->where('time_begin', '>=', $timeBeginObj)
                        ->where('time_begin', '<', $timeEndObj);
                })
                    ->orWhere(function ($query) use ($timeBeginObj, $timeEndObj) {
                        $query->where('time_end', '>', $timeBeginObj)
                            ->where('time_end', '<=', $timeEndObj);
                    })
                    ->orWhere(function ($query) use ($timeBeginObj, $timeEndObj) {
                        $query->where('time_begin', '<', $timeBeginObj)
                            ->where('time_end', '>', $timeEndObj);
                    });
            })->count();

        return $existingReservations === 0;
    }

    //w-park table
    private function checkParkingAvailability3($parkingId, $timeBegin, $timeEnd, $reservationDate)
    {
        $timeBeginObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeBegin");
        $timeEndObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeEnd");

        $existingReservations = w_parks::where('parking_id', $parkingId)
            ->where('date', $reservationDate)
            ->where(function ($query) use ($timeBeginObj, $timeEndObj) {
                $query->where(function ($query) use ($timeBeginObj, $timeEndObj) {
                    $query->where('time_begin', '>=', $timeBeginObj)
                        ->where('time_begin', '<', $timeEndObj);
                })
                    ->orWhere(function ($query) use ($timeBeginObj, $timeEndObj) {
                        $query->where('time_end', '>', $timeBeginObj)
                            ->where('time_end', '<=', $timeEndObj);
                    })
                    ->orWhere(function ($query) use ($timeBeginObj, $timeEndObj) {
                        $query->where('time_begin', '<', $timeBeginObj)
                            ->where('time_end', '>', $timeEndObj);
                    });
            })->count();

        return $existingReservations === 0;
    }

    //w-park-customer table
    private function checkParkingAvailability4($parkingId, $timeBegin, $timeEnd, $reservationDate)
    {
        $timeBeginObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeBegin");
        $timeEndObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeEnd");

        $existingReservations = w_parks_customer::where('parking_id', $parkingId)
            ->where('date', $reservationDate)
            ->where(function ($query) use ($timeBeginObj, $timeEndObj) {
                $query->where(function ($query) use ($timeBeginObj, $timeEndObj) {
                    $query->where('time_begin', '>=', $timeBeginObj)
                        ->where('time_begin', '<', $timeEndObj);
                })
                    ->orWhere(function ($query) use ($timeBeginObj, $timeEndObj) {
                        $query->where('time_end', '>', $timeBeginObj)
                            ->where('time_end', '<=', $timeEndObj);
                    })
                    ->orWhere(function ($query) use ($timeBeginObj, $timeEndObj) {
                        $query->where('time_begin', '<', $timeBeginObj)
                            ->where('time_end', '>', $timeEndObj);
                    });
            })->count();

        return $existingReservations === 0;
    }





}
