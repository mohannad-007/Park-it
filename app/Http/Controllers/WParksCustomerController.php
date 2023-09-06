<?php

namespace App\Http\Controllers;

use App\Models\cars;
use App\Models\customer_subscriptions;
use App\Models\floors;
use App\Models\garage_employees;
use App\Models\garages;
use App\Models\parkings;
use App\Models\reservations;
use App\Models\user_subscriptions;
use App\Models\w_parks;
use App\Models\w_parks_customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WParksCustomerController extends Controller
{
    public function wParkCustomer(Request $request)
    {
        $garage_employees = garage_employees::find(Auth::guard('garage_employee')->id());
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'time_begin' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_begin',
            'date' => 'required|date_format:Y-m-d',
            'parking_id' => 'required|exists:parkings,id',
            'floor_id' => 'required|exists:floors,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Extract the input data from the request

        $customer = $request->input('customer_id');
        $timeBegin = $request->input('time_begin');
        $timeEnd = $request->input('time_end');
        $reservationDate = $request->input('date');
        $parkingId = $request->input('parking_id');
        $floorId = $request->input('floor_id');

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
        $customerSubscription = customer_subscriptions::where('customer_id', $customer)
            ->where('garage_subscriptions_id', $garage_employees->garage_id)
            ->where('start_date_sub', '<=', $reservationDate)
            ->where('end_date_sub', '>=', $reservationDate)
            ->get();

        // If the user has a subscription, the reservation is free
        if ($customerSubscription) {
            $totalPrice = 0;
        }

        // Proceed with the reservation
        DB::beginTransaction();
        try {

            // Create the reservation
            $reservation = w_parks_customer::create([
                'date' => $reservationDate,
                'time_begin' =>  $timeBegin,
                'time_end' => $timeEnd,
                'time_reservation' => $timeReservationFormatted,
                'price' => $totalPrice,
                'pay'=>true,
                'customer_id' => $customer,
                'parking_id' => $parkingId,
                'floor_id' => $floorId,
                'garage_id' => $garage_employees->garage_id,
            ]);
            $parking->update ([
               'status_id' => 2,
            ]);

            $reservation = $reservation->with('customers','parkings.floors.garages')->first();

            DB::commit();

            return response()->json(['message' => 'Reservation successful.',
                'reservation' => $reservation,
//                'customer'=> $customer,
//                'garage'=> $garage,
//                'floor'=> $floor,
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
