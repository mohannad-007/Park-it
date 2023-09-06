<?php

namespace App\Http\Controllers;

use App\Models\fav_garage;
use App\Models\garages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavGarageController extends Controller
{

    public function addToFavorites(Request $request)
    {
        $user = Auth::user();
        // Validate the request data (if needed)
         $request->validate([
             'garage_id' => 'required|exists:garages,id',
         ]);
        // Get the garage_id from the request (assuming you have a form or API request)
        $garageId = $request->input('garage_id');

        // Check if the user already has the garage in their favorites
        $existingFavorite = fav_garage::where('user_id', $user->id)
            ->where('garage_id', $garageId)
            ->first();

        $garage = garages::find($garageId);
        if ($existingFavorite) {
            // Garage is already in favorites, you can handle this case if needed
            return response()->json(['message' => 'Garage already in favorites.'], 400);
        }

        // Create a new entry in the fav_garages table to store the favorite garage
        $favorite = new fav_garage();
        $favorite->user_id = $user->id;
        $favorite->garage_id = $garageId;
        $favorite->save();

        return response()->json([
            'message' => 'Garage added to favorites successfully.',
            'user'=> $user,
            'garage' => $garage,
        ], 200);
    }

    public function removeFromFavorites(Request $request)
    {
        $user = Auth::user();
        // Validate the request data (if needed)
        $request->validate([
            'garage_id' => 'required|exists:garages,id',
        ]);

        // Get the garage_id from the request (assuming you have a form or API request)
        $garageId = $request->input('garage_id');

        // Check if the garage is in the user's favorites
        $existingFavorite = fav_garage::where('user_id', $user->id)
            ->where('garage_id', $garageId)
            ->first();

        if (!$existingFavorite) {
            return response()->json(['message' => 'Garage is not in favorites.'], 400);
        }

        // Delete the entry from the fav_garages table to remove the favorite garage
        $existingFavorite->delete();

        return response()->json(['message' => 'Garage removed from favorites successfully.'], 200);
    }




    /**
     * Display a listing of the resource.
     */
    public function index($user_id)
    {
        $favoriteGarages = fav_garage::with('garages')->where('user_id', $user_id)->get();

        return response()->json(['favorite_garages' => $favoriteGarages], 200);
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
    public function show(fav_garage $fav_garage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(fav_garage $fav_garage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, fav_garage $fav_garage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(fav_garage $fav_garage)
    {
        //
    }
}
