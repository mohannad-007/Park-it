<?php

namespace App\Http\Controllers;

use App\Models\customers;
use App\Models\garage_employees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomersController extends Controller
{

    public function addCustmer (Request $request)
    {

        $employee = garage_employees::find(Auth::guard('garage_employee')->id());
        $garage=$employee->garage_id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:customers',
            'number' => 'required|integer|unique:customers',
            // 'car_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {

            $custmer = new customers;
            $custmer->name = $request->name;
            $custmer->number = $request->number;
            //  $custmer->car_id = $request->car_id;
            $custmer->garage_id = $garage;

            $custmer->save();

        } catch (\Exception $exception) {
            return response()->json(['message' => 'Unable to add custmer', 'error' => $exception->getMessage()], 500);
        }
        return response()->json(['message' => 'Custmer added successfully',
            $custmer]);
    }


    public function getCustomersInGarage()
    {
        $employee = garage_employees::find(Auth::guard('garage_employee')->id());
        $garage=$employee->garage_id;

        $customers = customers::where('garage_id', $garage)->get();
        return response()->json($customers);

//        $usersData = [];
//        foreach ($customers as $customers) {
//            $usersData[] = [
//                'id' => $customers->id,
//            ];
//        }
//        return response()->json($usersData);
    }

    public function showCoustemr($id)
    {
        // $garage = garages::find(Auth::guard('garage')->id());

        $customers = customers::findOrFail($id);

        return response()->json($customers);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(customers $customers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(customers $customers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, customers $customers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(customers $customers)
    {
        //
    }
}
