<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function pay(Request $request)
    {
        $validatedData = $request->validate([
            'amount' => 'required|in:50000,200000',
            'secret_code' => 'required|exists:secret_payments,secret_code'
        ]);
    
        $payment = new Payment();
        $payment->user_id = Auth::user()->id;
        $payment->amount = $validatedData['amount'];
        $payment->secret_code = $validatedData['secret_code'];
        $payment->save();
    
        $user = Auth::user();
        
        if ($validatedData['amount'] == 50000) {
            $user->increment('hotel_number', 5);
        } elseif ($validatedData['amount'] == 200000) {
            $user->increment('hotel_number', 200);
        }
        $user->save();
    
        return response()->json([
            'message' => 'Payment successful',
            'payment_id' => $payment->id,
            'user_id' => $user
            // 'secret_code' => $payment->secret_code,
        ]);
    }
}
