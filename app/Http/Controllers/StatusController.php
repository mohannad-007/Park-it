<?php

namespace App\Http\Controllers;

use App\Models\reservations;
use App\Models\status;
use Carbon\Carbon;
use http\Message;
use Illuminate\Http\Request;

class StatusController extends Controller
{

    public function addStatus(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:statuses',
        ]);
            Status::create([
                'name' => $request->name,
            ]);
        return response()->json(['message' => 'New status added successfully.']);

    }


}
