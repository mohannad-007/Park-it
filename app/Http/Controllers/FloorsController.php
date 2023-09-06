<?php

namespace App\Http\Controllers;

use App\Models\floors;
use App\Models\garages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Floors as FloorsResources;
class FloorsController extends Controller
{

    public function getAllFloors()
    {
        $garage = garages::find(Auth::guard('garage')->id());
        $garage_id=$garage->id;
        if($floors = floors::where('garage_id', $garage_id)
            ->orderBy('id')
            ->get()){

            // return response()->json((FloorsResources::collection($floors))->toArray1());
            return response()->json(FloorsResources::collection($floors)->map->toArray1());

        }
        return response()->json([
            'message' => 'ther is no floor in your garage']);
    }

    public function updateFloorInfo(Request $request,$id): \Illuminate\Http\JsonResponse
    {

        $floor = floors::find($id);

        try {
            //$garage = garages::find($id);
            $floor->number = $request->number ?? $floor->number;
            $floor->save();
        } catch (\Exception $exception) {
            return Response()->json(['message' => $exception->getMessage()]);
        }
        return response()->json([
            'message' => 'floor updated successfully', (new FloorsResources($floor))->toArray1()]);
    }

    public function removeFloor( $id)
    {
        $garage = garages::find(Auth::guard('garage')->id());


        $floor = floors::find($id);
        if (!$floor) {
            return response()->json(['message' => 'Attribute not found'], 404);
        }


        foreach ($floor->parkings as $parking) {
            $parking->delete();
        }

        $floor->delete();

        return response()->json(['message' => 'Attribute removed from account successfully'], 200);
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
    public function show(floors $floors)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(floors $floors)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, floors $floors)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(floors $floors)
    {
        //
    }
}
