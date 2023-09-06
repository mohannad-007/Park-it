<?php

namespace App\Http\Controllers;

use App\Models\reports;
use App\Models\w_customer_invoices;
use App\Models\w_invoices;
use Illuminate\Http\Request;

class ReportsController extends Controller
{

    public function getInvoices(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $records = w_invoices::whereBetween('date', [$startDate, $endDate])
            ->orWhere(function ($query) use ($startDate, $endDate) {
                $query->whereNull('date')
                    ->where('date', '>', $startDate)
                    ->where('date', '<', $endDate);
            })
            ->get();

        $customerRecords = w_customer_invoices::whereBetween('date', [$startDate, $endDate])
            ->orWhere(function ($query) use ($startDate, $endDate) {
                $query->whereNull('date')
                    ->where('date', '>', $startDate)
                    ->where('date', '<', $endDate);
            })
            ->get();

        $combinedRecords = $records->concat($customerRecords);

        if ($combinedRecords->isEmpty()) {
            return response()->json(['message' => 'لا يوجد فواتير']);
        }

        $totalMoney = $combinedRecords->sum('money');

        $formattedRecords = $combinedRecords->map(function ($record) {
            $recordData = [
                'money' => $record->money,
                'date' => $record->date,
                'Duration' => $record->Duration,
            ];

            if ($record->user) {
                $recordData['user_name']  = $record->user->name . ' ' . $record->user->nickname;
            } elseif ($record->customer) {
                $recordData['user_name'] = $record->customer->name;
            }

            return $recordData;
        });
//
//        $formattedRecords = $combinedRecords->map(function ($record) {
//            return [
//                'user_name' => $record->user->name ?? $record->customer->name,
//                'money' => $record->money,
//                'date' => $record->date,
//                'Duration' => $record->Duration,
//            ];
//        });

        return response()->json([
            'records' => $formattedRecords,
            'total_money' => $totalMoney
        ]);
    }



//    public function getCustomerInvoices(Request $request)
//    {
//        $startDate = $request->input('start_date');
//        $endDate = $request->input('end_date');
//
//        $records = w_customer_invoices::whereBetween('date', [$startDate, $endDate])
//            ->orWhere(function ($query) use ($startDate, $endDate) {
//                $query->whereNull('date')
//                    ->where('date', '>', $startDate)
//                    ->where('date', '<', $endDate);
//            })
//            ->get();
//
//        if ($records->isEmpty()) {
//            return response()->json(['message' => 'لا يوجد فواتير']);
//        }
//
//        $totalMoney = $records->sum('money');
//        $formattedRecords = $records->map(function ($record) {
//            return [
//                'user_name' => $record->customer->name,
//                'money' => $record->money,
//                'date' => $record->date,
//                'Duration' => $record->Duration,
//
//            ];
//        });
//
//        return response()->json([
//            'records' => $formattedRecords,
//            'total_money' => $totalMoney
//        ]);
//    }
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
    public function show(reports $reports)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(reports $reports)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, reports $reports)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(reports $reports)
    {
        //
    }
}
