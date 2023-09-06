<?php

namespace App\Http\Controllers;



use App\Models\active_customers;
use App\Models\active_users;
use App\Models\customers;
use App\Models\garage_employees;
use App\Models\garages;
use App\Models\parkings;
use App\Models\required_serv_cus;
use App\Models\required_services;
use App\Models\services;
use App\Models\status;
use App\Models\User;
use App\Models\w_customer_invoices;
use App\Models\w_invoices;
use App\Models\w_parks;
use App\Models\w_parks_customer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActiveUsersController extends Controller
{

    public function userEntry(Request $request)
    {
        $employee = garage_employees::find(Auth::guard('garage_employee')->id());
        $garage = $employee->garage_id;

        $userCarId = $request->car_id;

        $existingEntry = active_users::where('car_id', $userCarId)
            ->where('garage_id', $garage)
            ->first();

        if ($existingEntry) {
            return response()->json(['message' => 'السيارة موجودة بالفعل في الكراج.']);
        }


        $activeUser = new active_users();
        $activeUser->user_id = $request->user_id;
        $activeUser->car_id = $userCarId;
        $activeUser->garage_id = $garage;
        $activeUser->entry_time = Carbon::now();
        $activeUser->entry_date = Carbon::today();
        $activeUser->save();

        return response()->json(['message' => 'تم دخول السيارة بنجاح.']);
    }


    public function customerEntry(Request $request)
    {
        $employee = garage_employees::find(Auth::guard('garage_employee')->id());
        $garage = $employee->garage_id;

        $customerId = $request->customer_id;


        $existingEntry = active_customers::where('customer_id', $customerId)
            ->where('garage_id', $garage)
            ->first();

        if ($existingEntry) {
            return response()->json(['message' => ' الزبون موجود بالفعل بالكراج.']);
        }


        $activeCustomer = new active_customers();
        $activeCustomer->customer_id = $request->customer_id;
        $activeCustomer->garage_id = $garage;
        $activeCustomer->entry_time = Carbon::now();
        $activeCustomer->entry_date = Carbon::today();
        $activeCustomer->save();

        return response()->json(['message' => 'تم دخول الزبون بنجاح.']);
    }





    public function exitUser(Request $request)
    {
        $employee = garage_employees::find(Auth::guard('garage_employee')->id());

        $garage=$employee->garage_id;
        $userId = $request->user_id;
        $carId = $request->car_id;
        $date=Carbon::today();


        $entry = active_users::where('user_id', $userId)
            ->where('car_id', $carId)
            ->where('garage_id', $garage)
            ->orderBy('entry_time', 'desc')
            ->first();

        if (!$entry) {
            return response()->json(['message' => 'لم يتم العثور على سجل دخول السيارة.'], 404);
        }
        try {


            $exitTime = Carbon::now();
            // $exitTime = Carbon::now()->setTime(8, 0, 0);
            $entryTime = Carbon::parse($entry->entry_time);


            $parkingDurationInHours = ceil($exitTime->diffInHours($entryTime));


            $garage = garages::find($garage);
            $hourlyRate = $garage->price_per_hour;


            $w_park = w_parks::where('car_id', $carId)
                ->where('garage_id', $garage->id)
                //->where('time_end', '>=', $exitTime)
                //->where('date', $date)
                ->first();

            $parkingCost = 0;

            if ($w_park) {
                if ($exitTime->lessThanOrEqualTo($w_park->time_end)) {


                    $parkingCost = $w_park->price;

                } elseif ($exitTime->greaterThan($w_park->time_end)) {

                    $diff = $exitTime->diff($w_park->time_end);
                    // $extraHours = $exitTime->diffInHours($w_parks->time_end);
                    $extraMinutes = $diff->i;
                    $extraHours = $diff->h;

                    if ($extraMinutes > 10) {
                        $extraHours += 1;
                    }

                    $parkingCost = $w_park->price + ($hourlyRate * $extraHours);

                }

                $newStatus = Status::find(1);
                $park_id = $w_park->parking_id;
                $park = Parkings::find($park_id);
                if ($newStatus) {
                    $park->status_id = $newStatus->id;
                    $park->save();
                    $w_park->delete();
                }


            }

            $totalPrice = required_services::where('user_id', $userId)
                ->where('done', 1)
                ->join('services', 'required_services.services_id', '=', 'services.id')
                ->sum('services.price');

            required_services::where('user_id', $userId)
                ->where('done', 1)
                ->delete();

            $parkingCost = $parkingCost + $totalPrice;
            $parkingCost2 = ($parkingDurationInHours * $hourlyRate) + $totalPrice;
            $entry->delete();


            $user_invoices = new w_invoices();
            $user = User::find($userId);
            // $user_invoices->user_name = $user->name;
            $user_invoices->user_id = $userId;
            $user_invoices->date = Carbon::today();
            $user_invoices->duration = $parkingDurationInHours;
            $user_invoices->money = $parkingCost;
            $user_invoices->save();

        } catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }

        return response()->json(['message' => 'تم خروج السيارة بنجاح.',$parkingCost]);
    }


//اسم مستخدم وتاريخ ووقت اشغال والقيمة
//جدول اشتراكات
//زيائن
//يوزرات
//اجمالي

    public function exitCustomer(Request $request)
    {
        $employee = garage_employees::find(Auth::guard('garage_employee')->id());

        $garage=$employee->garage_id;
        $customerId = $request->customer_id;
        //$carId = $request->car_id;
        //$date=Carbon::today();


        $entry = active_customers::where('customer_id', $customerId)
            ->where('garage_id', $garage)
            ->orderBy('entry_time', 'desc')
            ->first();

        if (!$entry) {
            return response()->json(['message' => 'لم يتم العثور على سجل دخول الزبون.'], 404);
        }

        try {

        $exitTime = Carbon::now();
        // $exitTime = Carbon::now()->setTime(8, 0, 0);
        $entryTime = Carbon::parse($entry->entry_time);


        $parkingDurationInHours = ceil($exitTime->diffInHours($entryTime));


        $garage = garages::find($garage);
        $hourlyRate = $garage->price_per_hour;
        $w_cus_park = w_parks_customer::where('customer_id', $customerId)
            ->where('garage_id', $garage->id)
            //->where('time_end', '>=', $exitTime)
            //->where('date', $date)
          //  ->orderBy('time_end', 'asc')
            ->first();

        $parkingCost = 0;

        if ($w_cus_park) {
            if ($exitTime->lessThanOrEqualTo($w_cus_park->time_end)) {


                $parkingCost = $w_cus_park->price;

            } elseif ($exitTime->greaterThan($w_cus_park->time_end)) {

                $diff = $exitTime->diff($w_cus_park->time_end);
                // $extraHours = $exitTime->diffInHours($w_parks->time_end);
                $extraMinutes = $diff->i;
                $extraHours = $diff->h;

                if ($extraMinutes > 10) {
                    $extraHours += 1;
                }

                $parkingCost = $w_cus_park->price + ($hourlyRate * $extraHours);

            }

            $newStatus = Status::find(1);
            $park_id = $w_cus_park->parking_id;
            $park = Parkings::find($park_id);
            if ($newStatus) {
                $park->status_id = $newStatus->id;
                $park->save();
                $w_cus_park->delete();
            }


        }


        $totalPrice = required_serv_cus::where('customer_id', $customerId)
            ->join('services', 'required_serv_cus.services_id', '=', 'services.id')
            ->sum('services.price');

        required_serv_cus::where('customer_id', $customerId)
            ->where('done', 1)
            ->delete();

        $parkingCost=$parkingCost+$totalPrice;
        $entry->delete();
        } catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }


        $user_invoices = new w_customer_invoices();
        $customer = customers::find($customerId);
        $user_invoices->customer_id = $customerId;
        $user_invoices->date= Carbon::today();
        $user_invoices->duration=$parkingDurationInHours;
        $user_invoices->money=$parkingCost;
        $user_invoices->save();

        return response()->json(['message' => 'تم خروج السيارة بنجاح.',$parkingCost]);
    }




    public function getActiveUserWithWallet()
    {
        $relatedUsersWithCars = User::join('active_users', 'users.id', '=', 'active_users.user_id')
            ->join('cars', 'active_users.car_id', '=', 'cars.id')
            ->select(
                'users.*',
                'cars.id as car_id','cars.number as car_number',
                'cars.image as car_image','cars.barcode as car_barcode'

            )
            ->with(['walletes' => function ($query) {
                $query->select('id', 'price');
            }])
            ->with(['reservations'])
            ->get();

        return response()->json($relatedUsersWithCars);
    }

    public function getActiveCustomer()
    {
        $userIds = active_customers::pluck('customer_id');
        $relatedUsers = customers::whereIn('id', $userIds)->get();

        return $relatedUsers;
    }








}
