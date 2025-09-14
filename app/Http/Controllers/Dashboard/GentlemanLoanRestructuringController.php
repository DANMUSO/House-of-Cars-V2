<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GentlemanAgreement;
use App\Models\PaymentSchedule;
use App\Models\Penalty;
use App\Models\CarImport;
use App\Models\CustomerVehicle;
use App\Services\PenaltyService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Services\SmsService;

class GentlemanLoanRestructuringController extends Controller
{
    // Constants
    const DEFAULT_RESTRUCTURING_FEE_RATE = 3.0; // 3% default fee
    
    /**
     * Show restructuring options page for Gentleman Agreement
     */
    public function showRestructuringPage($agreementId)
{
    try {
        $agreement = GentlemanAgreement::with([
            'paymentSchedule', 
            'penalties',
            'customerVehicle',
            'carImport'
        ])->findOrFail($agreementId);
        
        // Calculate current financial position with complete loan information
        $financialSummary = $this->calculateCurrentFinancialPosition($agreement);
        
        // Ensure we have all necessary loan information
        $financialSummary['original_loan_amount'] = $agreement->loan_amount;
        $financialSummary['vehicle_price'] = $agreement->vehicle_price;
        $financialSummary['deposit_amount'] = $agreement->deposit_amount;
        $financialSummary['amount_paid'] = $agreement->amount_paid;
        $financialSummary['monthly_payment'] = $agreement->monthly_payment;
        $financialSummary['duration_months'] = $agreement->duration_months;
        
        // Calculate remaining months for JavaScript
        $currentRemainingMonths = $this->getRemainingMonths($agreement);
        
        Log::info('Loading Gentleman Agreement restructuring page:', [
            'agreement_id' => $agreementId,
            'remaining_months' => $currentRemainingMonths,
            'financial_summary' => $financialSummary,
            'no_interest' => true
        ]);
        
        return view('gentleman-loan-restructuring.options', compact(
            'agreement', 
            'financialSummary', 
            'currentRemainingMonths'
        ));
        
    } catch (\Exception $e) {
        Log::error('Error loading Gentleman Agreement restructuring page: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Unable to load restructuring options. Please try again.');
    }
}
    
    /**
     * Get restructuring options for Gentleman Agreement using exact formula
     */
    public function getRestructuringOptions(Request $request)
    {
        try {
            $agreementId = $request->get('agreement_id');
            $newDuration = $request->get('new_duration');
            $feeRate = $request->get('fee_rate', self::DEFAULT_RESTRUCTURING_FEE_RATE);
            
            if (!$agreementId) {
                return response()->json(['error' => 'Agreement ID is required'], 400);
            }
            
            $agreement = GentlemanAgreement::with(['paymentSchedule', 'penalties'])->findOrFail($agreementId);
            
            // Calculate current financial position
            $financialPosition = $this->calculateCurrentFinancialPosition($agreement);
            
            // Apply exact formula:
            // Outstanding Balance × Restructuring Fee Rate = Restructuring Fee
            $outstandingBalance = $financialPosition['outstanding_balance'];
            $restructuringFee = ($outstandingBalance * $feeRate) / 100;
            
            // New Loan Amount = Outstanding Balance + Restructuring Fee + Penalties
            $newLoanAmount = $outstandingBalance + $restructuringFee + $financialPosition['total_penalties'];
            
            Log::info('Gentleman Agreement restructuring calculation (NO INTEREST):', [
                'agreement_id' => $agreementId,
                'outstanding_balance' => $outstandingBalance,
                'fee_rate' => $feeRate,
                'restructuring_fee' => $restructuringFee,
                'penalties' => $financialPosition['total_penalties'],
                'new_loan_amount' => $newLoanAmount,
                'no_interest' => true
            ]);
            
            // Calculate options for different durations
            $options = [];
            
            $durations = [12, 18, 24, 36, 48, 60]; // Common durations
            if ($newDuration && !in_array($newDuration, $durations)) {
                $durations[] = $newDuration;
            }
            
            foreach ($durations as $duration) {
                // New Monthly Payment = New Loan Amount ÷ Admin-Selected Duration
                $monthlyPayment = $newLoanAmount / $duration;
                
                $options[] = [
                    'duration' => $duration,
                    'monthly_payment' => round($monthlyPayment, 2),
                    'total_amount' => $newLoanAmount,
                    'payment_change' => round($monthlyPayment - $agreement->monthly_payment, 2),
                    'description' => "Pay KSh " . number_format($monthlyPayment, 2) . " per month for {$duration} months (no interest)"
                ];
            }
            
            return response()->json([
                'success' => true,
                'financial_position' => $financialPosition,
                'outstanding_balance' => $outstandingBalance,
                'restructuring_fee' => $restructuringFee,
                'restructuring_fee_rate' => $feeRate,
                'new_loan_amount' => $newLoanAmount,
                'options' => $options,
                'no_interest' => true
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error calculating Gentleman Agreement restructuring options: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to calculate restructuring options: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * Process restructuring for Gentleman Agreement using exact formula
 */
public function processRestructuring(Request $request)
{
    try {
        $validated = $request->validate([
            'agreement_id' => 'required|integer|exists:gentlemanagreements,id',
            'new_duration' => 'required|integer|min:1|max:120',
            'restructuring_date' => 'required|date',
            'fee_rate' => 'nullable|numeric|min:0|max:20',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        DB::beginTransaction();
        
        $agreement = GentlemanAgreement::with(['paymentSchedule', 'penalties'])->findOrFail($validated['agreement_id']);
        
        Log::info('Processing Gentleman Agreement restructuring (NO INTEREST):', [
            'agreement_id' => $agreement->id,
            'new_duration' => $validated['new_duration'],
            'fee_rate' => $validated['fee_rate'] ?? self::DEFAULT_RESTRUCTURING_FEE_RATE,
            'no_interest' => true
        ]);
        
        // Calculate restructuring using exact formula
        $financialPosition = $this->calculateCurrentFinancialPosition($agreement);
        
        $actualFeeRate = $validated['fee_rate'] ?? self::DEFAULT_RESTRUCTURING_FEE_RATE;
        
        // Outstanding Balance × Restructuring Fee Rate = Restructuring Fee
        $outstandingBalance = $financialPosition['outstanding_balance'];
        $restructuringFee = ($outstandingBalance * $actualFeeRate) / 100;
        
        // New Loan Amount = Outstanding Balance + Restructuring Fee + Penalties
        $newLoanAmount = $outstandingBalance + $restructuringFee + $financialPosition['total_penalties'];
        
        // New Monthly Payment = New Loan Amount ÷ Admin-Selected Duration
        $newMonthlyPayment = $newLoanAmount / $validated['new_duration'];
        
        // Store original terms for history
        $originalTerms = $this->captureOriginalTerms($agreement);
        
        // Clear existing unpaid schedules
        $this->clearUnpaidSchedules($agreement->id);
        
        // **NEW: Mark all pending penalties as restructured**
        $this->markPenaltiesAsRestructured($agreement->id);
        
        // Generate new payment schedule (NO INTEREST)
        $this->generateSimplePaymentSchedule(
            $agreement->id,
            $newLoanAmount,
            $newMonthlyPayment,
            $validated['new_duration'],
            $this->getNextDueDate($agreement)
        );
        
        // Update agreement with new terms
        $this->updateAgreementAfterRestructuring($agreement, $newMonthlyPayment, $validated['new_duration'], $newLoanAmount);
        
        DB::commit();
        
        // ... rest of the method remains the same
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('❌ Gentleman Agreement restructuring failed: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
/**
 * Mark all pending penalties as restructured (absorbed into new loan)
 */
private function markPenaltiesAsRestructured($agreementId)
{
    $updatedCount = Penalty::where('agreement_id', $agreementId)
        ->where('status', 'pending')
        ->update([
            'status' => 'restructured',
            'restructured_at' => now(),
            'updated_at' => now()
        ]);
        
    Log::info('Marked pending penalties as restructured for Gentleman Agreement', [
        'agreement_id' => $agreementId,
        'penalties_restructured' => $updatedCount
    ]);
    
    return $updatedCount;
}

    /**
     * Generate simple payment schedule for Gentleman Agreement (NO INTEREST)
     */
    private function generateSimplePaymentSchedule($agreementId, $totalAmount, $monthlyPayment, $duration, $startDate)
    {
        Log::info('=== GENERATING RESTRUCTURED SCHEDULE FOR GENTLEMAN AGREEMENT (NO INTEREST) ===', [
            'total_amount' => $totalAmount,
            'monthly_payment' => $monthlyPayment,
            'duration' => $duration,
            'no_interest' => true
        ]);
        
        $remainingPrincipal = $totalAmount;
        
        // Get the next installment number (after existing payments)
        $lastInstallment = PaymentSchedule::where('agreement_id', $agreementId)
            ->max('installment_number') ?? 0;
        
        for ($month = 1; $month <= $duration; $month++) {
            // Simple calculation: NO INTEREST, just divide principal
            if ($month == $duration) {
                // For the last payment, pay the remaining balance exactly
                $monthlyPrincipal = $remainingPrincipal;
                $actualPayment = $remainingPrincipal;
                $newRemainingPrincipal = 0;
            } else {
                $monthlyPrincipal = $monthlyPayment;
                $actualPayment = $monthlyPayment;
                $newRemainingPrincipal = $remainingPrincipal - $monthlyPayment;
            }
            
            $monthlyInterest = 0; // NO INTEREST for Gentleman Agreement
            
            // Log first few payments for verification
            if ($month <= 3) {
                Log::info("RESTRUCTURED Gentleman Payment {$month} Details (NO INTEREST):", [
                    'Starting Balance' => round($remainingPrincipal, 2),
                    'Interest Rate' => '0% (Gentleman Agreement)',
                    'Interest Amount' => 0,
                    'Principal Amount' => round($monthlyPrincipal, 2),
                    'Total Payment' => round($actualPayment, 2),
                    'Ending Balance' => round($newRemainingPrincipal, 2)
                ]);
            }
            
            // Create payment schedule entry with NO INTEREST
            PaymentSchedule::create([
                'agreement_id' => $agreementId,
                'installment_number' => $lastInstallment + $month,
                'due_date' => Carbon::parse($startDate)->addMonths($month - 1),
                'principal_amount' => round($monthlyPrincipal, 2),
                'interest_amount' => 0, // NO INTEREST for Gentleman Agreement
                'total_amount' => round($actualPayment, 2),
                'balance_after' => round($newRemainingPrincipal, 2),
                'status' => 'pending',
                'amount_paid' => 0,
                'date_paid' => null,
                'days_overdue' => 0,
                'schedule_type' => 'restructured',
                'restructuring_type' => 'reduce_duration'
            ]);
            
            $remainingPrincipal = $newRemainingPrincipal;
            
            if ($remainingPrincipal <= 0) {
                break;
            }
        }
        
        Log::info('✅ Restructured Gentleman Agreement payment schedule generated successfully (NO INTEREST)');
    }

/**
 * Calculate current financial position for Gentleman Agreement
 * Updated to properly handle penalty status checking
 */
private function calculateCurrentFinancialPosition($agreement)
{
    // Get total PENDING penalties only (not paid ones)
    $totalPenalties = $agreement->penalties()
        ->where('status', 'pending')
        ->sum('penalty_amount');
    
    // Outstanding balance from the agreement
    $outstandingBalance = floatval($agreement->outstanding_balance);
    
    // Calculate total outstanding (balance + pending penalties only)
    $totalOutstanding = $outstandingBalance + $totalPenalties;
    
    // Get paid penalties for reference
    $paidPenalties = $agreement->penalties()
        ->where('status', 'paid')
        ->sum('penalty_amount');
    
    return [
        'original_loan_amount' => floatval($agreement->loan_amount),
        'outstanding_balance' => $outstandingBalance,
        'total_penalties' => $totalPenalties, // Only pending penalties
        'paid_penalties' => $paidPenalties, // For reference
        'total_outstanding' => $totalOutstanding,
        'amount_paid' => floatval($agreement->amount_paid),
        'vehicle_price' => floatval($agreement->vehicle_price),
        'deposit_amount' => floatval($agreement->deposit_amount),
        'monthly_payment' => floatval($agreement->monthly_payment),
        'progress_percentage' => floatval($agreement->payment_progress),
        'payments_made' => intval($agreement->payments_made),
        'payments_remaining' => intval($agreement->payments_remaining),
    ];
}
    
private function getRemainingMonths($agreement)
{
    // For gentleman agreements, this is typically based on remaining payments
    $remainingPayments = intval($agreement->payments_remaining);
    
    // If no remaining payments data, calculate based on outstanding balance
    if ($remainingPayments <= 0 && $agreement->outstanding_balance > 0) {
        $monthlyPayment = floatval($agreement->monthly_payment);
        if ($monthlyPayment > 0) {
            $remainingPayments = ceil(floatval($agreement->outstanding_balance) / $monthlyPayment);
        }
    }
    
    return max(1, $remainingPayments); // Ensure at least 1 month
}
    
    private function getNextDueDate($agreement)
    {
        $nextSchedule = PaymentSchedule::where('agreement_id', $agreement->id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->first();
        
        return $nextSchedule ? $nextSchedule->due_date : now()->addMonth();
    }
    
    private function clearUnpaidSchedules($agreementId)
    {
        // Use Laravel's built-in soft delete - only sets deleted_at
        $deletedCount = PaymentSchedule::where('agreement_id', $agreementId)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->delete();
            
        Log::info('Soft deleted unpaid schedules for Gentleman Agreement', [
            'agreement_id' => $agreementId,
            'soft_deleted_count' => $deletedCount
        ]);
        
        return $deletedCount;
    }
    
    private function captureOriginalTerms($agreement)
    {
        return [
            'duration_months' => $this->getRemainingMonths($agreement),
            'monthly_payment' => $agreement->monthly_payment,
            'completion_date' => $agreement->expected_completion_date,
            'outstanding_balance' => $agreement->outstanding_balance,
            'no_interest' => true
        ];
    }
    
    private function updateAgreementAfterRestructuring($agreement, $newMonthlyPayment, $newDuration, $newLoanAmount)
    {
        $updateData = [
            'monthly_payment' => round($newMonthlyPayment, 2),
            'outstanding_balance' => $newLoanAmount,
            'loan_amount' => $newLoanAmount,
            'duration_months' => $newDuration,
            'expected_completion_date' => now()->addMonths($newDuration),
            'payments_remaining' => $newDuration,
            'updated_at' => now()
        ];
        
        DB::table('gentlemanagreements')
            ->where('id', $agreement->id)
            ->update($updateData);
            
        Log::info('Updated Gentleman Agreement after restructuring', [
            'agreement_id' => $agreement->id,
            'new_monthly_payment' => $newMonthlyPayment,
            'new_duration' => $newDuration,
            'new_loan_amount' => $newLoanAmount
        ]);
    }

   

    private function getCarDetails($car_type, $car_id)
    {
        try {
            if ($car_type === 'import') {
                $car = CarImport::find($car_id);
                if ($car) {
                    return "{$car->year} {$car->make} {$car->model}";
                }
            } else {
                $car = CustomerVehicle::find($car_id);
                if ($car) {
                    return "{$car->year} {$car->vehicle_make} {$car->model}";
                }
            }
            
            return "your selected vehicle";
            
        } catch (\Exception $e) {
            Log::error('Error getting car details: ' . $e->getMessage());
            return "your selected vehicle";
        }
    }

    /**
     * API endpoints for Gentleman Agreement loan restructuring
     */
    
    /**
     * Get financial summary for Gentleman Agreement (API endpoint)
     */
    public function getFinancialSummary($agreementId)
    {
        try {
            $agreement = GentlemanAgreement::with(['paymentSchedule', 'penalties'])->findOrFail($agreementId);
            $financialSummary = $this->calculateCurrentFinancialPosition($agreement);
            
            return response()->json([
                'success' => true,
                'financial_summary' => $financialSummary,
                'no_interest' => true,
                'restructuring_fee_rate' => self::DEFAULT_RESTRUCTURING_FEE_RATE
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
  
    
    /**
     * Validate Gentleman Agreement restructured schedule
     */
    public function validateRestructuredSchedule($agreementId)
    {
        try {
            $agreement = GentlemanAgreement::findOrFail($agreementId);
            
            // Check recent payment schedules to ensure they have no interest
            $recentSchedules = PaymentSchedule::where('agreement_id', $agreementId)
                ->where('created_at', '>', now()->subDays(1)) // Recent schedules
                ->orderBy('installment_number')
                ->take(5)
                ->get();
                
            $validation = [
                'agreement_id' => $agreementId,
                'agreement_type' => 'gentleman_agreement',
                'schedules_validated' => [],
                'all_valid' => true,
                'errors' => []
            ];
            
            foreach ($recentSchedules as $schedule) {
                // For Gentleman Agreement, interest should always be 0
                $expectedInterest = 0;
                $actualInterest = $schedule->interest_amount;
                
                $isValid = $actualInterest == 0;
                
                $validation['schedules_validated'][] = [
                    'installment_number' => $schedule->installment_number,
                    'principal_amount' => $schedule->principal_amount,
                    'expected_interest' => $expectedInterest,
                    'actual_interest' => $actualInterest,
                    'total_amount' => $schedule->total_amount,
                    'balance_after' => $schedule->balance_after,
                    'is_valid' => $isValid
                ];
                
                if (!$isValid) {
                    $validation['all_valid'] = false;
                    $validation['errors'][] = "Installment {$schedule->installment_number} has interest (should be 0 for Gentleman Agreement)";
                }
            }
            
            return response()->json([
                'success' => true,
                'validation' => $validation,
                'no_interest_required' => true
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}