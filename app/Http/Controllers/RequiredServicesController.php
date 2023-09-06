<?php

namespace App\Http\Controllers;

use App\Http\Resources\Services as ServicesResource;
use App\Models\garages;
use App\Models\required_services;
use App\Models\services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RequiredServicesController extends Controller
{

    public function showMyServices()
    {
        $garage = garages::find(Auth::guard('garage')->id());
        $garageServices = $garage->services;
        return response()->json(  ServicesResource::collection($garageServices));

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
    public function show(required_services $required_services)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(required_services $required_services)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, required_services $required_services)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(required_services $required_services)
    {
        //
    }
}
