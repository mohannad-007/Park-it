<?php

namespace App\Http\Controllers;

use App\Models\cars;
use App\Models\floors;
use App\Models\garages;
use App\Models\parkings;
use App\Models\reservations;
use App\Models\User;
use App\Models\user_subscriptions;
use App\Models\w_invoices;
use App\Models\w_parks;
use App\Models\w_parks_customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Picqer\Barcode\BarcodeGeneratorJPG;



class ReservationsController extends Controller
{


    function MakeReservationBarcode(int $reservation,int $user)
    {

        try {
            $generator = new BarcodeGeneratorJPG();

            // $barcode_value = $request->car_id . '-' . $request->user_id;
            $barcode_value = $reservation . '-' . $user;
            $barcode = $generator->getBarcode($barcode_value, $generator::TYPE_CODE_128);
            $filename = $barcode_value . 'barcode.jpg';

            file_put_contents(' barcode-images', $filename);
            $barcode_path = public_path('barcode-images/' . $filename);
            file_put_contents($barcode_path, $barcode);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()]);
        }
        return url('barcode-images/' . $filename);
    }


    public function reserveParking(Request $request)
    {
        $user = Auth::user()->load('walletes');
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'time_begin' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_begin',
            'parking_id' => 'required|exists:parkings,id',
            'date' => 'required|date_format:Y-m-d',
            'floor_id' => 'required|exists:floors,id',
            'garage_id' => 'required|exists:garages,id',
            'car_id' => 'required|exists:cars,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Extract the input data from the request
        $timeBegin = $request->input('time_begin');
        $timeEnd = $request->input('time_end');
        $reservationDate = $request->input('date');
        $parkingId = $request->input('parking_id');
        $floorId = $request->input('floor_id');
        $garageId = $request->input('garage_id');
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
        $garage = garages::find($garageId);
        $startTimeObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeBegin");
        $endTimeObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeEnd");



        $hoursReserved = $startTimeObj->diffInHours($endTimeObj);
//        $hoursReserved2 = $startTimeObj->diffInMinutes($endTimeObj);
        $minutesReserved = $startTimeObj->diffInMinutes($endTimeObj) % 60;
//        $totalPrice = $hoursReserved * $garage->price_per_hour;
        $timeReservationFormatted = sprintf('%02d:%02d', $hoursReserved, $minutesReserved);

        $totalPrice = ($hoursReserved * 60 + $minutesReserved) * ($garage->price_per_hour / 60);

        // Check if the user has a subscription for the garage
        $userSubscription = user_subscriptions::where('user_id', $user->id)
            ->where('garage_subscriptions_id', $garageId)
            ->where('start_date_sub', '<=', $reservationDate)
            ->where('end_date_sub', '>=', $reservationDate)
            ->first();

        // If the user has a subscription, the reservation is free
        if ($userSubscription) {
            $totalPrice = 0;
        }

        // Check if the user has enough funds in their wallet
        $userWallet = $user->walletes;
        if (!$userWallet || $userWallet->price < $totalPrice) {
            return response()->json(['message' => 'Insufficient funds in the wallet.'], 400);
        }
        $car = cars::find($carId);
        $floor = floors::find($floorId);

        // Proceed with the reservation
        DB::beginTransaction();
        try {
            if (!$userSubscription) {
                $userWallet->update(['price' => $userWallet->price - $totalPrice]);
            }
            // Create the reservation
            $reservation = reservations::create([
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
                'garage_id' => $garageId,
            ]);

//            $reservation = $reservation->with('user','cars','parkings.floors.garages')->first();


            $reservation->barcode = $this->MakeReservationBarcode($reservation->id,$user->id);
            $barcode= $reservation->barcode;
            $reservation->save();

            // Deduct the amount from the user's wallet

            DB::commit();

            $user_invoices = new w_invoices();
          //  $user = User::find($userId);
            // $user_invoices->user_name = $user->name;
            $user_invoices->user_id = $user->id;
            $user_invoices->date = \Illuminate\Support\Carbon::today();
            $user_invoices->duration = $hoursReserved;
            $user_invoices->money = $totalPrice;
            $user_invoices->save();

            return response()->json(['message' => 'Reservation successful.',
                'reservation' => $reservation ,
                'user'=> $user,
                'garage'=> $garage,
                'floor'=> $floor,
                'car'=> $car,
                'parking'=> $parking,
                'barcode'=>$barcode,
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






    public function updateReservation(Request $request, $reservationId)
    {
        $user = Auth::user()->load('walletes');
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'time_begin' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_begin',
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Extract the input data from the request
        $timeBegin = $request->input('time_begin');
        $timeEnd = $request->input('time_end');
        $reservationDate = $request->input('date');

        // Fetch the existing reservation
        $reservation = reservations::find($reservationId);

        // Check if the reservation exists
        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found.'], 404);
        }

        // Check if the reservation is available for modification (not expired)
        $currentDateTime = Carbon::now();
        $reservationDateTime = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeBegin");

        if ($reservationDateTime <= $currentDateTime) {
            return response()->json(['message' => 'Reservation cannot be modified as it has already started or expired.'], 400);
        }

        // Check if the parking space is available for the specified time and date
        $isAvailable = $this->checkParkingAvailability2($reservation->parking_id, $timeBegin, $timeEnd, $reservationDate,$reservationId);

        if (!$isAvailable) {
            return response()->json(['message' => 'Parking space not available for the specified time and date.'], 400);
        }

        // Calculate the total price based on the time duration and garage price_per_hour
        $garage = garages::find($reservation->garage_id);
        $startTimeObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeBegin");
        $endTimeObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeEnd");

        $hoursReserved = $startTimeObj->diffInHours($endTimeObj);
        $minutesReserved = $startTimeObj->diffInMinutes($endTimeObj) % 60;
        $timeReservationFormatted = sprintf('%02d:%02d', $hoursReserved, $minutesReserved);

        $totalPrice = ($hoursReserved * 60 + $minutesReserved) * ($garage->price_per_hour / 60);

        // Calculate the price difference and check if the user has enough funds in their wallet
        $priceDifference = $totalPrice - $reservation->price;
        $userWallet = $user->walletes;

        if (!$userWallet || $userWallet->price < $priceDifference) {
            return response()->json(['message' => 'Insufficient funds in the wallet.'], 400);
        }

        $barcode = $this->MakeReservationBarcode($reservation->id,$user->id);


        // Update the reservation with the new data
        $reservation->update([
            'date' => $reservationDate,
            'time_begin' =>  $timeBegin,
            'time_end' => $timeEnd,
            'time_reservation' => $timeReservationFormatted,
            'price' => $totalPrice,
            'barcode'=>$barcode,
        ]);

        // Deduct the price difference from the user's wallet
        $userWallet->update(['price' => $userWallet->price - $priceDifference]);


        return response()->json([
            'message' => 'Reservation updated successfully.',
            'reservation' => $reservation,
            'user'=> $user,
            'garage'=> $garage,
            ], 200);
    }

    //update reservation checked
    private function checkParkingAvailability2($parkingId, $timeBegin, $timeEnd, $reservationDate, $currentReservationId)
    {
        $timeBeginObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeBegin");
        $timeEndObj = Carbon::createFromFormat('Y-m-d H:i', "$reservationDate $timeEnd");

        // Check if any existing reservations overlap with the requested time
        $existingReservations = reservations::where('parking_id', $parkingId)
            ->where('date', $reservationDate)
            ->where('id', '!=', $currentReservationId) // Exclude the current reservation from the check
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
            })->get();

        // Check if any existing reservation starts after the requested time (after 12:00)
        $reservationAfterRequestedTime = $existingReservations->first(function ($reservation) use ($timeBeginObj) {
            return $reservation->time_begin > $timeBeginObj;
        });

        // Check if any existing reservation ends before the requested time (before 10:00)
        $reservationBeforeRequestedTime = $existingReservations->first(function ($reservation) use ($timeEndObj) {
            return $reservation->time_end < $timeEndObj;
        });

        // If no overlapping reservation and no reservation starting after the requested time or ending before it, then the space is available
        return !$reservationAfterRequestedTime && !$reservationBeforeRequestedTime;
    }


    public function deleteReservation($reservationId)
    {
        $reservation = reservations::find($reservationId);

        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found.'], 404);
        }

        $user = $reservation->user;

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $userWallet = $user->walletes; // احتفظ بكائن المحفظة بدلاً من قيمة السعر فقط
        $reservatioprice = $reservation->price;

        // قم بتحديث قيمة المحفظة بإضافة قيمة الحجز
        $userWallet->update(['price' => $userWallet->price + $reservatioprice]);

        // قم بحذف الحجز بعد تحديث المحفظة
        $reservation->forceDelete();

        return response()->json(['message' => 'Reservation deleted successfully.'], 200);
    }



//    public function getUserReservations($userId)
//    {
//        // استخدم طريقة where للبحث عن جميع حجوزات المستخدم المحدد بناءً على معرفه
//        $reservations = reservations::with('user','cars','parkings.floors.garages')->where('user_id', $userId)->get();
////        $reservations2 = reservations::with('parkings.floors.garages')->where('user_id', $userId)->get();
//
//        return response()->json([
//            'reservations' => $reservations,
////            'reservations2' => $reservations2,
//        ], 200);
//    }

    public function getUserReservations($userId)
    {
        $now = now();

        // استخدم طريقة where للبحث عن جميع حجوزات المستخدم المحدد بناءً على معرفه
        $reservations = reservations::with('user','cars')
            ->where('user_id', $userId)
            ->orderBy('date', 'asc')
            ->orderBy('time_begin', 'asc')
            ->get();

        // Loop through the reservations and update as needed
        foreach ($reservations as $reservation) {
            if ($reservation->date < $now->toDateString()) {
                // Reservation date is in the past, mark as deleted
                $reservation->delete();
            } elseif ($reservation->date == $now->toDateString() && $reservation->time_end < $now->toTimeString()) {
                // Reservation date is today and end time has passed, mark as deleted
                $reservation->delete();
            }
        }

        // Filter out the deleted reservations
        $reservations = $reservations->filter(function ($reservation) {
            return !$reservation->trashed();
        });

        $reservations2 = reservations::with('parkings.floors.garages')
            ->where('user_id', $userId)
            ->orderBy('date', 'asc')
            ->orderBy('time_begin', 'asc')
            ->get();

        $reservations2 = $reservations2->filter(function ($reservations) {
            return !$reservations->trashed();
        });

        return response()->json([
            'reservations' => $reservations,
            'reservations2' => $reservations2,
        ], 200);
    }

    public function getArchivedReservationsForUser($userId) //الحجوزات المأرشفة او القديمة
    {
        $archivedReservations = reservations::onlyTrashed()
            ->with('user','cars')
            ->where('user_id', $userId)
            ->get();

        $archivedReservations2 = reservations::onlyTrashed()
            ->with('parkings.floors.garages')
            ->where('user_id', $userId)
            ->get();
        return response()->json([
            'archived_reservations' => $archivedReservations,
            'archived_reservations2' => $archivedReservations2,
        ], 200);
    }


}
