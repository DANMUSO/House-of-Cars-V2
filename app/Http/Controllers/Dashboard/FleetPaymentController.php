<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FleetPayment;
use Illuminate\Http\Request;

class FleetPaymentController extends Controller
{
    public function show($id)
    {
        try {
            $payment = FleetPayment::with('fleetAcquisition')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }
    }

    public function confirm($id)
    {
        try {
            $payment = FleetPayment::findOrFail($id);
            
            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is already processed'
                ], 400);
            }
            
            $payment->update([
                'status' => 'confirmed',
                'processed_by' => auth()->user()->name ?? 'System'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error confirming payment: ' . $e->getMessage()
            ], 500);
        }
    }
}