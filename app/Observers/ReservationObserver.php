<?php

namespace App\Observers;

use App\Models\reservations;
use Carbon\Carbon;

class ReservationObserver
{

    public function deleted(reservations $reservation)
    {
//        // Get the current time
//        $currentTime = Carbon::now();
//        // Compare the current time with the end time of the reservation
//        $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $reservation->date . ' ' . $reservation->time_end);
//        if ($currentTime >= $endTime) {
//            // Set the deleted_at field to the current time to perform soft delete
//            $reservation->deleted_at = $currentTime;
//        }

//        $reservation=$reservation::query()->find(6);
//        // Get the current time
//        $currentTime = Carbon::now();
////        return $currentTime;
//        // Compare the current time with the end time of the reservation
////        $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $reservation->date . ' ' . $reservation->time_end);
//        $endTimeObj = Carbon::createFromFormat('Y-m-d H:i', "$reservation->date $reservation->time_end");
////        return $endTimeObj;
//        if ($currentTime >= $endTimeObj) {
//            // Set the deleted_at field to the current time to perform soft delete
//            $reservation->deleted_at = $currentTime;
//            $reservation->save();
//        }



//        $reservation=reservations::query()->where('deleted_at','=',null)->get();
//
////        return $reservation;
//        // Get the current time
//        $currentTime = Carbon::now();
////        return $currentTime;
//        // Compare the current time with the end time of the reservation
//        $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $reservation->date . $reservation->time_end);
//        return $endTime;
////        $endTimeObj = Carbon::createFromFormat('Y-m-d H:i', "$reservation->date $reservation->time_end");
////        return $endTimeObj;
//        if ($currentTime >= $endTime) {
//            // Set the deleted_at field to the current time to perform soft delete
//            $reservation->deleted_at = $currentTime;
//            $reservation->save();
//        }


    }



    /**
     * Handle the reservations "created" event.
     */
    public function created(reservations $reservations): void
    {
        //
    }

    /**
     * Handle the reservations "updated" event.
     */
    public function updated(reservations $reservations): void
    {

    }

    /**
     * Handle the reservations "deleted" event.
     */
//    public function deleted(reservations $reservations): void
//    {
////        // Get the current time
////        $currentTime = Carbon::now();
////        // Compare the current time with the end time of the reservation
////        $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $reservations->date . ' ' . $reservations->time_end);
////        if ($currentTime >= $endTime) {
////            // Set the deleted_at field to the current time to perform soft delete
////            $reservations->deleted_at = $currentTime;
////        }
//    }

    /**
     * Handle the reservations "restored" event.
     */
    public function restored(reservations $reservations): void
    {
        //
    }

    /**
     * Handle the reservations "force deleted" event.
     */
    public function forceDeleted(reservations $reservations): void
    {
        //
    }
}
