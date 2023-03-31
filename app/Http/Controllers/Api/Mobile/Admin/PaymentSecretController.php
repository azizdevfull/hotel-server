<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use Illuminate\Http\Request;
use App\Models\SecretPayment;
use App\Http\Controllers\Controller;

class PaymentSecretController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentSecret = SecretPayment::all();

        return response()->json([
            'status' => true,
            'data' => $paymentSecret,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'secret_code' => 'required|unique:secret_payments',
        ]);

        $paymentSecret = SecretPayment::create($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'Payment secret code created successfully',
            'secret_code' => $paymentSecret,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $paymentSecret = SecretPayment::find($id);
        if(!$paymentSecret){
            return response()->json([
               'status' => false,
               'message' => 'Payment secret code not found',
            ]);
        }

        $validatedData = $request->validate([
            'secret_code' => 'required|unique:secret_payments,secret_code,' . $id,
        ]);

        $paymentSecret->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'Payment secret code updated successfully',
            'secret_code' => $paymentSecret,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paymentSecret = SecretPayment::find($id);

        if (!$paymentSecret) {
            return response()->json([
                'status' => false,
                'message' => 'Payment secret code not found',
            ], 404);
        }

        $paymentSecret->delete();

        return response()->json([
            'status' => true,
            'message' => 'Payment secret code deleted successfully',
        ]);
    }
}
