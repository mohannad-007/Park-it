<?php

namespace App\Http\Controllers;

use App\Models\garage_employees;
use App\Models\required_serv_cus;
use App\Models\required_services;
use App\Models\services;
use Illuminate\Http\Request;
use App\Models\garages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Services as ServicesResource;


class ServicesController extends Controller
{
    public function showMyServices()
    {
        $garage = garages::find(Auth::guard('garage')->id());
        $garageServices = $garage->services;
        return response()->json(  ServicesResource::collection($garageServices));
    }

    public function showGarageServices($id)
    {
        $garage = garages::with('services')->find($id);
        $garageServices = $garage;
        return response()->json([
            'garageServices'=>$garageServices,
        ]);
    }

    public function addservice(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'image' => 'required|image',
            'price' => 'required|integer',
            'name' => 'required|string',
            'service_information' => 'required|string',


        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $garage_id = garages::find(Auth::guard('garage')->id());
            $service = new services;
            // $services->image = $request->image;
            $service->price = $request->price;
            $service->name = $request->name;
            $service->service_information = $request->service_information;
            $service->garage_id = $garage_id->id;


            $file = $request->file('image');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename =  time() . '.' . $extension;
            $path = $file->move(public_path('services-images'), $filename);
            $service->image = url('services-images/' . $filename);
            $service->save();

        } catch (\Exception $exception) {
            return response()->json(['message' => 'Unable to add parking', 'error' => $exception->getMessage()], 500);
        }
        return response()->json(['message' => 'Service added successfully',
            new ServicesResource($service),201]);
    }


    public function updateServiceInfo(Request $request,$id): \Illuminate\Http\JsonResponse
    {

        $service = services::find($id);

        try {
            //$garage = garages::find($id);
            //$service->image = $request->image ?? $service->image;///هون يمكن مو هيك
            $service->price = $request->price ?? $service->price;
            $service->name = $request->name ?? $service->name;
            $service->service_information = $request->service_information ?? $service->service_information;

            if( $request->image)
            {
                $file = $request->file('image');
                $filename = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename =  time() . '.' . $extension;
                $path = $file->move(public_path('services-images'), $filename);
                $service->image = url('services-images/' . $filename);
            }else
            {
                $image=$service->image;
                $service->image = $image;
            }
            $service->save();
        } catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }
        return response()->json([
            'message' => 'service updated successfully', new ServicesResource($service)]);
    }


    public function removeService($service_id)
    {
        $garage = garages::find(Auth::guard('garage')->id());


        $services = services::find($service_id);
        if (!$services) {
            return response()->json(['message' => 'services not found'], 404);
        }


        $services->delete();

        return response()->json(['message' => 'services removed from account successfully'], 200);
    }

    public function services_done(Request $request)
    {
        $service = required_services::find($request->id);

        if ($service) {
            $service->update(['done' => 1]);
            return response()->json(['message' => 'تم تحديث حالة الخدمة بنجاح.']);
        } else {
            return response()->json(['error' => 'الخدمة غير موجودة.'], 404);
        }
    }

    public function services_customer_done(Request $request)
    {
        $service = required_serv_cus::find($request->id);

        if ($service) {
            $service->update(['done' => 1]);
            return response()->json(['message' => 'تم تحديث حالة الخدمة بنجاح.'])
                ->header('Accept', 'application/json');
        } else {
            return response()->json(['error' => 'الخدمة غير موجودة.'])
                ->header('Accept', 'application/json');
        }
    }


    public function garage_employee_services()
    {

        $garage_employees = garage_employees::find(Auth::guard('garage_employee')->id());
        $garageId = $garage_employees->garage_id;
        $garageServices = services::where('garage_id' ,$garageId)->get();
        return response()->json([
            'garageServices'=>$garageServices,
        ]);
    }


    public function requestCustomerServices(Request $request)
    {

        $garage_employees = garage_employees::find(Auth::guard('garage_employee')->id());
        $garageId = $garage_employees->garage_id;
        $customer = $request->input('customer_id');
        $serviceId = $request->input('service_id');
        // Validate if the user has provided the service_id
        if (!$customer) {
            return response()->json(['message' => 'Invalid customer request.'], 400);
        }
        if (!$serviceId) {
            return response()->json(['message' => 'Invalid service request.'], 400);
        }
        // Retrieve the service details
        $service = services::where('garage_id' ,$garageId)->find($serviceId);

        if (!$service) {
            return response()->json(['message' => 'Service not found.'], 404);
        }

        $req = required_serv_cus::create([
            'customer_id' => $customer,
            'services_id' => $serviceId,
        ]);

        $req = $req->with('customers.garages','services')->get();

        return response()->json([
            'message' => 'Service requested successfully.',
            'requred_services'=>$req
        ], 200);
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
    public function show(services $services)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(services $services)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, services $services)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(services $services)
    {
        //
    }
}
