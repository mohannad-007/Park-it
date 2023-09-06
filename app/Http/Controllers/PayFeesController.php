<?php

namespace App\Http\Controllers;

use App\Models\pay_fees;
use App\Models\User;
use App\Models\w_invoices;
use Illuminate\Http\Request;

class PayFeesController extends Controller
{

    public function makePayment(Request $request)
    {
        $userId = $request->input('user_id');

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        $invoice = w_invoices::where('user_id', $userId)->first();

        if (!$invoice) {
            return response()->json(['message' => 'لم يتم العثور على فاتورة لهذا المستخدم'], 404);
        }

        $amountToPay = $invoice->money;

        $currentBalance = $user->walletes->price;

        if ($currentBalance < $amountToPay) {

            $remainingAmount = $amountToPay - $currentBalance;

            $user->walletes->price = 0;
            $user->walletes->save();

            return response()->json([
                'message' => 'المبلغ المطلوب تم تغطيته جزئيا لعدم وجود مبلغ كافي بالمحفظة.',
                'remaining_amount' => $remainingAmount
            ]);
        } else {

            $user->walletes->price -= $amountToPay;
            $user->walletes->save();

            return response()->json([
                'message' => 'تم خصم المبلغ المطلوب بنجاح من المحفظة.'
            ]);
        }
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
    public function show(pay_fees $pay_fees)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(pay_fees $pay_fees)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, pay_fees $pay_fees)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(pay_fees $pay_fees)
    {
        //
    }
}
