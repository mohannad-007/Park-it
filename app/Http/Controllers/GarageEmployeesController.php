<?php

namespace App\Http\Controllers;

use App\Http\Resources\Employees as EmployeesResources;
use App\Models\active_users;
use App\Models\customer_subscriptions;
use App\Models\customers;
use App\Models\garage_employees;
use App\Models\garage_subscriptions;
use App\Models\garages;
use App\Models\required_services;
use App\Models\reservations;
use App\Models\User;
use App\Models\user_subscriptions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class GarageEmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showMyEmployees()
    {
        $garage = garages::find(Auth::guard('garage')->id());
        $garageEmployees = $garage->garage_emp;
        $garageEmployees->makeHidden('password');
        return response()->json( $garageEmployees);

    }

    public function showAccountEmployees($id)
    {
        $garage = garages::find(Auth::guard('garage')->id());

        $employee = garage_employees::findOrFail($id);
        if ($employee->garage_id = $garage) {

            return response()->json((new EmployeesResources($employee))->toArray1());
        }

        return response()->json(  'error');

    }

    public function updateEmployeeInfo(Request $request,$id)
    {

        $employee = garage_employees::find($id);

        try {
            //$garage = garages::find($id);
            $employee->name = $request->name ?? $employee->name;
            $employee->phone_number = $request->phone_number ?? $employee->phone_number;
            $employee->email = $request->email ?? $employee->email;
            $employee->password = bcrypt($request->password) ?? $employee->password;
            $employee->address = $request->address ?? $employee->address;
            //$employee->garage_id = $request->garage_id ?? $employee->garage_id;
            $employee->save();
        } catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }
        $employee->makeHidden('password');
        return response()->json([

            'message' => 'Account updated successfully', ($employee)]);
    }

    public function removeEmployee( $id)
    {
        $garage = garages::find(Auth::guard('garage')->id());

        $employee = garage_employees::find($id);
        if (!$employee) {
            return response()->json(['message' => 'employee not found'], 404);
        }
        $employee->delete();
        if ($employee->garage_id != $garage->id)
        {
            return response()->json(['message' => 'employee not associated with this garage'], 403);
        }

        return response()->json(['message' => 'Employee removed from account successfully']);



    }

    public static function searchByNameforEmployee($name)
    {
        $garage = garages::find(Auth::guard('garage')->id());

        $employees = garage_employees::where('garage_id', $garage->id)
            ->where('name', 'LIKE', '%' . $name . '%')
            ->get();
        if ($employees->isEmpty()) {
            return response()->json(['message' => 'No employees found with this name in this garage'], 404);
        }
        $employees->each(function ($employee) {
            $employee->makeHidden('password');
        });
        return response()->json($employees);
    }

    public static function searchByNameForCustomer(Request $request)
    {

        $query = $request->get('query');
        // Perform the search using the 'name' column
        $customers = customers::with('garages')->where('name', 'LIKE', '%' . $query . '%')->get();
        if ($customers->isEmpty()) {
            return response()->json(['message' => 'No customers found with this name'], 404);
        }
        // Return the search results
        return response()->json([
            'results' => $customers,
        ], 200);

    }

    public static function searchByNameForUser(Request $request)
    {

        $query = $request->get('query');
        // Perform the search using the 'name' column
        $user = User::where('name', 'LIKE', '%' . $query . '%')->get();
        if ($user->isEmpty()) {
            return response()->json(['message' => 'No user found with this name'], 404);
        }
        // Return the search results
        return response()->json([
            'results' => $user,
        ], 200);

    }


    public function searchByNameForActiveUser(Request $request)
    {
        $query = $request->get('query');

        // Perform the search using the 'name' column in the 'users' table
        $activeUsers = active_users::with('cars.user')
            ->whereHas('users', function ($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%');
            })
            ->get();
//        $activeUsers = active_users::with('cars.user')
//            ->where('name', 'LIKE', '%' . $query . '%')->get();

        if ($activeUsers->isEmpty()) {
            return response()->json(['message' => 'No active user found with this name'], 404);
        }

        return response()->json([
            'results of search ' => $activeUsers,
        ], 200);
    }

    public function customerSubscription(Request $request)
    {
        $garage_employees = garage_employees::find(Auth::guard('garage_employee')->id());

        $customer= $request->input('customer_id');
        $subscriptionType = $request->input('subscription_type');
        $numberOfMonths = $request->input('number_of_months'); // Assuming this is the input for the number of months

        $getCustumer= customers::findOrFail($customer);

        $garage = garages::with('garage_subscriptions.subscriptions')->find($garage_employees);
        if (!$garage) {
            return response()->json(['message' => 'Garage not found.'], 404);
        }

        // Check if the subscription type exists in the "garage_subscriptions" table
        $subscription = garage_subscriptions::whereHas('subscriptions', function ($query) use ($subscriptionType) {
            $query->where('type', $subscriptionType);
        })->first();

        if (!$subscription) {
            return response()->json(['message' => 'Invalid subscription type.'], 400);
        }

        // Check if there is an existing subscription for the same customer and garage with the same start date
        $existingSubscription = customer_subscriptions::where('customer_id', $getCustumer->id)
            ->where('start_date_sub', Carbon::now()->toDateString())
            ->first();

        if ($existingSubscription) {
            return response()->json(['message' => 'Subscription already exists for the same date.'], 400);
        }

        // Fetch the correct price from the "garage_subscriptions" table
        $pricePerMonth = $subscription->price;
        $totalPrice = $pricePerMonth * $numberOfMonths;

        // Proceed with the subscription if all conditions are met
        // You may want to handle any payment or transaction logic here if applicable
        DB::beginTransaction();
        try {
            // Create the user subscription
            $customerSubscription = customer_subscriptions::create([
                'start_date_sub' => Carbon::now()->toDateString(),
                'end_date_sub' => $subscriptionType === 'monthly'
                    ? Carbon::now()->addMonths($numberOfMonths)->toDateString()
                    : Carbon::now()->addYears($numberOfMonths)->toDateString(),
                'customer_id' => $getCustumer->id,
                'garage_subscriptions_id' => $subscription->id,
            ]);
            DB::commit();

            // Modify the user subscription object before adding it to the response
            $customerSubscriptionData = $customerSubscription->toArray();
            unset($customerSubscriptionData['id']); // Remove the 'id' field
            $customerSubscriptionData = ['id' => $customerSubscription->id] + $customerSubscriptionData;

//            $customerSubscriptionData = customer_subscriptions::with('customers.garages');

            return response()->json([
                'totalPrice' => $totalPrice,
                'message' => 'Subscription successful.',
                'customer_subscription' => $customerSubscriptionData,
                'customer' => $getCustumer,
                'garage' => $garage,
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'An error occurred while processing the subscription.',$e->getMessage()], 500);
        }
    }


    public function updateCustomerSubscription(Request $request)
    {
//        $user = Auth::user()->load('walletes');

//        $garageId = $request->input('garage_id');
        $garage_employees = garage_employees::find(Auth::guard('garage_employee')->id());

        $customer= $request->input('customer_id');
        $subscriptionType = $request->input('subscription_type');
        $numberOfMonths = $request->input('number_of_months'); // Assuming this is the input for the number of months

        $getCustumer= customers::findOrFail($customer);

        $garage = garages::with('garage_subscriptions.subscriptions')->find($garage_employees);

        if (!$garage) {
            return response()->json(['message' => 'Garage not found.'], 404);
        }

        // Check if the subscription type exists in the "garage_subscriptions" table
        $subscription = garage_subscriptions::whereHas('subscriptions', function ($query) use ($subscriptionType) {
            $query->where('type', $subscriptionType);
        })->first();

        if (!$subscription) {
            return response()->json(['message' => 'Invalid subscription type.'], 400);
        }



        // Fetch the correct price from the "garage_subscriptions" table
        $pricePerMonth = $subscription->price;
        $totalPrice = $pricePerMonth * $numberOfMonths;


        // Check if the user has an active subscription for the same garage and subscription type
        $activeSubscription = customer_subscriptions::where('customer_id', $getCustumer->id)
            ->where('garage_subscriptions_id', $subscription->id)
            ->where('end_date_sub', '>=', Carbon::now()->toDateString())
            ->first();

        DB::beginTransaction();
        try {
            if ($activeSubscription) {
                // Renew the existing subscription by updating its end date
                $activeSubscription->update([
                    'end_date_sub' => $subscriptionType === 'monthly'
                        ? Carbon::parse($activeSubscription->end_date_sub)->addMonths($numberOfMonths)->toDateString()
                        : Carbon::parse($activeSubscription->end_date_sub)->addYears($numberOfMonths)->toDateString(),
                ]);
            } else {
                // Create the user subscription since it doesn't exist
                $customerSubscription = customer_subscriptions::create([
                    'start_date_sub' => Carbon::now()->toDateString(),
                    'end_date_sub' => $subscriptionType === 'monthly'
                        ? Carbon::now()->addMonths($numberOfMonths)->toDateString()
                        : Carbon::now()->addYears($numberOfMonths)->toDateString(),
                    'customer_id' => $getCustumer->id,
                    'garage_subscriptions_id' => $subscription->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'totalPrice' => $totalPrice,
                'message' => 'Subscription renewed successfully.',
                'subscription' => $customerSubscription ?? $activeSubscription,
                'customer'=>$getCustumer,
                'garage'=>$garage,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'An error occurred while processing the subscription.',
                $e->getMessage()
            ], 500);
        }
    }

    public function getMyAccountInfo()
    {
        $employee = garage_employees::find(Auth::guard('garage_employee')->id());
        try {
            return response()->json((new EmployeesResources($employee))->toArray1());
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()]);
        }

    }


    public function updateMyInfo(Request $request)
    {

        $employee = garage_employees::find(Auth::guard('garage_employee')->id());


        try {
            //$garage = garages::find($id);
            $employee->name = $request->name ?? $employee->name;
            $employee->phone_number = $request->phone_number ?? $employee->phone_number;
            $employee->email = $request->email ?? $employee->email;
            $employee->password = bcrypt($request->password) ?? $employee->password;
            $employee->address = $request->address ?? $employee->address;
            //$employee->garage_id = $request->garage_id ?? $employee->garage_id;
            if( $request->image)
            {
                $file = $request->file('image');
                $filename = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename =  time() . '.' . $extension;
                $path = $file->move(public_path('employees-images'), $filename);
                $employee->image = url('employees-images/' . $filename);
            }
            $employee->save();
        } catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }
        return response()->json([
            'message' => 'Account updated successfully', (new EmployeesResources($employee))->toArray1()]);
    }


    public function showAccountUser($id)
    {
        // $garage = garages::find(Auth::guard('garage')->id());

        $user = user::findOrFail($id);

        return response()->json($user);
    }

    public function getUsersReservations()
    {
        $employee = garage_employees::find(Auth::guard('garage_employee')->id());
        $garage = $employee->garage_id;

        $reservations = reservations::with(['user']) // تحميل معلومات المستخدم مع معلومات الحجز
        ->whereHas('garages', function ($query) use ($garage) {
            $query->where('garage_id', '=', $garage);
        })
            ->get();

        if ($reservations->count() > 0) {
            return response()->json(['reservations' => $reservations], 200);
        }

        return response()->json(['message' => 'There are no reservations'], 200);
    }


    public function showMyUsersServices()
    {
        $employee = garage_employees::find(Auth::guard('garage_employee')->id());
        $garage = $employee->garage_id;

        $requiredServices = required_services::with(['services', 'user'])
        ->whereHas('services', function ($query) use ($garage) {
            $query->where('garage_id', '=', $garage);
        })
            ->get();

        if ($requiredServices->count() > 0) {
            return response()->json(['required_services' => $requiredServices], 200);
        }

        return response()->json(['message' => 'There are no required services'], 200);
    }






}
