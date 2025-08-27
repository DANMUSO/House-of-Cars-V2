<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HirePurchaseAgreement;
use App\Models\HirePurchasePayment;
use App\Models\GentlemanAgreement;
use App\Models\LoanSetting;
use App\Models\PaymentSchedule;
use App\Models\VehicleInspection;
use App\Models\CarImport;
use App\Models\CustomerVehicle;
use App\Models\InCash;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // ADD THIS LINE
use Carbon\Carbon;

class HirePurchasesController extends Controller
{
    // Constants (removed hardcoded TRACKING_FEE)
    const COMMISSION_HOC = 10000;         // KES 10,000 HOC commission
    const COMMISSION_SALES = 15000;       // KES 15,000 Sales commission

    public function index()
    {
        $hirePurchases = HirePurchaseAgreement::with(['payments', 'approvedBy', 'carImport', 'customerVehicle'])
            ->orderBy('created_at', 'desc')
            ->get();

       // Collect IDs from InCash, HirePurchaseAgreement, and GentlemanAgreement
        $importedIds = array_merge(
            InCash::whereNotNull('imported_id')->pluck('imported_id')->toArray(),
            HirePurchaseAgreement::whereNotNull('imported_id')->pluck('imported_id')->toArray(),
            GentlemanAgreement::whereNotNull('imported_id')->pluck('imported_id')->toArray()
        );

        $customerIds = array_merge(
            InCash::whereNotNull('customer_id')->pluck('customer_id')->toArray(),
            HirePurchaseAgreement::whereNotNull('customer_id')->pluck('customer_id')->toArray(),
            GentlemanAgreement::whereNotNull('customer_id')->pluck('customer_id')->toArray()
        );

        // Filter VehicleInspections where cars are not already in InCash, HirePurchase, or GentlemanAgreement
        $cars = VehicleInspection::with('carsImport', 'customerVehicle')
            ->whereDoesntHave('carsImport', function ($query) use ($importedIds) {
                $query->whereIn('id', $importedIds);
            })
            ->whereDoesntHave('customerVehicle', function ($query) use ($customerIds) {
                $query->whereIn('id', $customerIds);
            })
            ->latest()
            ->get();


        // Group imported and customer cars for display (for existing agreements)
        $importCars = CarImport::whereIn('id', $hirePurchases->where('car_type', 'import')->pluck('car_id'))->get()->keyBy('id');
        $customerCars = CustomerVehicle::whereIn('id', $hirePurchases->where('car_type', 'customer')->pluck('car_id'))->get()->keyBy('id');

        return view('hirepurchase.index', compact('cars', 'hirePurchases', 'importCars', 'customerCars'));
    }
    public function storeLumpSumPayment(Request $request)
    {
        try {
            Log::info('=== CORRECTED LUMP SUM PAYMENT START ===', $request->all());
            
            $validated = $request->validate([
                'agreement_id' => 'required|integer|exists:hire_purchase_agreements,id',
                'payment_amount' => 'required|numeric|min:1',
                'payment_date' => 'required|date',
                'payment_method' => 'required|string|in:cash,bank_transfer,mpesa,cheque,card',
                'payment_reference' => 'nullable|string|max:100',
                'payment_notes' => 'nullable|string',
                'reschedule_option' => 'required|string|in:reduce_duration,reduce_installment',
            ]);
    
            DB::beginTransaction();
    
            $agreement = HirePurchaseAgreement::with('paymentSchedule')->findOrFail($request->agreement_id);
            
            if ($agreement->status === 'completed') {
                throw new \Exception('Cannot make payment on a completed agreement');
            }
    
            $currentOutstanding = $this->calculateCurrentOutstandingFromSchedule($agreement);
            
            if ($request->payment_amount > $currentOutstanding) {
                throw new \Exception('Payment amount exceeds outstanding balance of KSh ' . number_format($currentOutstanding, 2));
            }
    
            // Store original terms
            $originalTerms = $this->captureOriginalTerms($agreement);
    
            // Create payment record
            $paymentData = $this->createLumpSumPaymentRecord($agreement, $request, $currentOutstanding);
            Log::info('✅ Payment record created', ['payment_id' => $paymentData['payment_id']]);
    
            // Apply lump sum with PRIORITY SYSTEM
            $applicationResult = $this->applyLumpSumToFirstDueOnly($agreement->id, $request->payment_amount, $request->payment_date);
            Log::info('✅ Payment applied to first due payment only', $applicationResult);
    
            // Calculate new principal balance correctly
            $newPrincipalBalance = $this->calculateCorrectNewPrincipalBalance($agreement, $applicationResult);
            Log::info('✅ New principal balance calculated correctly', ['balance' => $newPrincipalBalance]);
    
            // Check if loan is completed
            if ($newPrincipalBalance <= 0) {
                $reschedulingResult = [
                    'reschedule_type' => 'loan_completed',
                    'new_outstanding_balance' => 0,
                    'savings_message' => 'Loan completed successfully!',
                    'completion' => true
                ];
                
                $agreement->update([
                    'status' => 'completed',
                    'amount_paid' => $agreement->amount_paid + $request->payment_amount,
                    'outstanding_balance' => 0,
                    'last_payment_date' => $request->payment_date
                ]);
                
                Log::info('✅ Loan completed');
            } else {
                // Perform CORRECTED rescheduling
                $reschedulingResult = $this->performCorrectRescheduling(
                    $agreement,
                    $newPrincipalBalance,
                    $request->reschedule_option,
                    $applicationResult
                );
                Log::info('✅ Rescheduling completed correctly', $reschedulingResult);
    
                // Update agreement
                $this->updateAgreementAfterRescheduling($agreement, $reschedulingResult, $request->payment_amount);
                Log::info('✅ Agreement updated');
            }
    
            // Create history record
            $reschedulingId = $this->createDetailedReschedulingHistory(
                $agreement,
                $paymentData['payment_id'],
                $request,
                $originalTerms,
                $reschedulingResult,
                $applicationResult
            );
            Log::info('✅ History created', ['rescheduling_id' => $reschedulingId]);
    
            DB::commit();
            Log::info('✅ Transaction completed successfully');
    
            return response()->json([
                'success' => true,
                'message' => $reschedulingResult['completion'] ?? false ? 
                            'Lump sum payment recorded and loan completed!' :
                            'Lump sum payment recorded and loan rescheduled successfully!',
                'payment_breakdown' => [
                    'first_payment_covered' => $applicationResult['total_applied_to_schedule'],
                    'principal_reduction' => $applicationResult['remaining_for_principal_reduction'],
                    'affected_payment' => $applicationResult['affected_installment'] ?? 'none'
                ],
                'rescheduling_details' => $reschedulingResult,
                'new_balance' => $reschedulingResult['new_outstanding_balance'] ?? 0,
                'payment_id' => $paymentData['payment_id'],
                'completion' => $reschedulingResult['completion'] ?? false
            ]);
    
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('❌ Error in corrected lump sum payment', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
 * CORRECTED: Apply lump sum to ONLY first due payment + principal reduction
 */
private function applyLumpSumToFirstDueOnly($agreementId, $paymentAmount, $paymentDate)
{
    $remainingAmount = $paymentAmount;
    
    Log::info('=== APPLYING TO FIRST DUE PAYMENT ONLY ===', [
        'agreement_id' => $agreementId,
        'payment_amount' => $paymentAmount
    ]);
    
    // Get ONLY the first due payment (overdue > partial > pending)
    $firstDuePayment = PaymentSchedule::where('agreement_id', $agreementId)
        ->whereIn('status', ['overdue', 'partial', 'pending'])
        ->orderByRaw("
            CASE 
                WHEN status = 'overdue' THEN 1 
                WHEN status = 'partial' THEN 2 
                WHEN status = 'pending' THEN 3 
            END
        ")
        ->orderBy('due_date', 'asc')
        ->first();
    
    $totalAppliedToSchedule = 0;
    $affectedInstallment = null;
    
    // Apply to ONLY the first due payment
    if ($firstDuePayment && $remainingAmount > 0) {
        $currentPaid = $firstDuePayment->amount_paid ?? 0;
        $amountDue = $firstDuePayment->total_amount - $currentPaid;
        
        if ($amountDue > 0) {
            $appliedAmount = min($remainingAmount, $amountDue);
            $newAmountPaid = $currentPaid + $appliedAmount;
            $newStatus = ($newAmountPaid >= $firstDuePayment->total_amount) ? 'paid' : 'partial';
            
            $firstDuePayment->update([
                'amount_paid' => $newAmountPaid,
                'status' => $newStatus,
                'date_paid' => $newStatus === 'paid' ? $paymentDate : $firstDuePayment->date_paid,
                'days_overdue' => $newStatus === 'paid' ? 0 : $firstDuePayment->days_overdue
            ]);
            
            $remainingAmount -= $appliedAmount;
            $totalAppliedToSchedule = $appliedAmount;
            $affectedInstallment = $firstDuePayment->installment_number;
            
            Log::info("Applied to first due payment {$firstDuePayment->installment_number}:", [
                'applied' => $appliedAmount,
                'new_status' => $newStatus,
                'remaining_for_principal' => $remainingAmount
            ]);
        }
    }
    
    Log::info('First due payment application result:', [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $remainingAmount,
        'affected_installment' => $affectedInstallment
    ]);
    
    return [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $remainingAmount,
        'affected_installment' => $affectedInstallment
    ];
    // After updating the payment schedule, add this debug
Log::info('Payment schedule updated:', [
    'installment_number' => $firstDuePayment->installment_number,
    'old_amount_paid' => $currentPaid,
    'new_amount_paid' => $newAmountPaid,
    'old_status' => $firstDuePayment->status,
    'new_status' => $newStatus
]);

// Verify the update actually happened
$verifyUpdate = PaymentSchedule::find($firstDuePayment->id);
Log::info('Verification - schedule after update:', [
    'amount_paid' => $verifyUpdate->amount_paid,
    'status' => $verifyUpdate->status
]);
}

/**
 * CORRECTED: Calculate new principal balance after lump sum application
 * ENHANCED: With detailed logging to debug the 0 balance issue
 */
private function calculateCorrectNewPrincipalBalance($agreement, $applicationResult)
{
    Log::info('=== CALCULATING CORRECT NEW PRINCIPAL BALANCE ===');
    
    // Get ALL unpaid/partial schedules (after the application)
    $unpaidSchedules = $agreement->paymentSchedule()
        ->whereIn('status', ['pending', 'partial', 'overdue'])
        ->get();
    
    Log::info('Unpaid schedules found:', [
        'count' => $unpaidSchedules->count(),
        'statuses' => $unpaidSchedules->pluck('status')->toArray()
    ]);
    
    if ($unpaidSchedules->count() == 0) {
        Log::warning('🚨 NO UNPAID SCHEDULES FOUND - This might be the issue!');
        
        // Check all schedules to see their status
        $allSchedules = $agreement->paymentSchedule()->get();
        Log::info('All schedules status breakdown:', [
            'total_schedules' => $allSchedules->count(),
            'paid' => $allSchedules->where('status', 'paid')->count(),
            'pending' => $allSchedules->where('status', 'pending')->count(),
            'partial' => $allSchedules->where('status', 'partial')->count(),
            'overdue' => $allSchedules->where('status', 'overdue')->count()
        ]);
        
        // If ALL schedules are paid, return 0 (loan complete)
        if ($allSchedules->where('status', 'paid')->count() == $allSchedules->count()) {
            Log::info('All schedules are paid - loan is complete');
            return 0;
        }
    }
    
    $totalRemainingPrincipal = 0;
    
    foreach ($unpaidSchedules as $schedule) {
        // Calculate how much principal is still unpaid
        $paidRatio = $schedule->total_amount > 0 ? 
                    ($schedule->amount_paid / $schedule->total_amount) : 0;
        $unpaidPrincipal = $schedule->principal_amount * (1 - $paidRatio);
        $totalRemainingPrincipal += $unpaidPrincipal;
        
        Log::info("Schedule {$schedule->installment_number} remaining principal:", [
            'status' => $schedule->status,
            'total_principal' => $schedule->principal_amount,
            'amount_paid' => $schedule->amount_paid,
            'total_amount' => $schedule->total_amount,
            'paid_ratio' => round($paidRatio, 4),
            'unpaid_principal' => round($unpaidPrincipal, 2)
        ]);
    }
    
    // Apply principal reduction from lump sum
    $principalReduction = $applicationResult['remaining_for_principal_reduction'];
    $newPrincipalBalance = max(0, $totalRemainingPrincipal - $principalReduction);
    
    Log::info('🔥 CRITICAL: Final principal calculation:', [
        'total_remaining_principal' => $totalRemainingPrincipal,
        'principal_reduction_from_lump_sum' => $principalReduction,
        'new_principal_balance' => $newPrincipalBalance,
        'is_balance_zero' => $newPrincipalBalance == 0 ? 'YES - POTENTIAL ISSUE' : 'NO - CORRECT'
    ]);
    
    return $newPrincipalBalance;
}
private function performCorrectRescheduling($agreement, $newPrincipalBalance, $rescheduleOption, $applicationResult)
{
    Log::info('=== PERFORMING CORRECT RESCHEDULING ===', [
        'agreement_id' => $agreement->id,
        'new_principal_balance' => $newPrincipalBalance,
        'reschedule_option' => $rescheduleOption
    ]);
    
    if ($newPrincipalBalance <= 0) {
        return [
            'reschedule_type' => 'loan_completed',
            'new_outstanding_balance' => 0,
            'completion' => true,
            'savings_message' => 'Loan completed successfully!'
        ];
    }
    
    // ❌ DON'T clear schedules here for reduce payment!
    // $this->clearFutureUnpaidSchedules($agreement->id);
    
    // Get remaining months BEFORE clearing schedules
    $remainingMonths = $this->getRemainingMonths($agreement);
    $nextDueDate = $this->getNextDueDate($agreement);
    
    // Get original monthly interest rate
    $originalMonthlyRate = $this->getOriginalMonthlyInterestRate($agreement);
    $monthlyInterestDecimal = $originalMonthlyRate / 100;
    
    Log::info('Rescheduling parameters:', [
        'remaining_months' => $remainingMonths,  // Should be 23, not 1!
        'monthly_interest_rate' => $originalMonthlyRate . '%',
        'next_due_date' => $nextDueDate
    ]);
    
    if ($rescheduleOption === 'reduce_duration') {
        // For reduce duration, clear schedules first
        $this->clearFutureUnpaidSchedules($agreement->id);
        return $this->correctReduceDuration($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal);
    } else {
        // For reduce payment, clear schedules INSIDE the method
        return $this->correctReduceInstallment($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal);
    }
}
/**
 * CORRECTED: Reduce Duration - Keep payment same, reduce months
 */
private function correctReduceDuration($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal)
{
    $currentMonthlyPayment = $agreement->monthly_payment;
    
    Log::info('=== CORRECT REDUCE DURATION ===', [
        'new_principal_balance' => $newPrincipalBalance,
        'current_monthly_payment' => $currentMonthlyPayment,
        'monthly_interest_decimal' => $monthlyInterestDecimal
    ]);
    
    // Calculate new duration using PMT formula: n = -ln(1 - (P*r)/PMT) / ln(1+r)
    if ($newPrincipalBalance <= 0) {
        $newDuration = 0;
    } else {
        $factor = ($newPrincipalBalance * $monthlyInterestDecimal) / $currentMonthlyPayment;
        if ($factor >= 1) {
            $newDuration = $remainingMonths;
        } else {
            $newDuration = ceil(-log(1 - $factor) / log(1 + $monthlyInterestDecimal));
        }
    }
    
    $newDuration = max(1, $newDuration);
    $durationReduction = max(0, $remainingMonths - $newDuration);
    
    // Generate new schedule
    if ($newDuration > 0) {
        $this->generateCorrectPaymentSchedule(
            $agreement->id,
            $newPrincipalBalance,
            $currentMonthlyPayment,
            $newDuration,
            $monthlyInterestDecimal,
            $nextDueDate
        );
    }
    
    return [
        'reschedule_type' => 'reduce_duration',
        'original_remaining_months' => $remainingMonths,
        'new_duration' => $newDuration,
        'duration_reduction' => $durationReduction,
        'monthly_payment' => $currentMonthlyPayment,
        'new_outstanding_balance' => $newPrincipalBalance,
        'new_completion_date' => $newDuration > 0 ? 
            Carbon::parse($nextDueDate)->addMonths($newDuration - 1) : now(),
        'savings_message' => "Loan duration reduced by {$durationReduction} months"
    ];
}

private function correctReduceInstallment($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal)
{
    Log::info('=== CORRECT REDUCE INSTALLMENT ===', [
        'new_principal_balance' => $newPrincipalBalance,
        'remaining_months' => $remainingMonths,  // This should be 23!
        'monthly_interest_decimal' => $monthlyInterestDecimal
    ]);
    
    if ($newPrincipalBalance <= 0) {
        return [
            'reschedule_type' => 'loan_completed',
            'new_outstanding_balance' => 0,
            'completion' => true,
            'savings_message' => 'Loan completed successfully!'
        ];
    }

    if ($remainingMonths <= 1) {
        Log::warning('Only 1 month remaining - unusual for reduce payment');
        $remainingMonths = max(2, $agreement->duration_months - 1); // Force at least 2 months
    }
    
    $originalPayment = $agreement->monthly_payment;
    
    // Calculate new monthly payment with 23 months, not 1!
    $newMonthlyPayment = $this->calculatePMTSafe(
        $newPrincipalBalance,    // 347522.50
        $monthlyInterestDecimal, // 0.0429
        $remainingMonths         // Should be 23, not 1!
    );
    
    $paymentReduction = max(0, $originalPayment - $newMonthlyPayment);
    
    Log::info('Payment calculation with correct months:', [
        'principal' => $newPrincipalBalance,
        'months' => $remainingMonths,
        'rate' => $monthlyInterestDecimal,
        'original_payment' => $originalPayment,
        'new_payment' => $newMonthlyPayment,
        'reduction' => $paymentReduction
    ]);
    
    // NOW clear schedules after calculation
    $this->clearFutureUnpaidSchedules($agreement->id);
    
    // Generate new schedule
    if ($remainingMonths > 0) {
        $this->generateCorrectPaymentSchedule(
            $agreement->id,
            $newPrincipalBalance,
            $newMonthlyPayment,
            $remainingMonths,
            $monthlyInterestDecimal,
            $nextDueDate
        );
    }
    
    return [
        'reschedule_type' => 'reduce_installment',
        'original_monthly_payment' => $originalPayment,
        'new_monthly_payment' => round($newMonthlyPayment, 2),
        'payment_reduction' => round($paymentReduction, 2),
        'remaining_duration' => $remainingMonths,
        'new_outstanding_balance' => $newPrincipalBalance,
        'savings_message' => "Monthly payment reduced by KSh " . number_format($paymentReduction, 2)
    ];
}
private function generateCorrectPaymentSchedule($agreementId, $principalAmount, $monthlyPayment, $duration, $monthlyInterestDecimal, $startDate)
{
    Log::info('=== GENERATING CORRECT PAYMENT SCHEDULE ===', [
        'principal_amount' => $principalAmount,
        'monthly_payment' => $monthlyPayment,
        'duration' => $duration,
        'monthly_interest_decimal' => $monthlyInterestDecimal
    ]);
    
    // CRITICAL: Validate interest rate
    if ($monthlyInterestDecimal <= 0) {
        throw new \Exception('CRITICAL: Monthly interest rate is zero! Cannot generate schedule.');
    }
    
    $remainingPrincipal = $principalAmount;
    
    // Get the next installment number
    $lastInstallment = PaymentSchedule::where('agreement_id', $agreementId)
        ->max('installment_number') ?? 0;
    
    for ($month = 1; $month <= $duration; $month++) {
        // Calculate interest on REMAINING principal
        $monthlyInterest = $remainingPrincipal * $monthlyInterestDecimal;
        $monthlyPrincipal = $monthlyPayment - $monthlyInterest;
        
        // VALIDATION: Ensure positive interest
        if ($monthlyInterest <= 0) {
            throw new \Exception('Invalid interest calculation - got zero interest');
        }
        
        // Ensure principal payment is positive
        if ($monthlyPrincipal < 0) {
            $monthlyPrincipal = 0;
            $monthlyInterest = $monthlyPayment;
        }
        
        // For the last payment, adjust to pay off exactly
        if ($month == $duration || $monthlyPrincipal >= $remainingPrincipal) {
            $monthlyPrincipal = $remainingPrincipal;
            $actualPayment = $monthlyPrincipal + $monthlyInterest;
            $newRemainingPrincipal = 0;
        } else {
            $actualPayment = $monthlyPayment;
            $newRemainingPrincipal = $remainingPrincipal - $monthlyPrincipal;
        }
        
        // Create payment schedule entry
        PaymentSchedule::create([
            'agreement_id' => $agreementId,
            'installment_number' => $lastInstallment + $month,
            'due_date' => Carbon::parse($startDate)->addMonths($month - 1),
            'principal_amount' => round($monthlyPrincipal, 2),
            'interest_amount' => round($monthlyInterest, 2),
            'total_amount' => round($actualPayment, 2),
            'balance_after' => round($newRemainingPrincipal, 2),
            'status' => 'pending',
            'amount_paid' => 0,
            'date_paid' => null,
            'days_overdue' => 0
        ]);
        
        $remainingPrincipal = $newRemainingPrincipal;
        
        if ($remainingPrincipal <= 0) {
            break;
        }
    }
    
    Log::info('✅ Correct payment schedule generated');
}

/**
 * FIXED: Calculate reduce payment option with proper validation
 */
private function calculateReducePaymentOptionFixed($principalBalance, $duration, $monthlyInterestDecimal, $currentPayment)
{
    Log::info('=== FIXED REDUCE PAYMENT CALCULATION ===', [
        'principal_balance' => $principalBalance,
        'duration' => $duration,
        'monthly_interest_decimal' => $monthlyInterestDecimal,
        'current_payment' => $currentPayment
    ]);
    
    // Handle edge cases first
    if ($principalBalance <= 0) {
        Log::info('Principal balance is zero or negative, no payment needed');
        return [
            'new_payment' => 0,
            'payment_reduction' => $currentPayment
        ];
    }
    
    if ($duration <= 0) {
        Log::warning('Invalid duration, keeping current payment');
        return [
            'new_payment' => $currentPayment,
            'payment_reduction' => 0
        ];
    }
    
    if ($monthlyInterestDecimal < 0) {
        Log::error('Invalid interest rate', ['rate' => $monthlyInterestDecimal]);
        throw new \Exception('Invalid monthly interest rate: ' . ($monthlyInterestDecimal * 100) . '%');
    }
    
    // Calculate new payment using PMT formula with enhanced validation
    try {
        $newPayment = $this->calculatePMTSafe($principalBalance, $monthlyInterestDecimal, $duration);
        
        // Additional validation - ensure reasonable payment
        if ($newPayment <= 0) {
            Log::warning('Calculated payment is zero or negative', ['calculated' => $newPayment]);
            // Fallback: use simple division with minimum payment
            $newPayment = max(($principalBalance / $duration), ($principalBalance * 0.05)); // At least 5% of principal
        }
        
        // Ensure new payment is less than current payment (otherwise no benefit)
        if ($newPayment >= $currentPayment) {
            Log::warning('Calculated payment is not lower than current payment', [
                'calculated' => $newPayment,
                'current' => $currentPayment
            ]);
            // Set to 90% of current payment as a reasonable reduction
            $newPayment = $currentPayment * 0.9;
        }
        
        $paymentReduction = $currentPayment - $newPayment;
        
        // Final validation - ensure positive reduction
        if ($paymentReduction <= 0) {
            Log::warning('No payment reduction achieved, forcing minimum reduction');
            $newPayment = $currentPayment * 0.95; // 5% reduction minimum
            $paymentReduction = $currentPayment - $newPayment;
        }
        
        Log::info('Payment reduction calculation successful:', [
            'new_payment' => $newPayment,
            'payment_reduction' => $paymentReduction,
            'reduction_percentage' => round(($paymentReduction / $currentPayment) * 100, 2)
        ]);
        
        return [
            'new_payment' => round($newPayment, 2),
            'payment_reduction' => round($paymentReduction, 2)
        ];
        
    } catch (\Exception $e) {
        Log::error('Error in payment calculation:', [
            'error' => $e->getMessage(),
            'principal' => $principalBalance,
            'duration' => $duration,
            'rate' => $monthlyInterestDecimal
        ]);
        
        // Fallback to a reasonable reduction
        $fallbackPayment = max(($currentPayment * 0.8), ($principalBalance / $duration)); // 20% reduction or minimum viable
        return [
            'new_payment' => round($fallbackPayment, 2),
            'payment_reduction' => round($currentPayment - $fallbackPayment, 2)
        ];
    }
}

/**
 * FIXED: Enhanced PMT calculation with comprehensive error handling
 */
private function calculatePMTSafe($loanAmount, $monthlyRate, $termMonths)
{
    Log::info('Calculating PMT with enhanced safety checks:', [
        'loan_amount' => $loanAmount,
        'monthly_rate' => $monthlyRate,
        'term_months' => $termMonths
    ]);
    
    // CRITICAL: Enhanced input validation
    if ($loanAmount <= 0) {
        throw new \Exception('Invalid loan amount: ' . $loanAmount);
    }
    
    if ($termMonths <= 0) {
        Log::warning('Invalid term months received: ' . $termMonths . ', setting to 1');
        $termMonths = 1; // FIX: Don't throw exception, just set to 1
    }
    
    if ($monthlyRate < 0) {
        throw new \Exception('Invalid monthly rate: ' . $monthlyRate);
    }
    
    // Handle zero interest rate
    if ($monthlyRate == 0 || $monthlyRate < 0.0001) {
        $pmt = $loanAmount / $termMonths;
        Log::info('Zero/minimal interest rate, using simple division:', ['pmt' => $pmt]);
        return $pmt;
    }
    
    // For very short terms (1-2 months), use simplified calculation
    if ($termMonths <= 2) {
        $totalInterest = $loanAmount * $monthlyRate * $termMonths;
        $pmt = ($loanAmount + $totalInterest) / $termMonths;
        Log::info('Short term loan, using simplified calculation:', ['pmt' => $pmt]);
        return $pmt;
    }
    
    // Rest of your PMT calculation code remains the same...
    try {
        $factor = pow(1 + $monthlyRate, $termMonths);
        
        if (!is_finite($factor) || $factor <= 1) {
            throw new \Exception('Mathematical overflow in PMT calculation');
        }
        
        $numerator = $loanAmount * ($monthlyRate * $factor);
        $denominator = ($factor - 1);
        
        if ($denominator == 0) {
            throw new \Exception('Zero denominator in PMT calculation');
        }
        
        $pmt = $numerator / $denominator;
        
        if (!is_finite($pmt) || $pmt <= 0) {
            throw new \Exception('Invalid PMT result: ' . $pmt);
        }
        
        return round($pmt, 2);
        
    } catch (\Exception $e) {
        Log::error('PMT calculation failed:', ['error' => $e->getMessage()]);
        
        // Fallback calculation
        $simplePmt = $loanAmount / $termMonths;
        $interestBuffer = $simplePmt * $monthlyRate * 0.5;
        return round($simplePmt + $interestBuffer, 2);
    }
}

/**
 * CORRECTED: getReschedulingOptions with proper calculations
 */
public function getReschedulingOptions(Request $request)
{
    try {
        $agreementId = $request->get('agreement_id');
        $lumpSumAmount = $request->get('lump_sum_amount');
        
        Log::info('Getting corrected rescheduling options:', [
            'agreement_id' => $agreementId,
            'lump_sum_amount' => $lumpSumAmount
        ]);
        
        if (!$agreementId || !$lumpSumAmount) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        
        $agreement = HirePurchaseAgreement::with('paymentSchedule')->findOrFail($agreementId);
        $currentOutstanding = $this->calculateCurrentOutstandingFromSchedule($agreement);
        
        // Check completion
        if ($lumpSumAmount >= $currentOutstanding) {
            return response()->json([
                'completion' => true,
                'message' => 'This payment will complete the loan',
                'current_outstanding' => $currentOutstanding,
                'payment_amount' => $lumpSumAmount
            ]);
        }
        
        // Simulate first payment only application
        $simulationResult = $this->simulateFirstPaymentOnlyApplication($agreement, $lumpSumAmount);
        
        // Get current terms with original interest rate
        $currentMonthlyPayment = $agreement->monthly_payment;
        $originalMonthlyRate = $this->getOriginalMonthlyInterestRate($agreement);
        $monthlyInterestDecimal = $originalMonthlyRate / 100;
        $remainingMonths = $this->getRemainingMonths($agreement);
        // CRITICAL: Additional validation for options calculation
        if ($remainingMonths <= 0) {
            Log::warning('No remaining months for rescheduling options');
            return response()->json([
                'completion' => true,
                'message' => 'No remaining payments - loan appears to be complete',
                'current_outstanding' => $currentOutstanding
            ]);
        }
        Log::info('Current terms for calculation:', [
            'current_monthly_payment' => $currentMonthlyPayment,
            'original_monthly_rate' => $originalMonthlyRate,
            'remaining_months' => $remainingMonths,
            'remaining_principal' => $simulationResult['remaining_principal']
        ]);
        
        // Calculate options using SAME method for both
        $option1 = $this->calculateReduceDurationOption(
            $simulationResult['remaining_principal'], 
            $currentMonthlyPayment, 
            $monthlyInterestDecimal, 
            $remainingMonths
        );
        
        $option2 = $this->calculateReducePaymentOptionCorrected(
            $simulationResult['remaining_principal'], 
            $remainingMonths, 
            $monthlyInterestDecimal,
            $currentMonthlyPayment
        );
        
        Log::info('Options calculated successfully:', [
            'option1' => $option1,
            'option2' => $option2
        ]);
        
        return response()->json([
            'current_outstanding' => $currentOutstanding,
            'new_outstanding' => $simulationResult['remaining_principal'],
            'option_1' => [
                'type' => 'reduce_duration',
                'title' => 'Reduce Loan Duration',
                'current_duration' => $remainingMonths,
                'new_duration' => $option1['new_duration'],
                'duration_reduction' => $option1['duration_reduction'],
                'monthly_payment' => $currentMonthlyPayment,
                'description' => "Keep monthly payment at KSh " . number_format($currentMonthlyPayment, 2) . 
                               " and reduce duration by {$option1['duration_reduction']} months"
            ],
             'option_2' => [
                'type' => 'reduce_installment',
                'title' => 'Reduce Monthly Payment',
                'current_payment' => $currentMonthlyPayment,
                'original_monthly_payment' => $currentMonthlyPayment,
                'new_payment' => $option2['new_payment'],
                'new_monthly_payment' => $option2['new_payment'],
                'payment_reduction' => $option2['payment_reduction'],
                'duration' => $remainingMonths,
                'remaining_duration' => $remainingMonths,
                'description' => "Reduce monthly payment to KSh " . number_format($option2['new_payment'], 2) . 
                            " and save KSh " . number_format($option2['payment_reduction'], 2) . " monthly"
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in getReschedulingOptions:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => 'Failed to calculate rescheduling options: ' . $e->getMessage()
        ], 500);
    }
}
private function calculateReducePaymentOptionCorrected($principalBalance, $duration, $monthlyInterestDecimal, $currentPayment)
{
    Log::info('=== CORRECTED REDUCE PAYMENT CALCULATION ===', [
        'principal_balance' => $principalBalance,
        'duration' => $duration,
        'monthly_interest_decimal' => $monthlyInterestDecimal,
        'current_payment' => $currentPayment
    ]);
    
    // Handle edge cases
    if ($principalBalance <= 0) {
        Log::warning('Principal balance is zero or negative');
        return [
            'new_payment' => 0,
            'payment_reduction' => $currentPayment
        ];
    }
    
    if ($duration <= 0) {
        Log::warning('Duration is zero or negative');
        return [
            'new_payment' => $currentPayment,
            'payment_reduction' => 0
        ];
    }
    
    if ($monthlyInterestDecimal <= 0) {
        Log::warning('Monthly interest rate is zero or negative');
        return [
            'new_payment' => $currentPayment,
            'payment_reduction' => 0
        ];
    }
    
    // Calculate new payment using EXACT SAME PMT method as reduce duration
    try {
        $newPayment = $this->calculatePMTSafe($principalBalance, $monthlyInterestDecimal, $duration);
        
        Log::info('PMT calculation result:', [
            'calculated_new_payment' => $newPayment,
            'current_payment' => $currentPayment
        ]);
        
        // Ensure new payment is less than current payment
        if ($newPayment >= $currentPayment) {
            Log::warning('Calculated payment is not lower, adjusting', [
                'calculated' => $newPayment,
                'current' => $currentPayment
            ]);
            $newPayment = $currentPayment * 0.95; // 5% reduction minimum
        }
        
        $paymentReduction = $currentPayment - $newPayment;
        
        // Ensure positive reduction
        if ($paymentReduction <= 0) {
            Log::warning('No payment reduction achieved, forcing minimum');
            $newPayment = $currentPayment * 0.9; // 10% reduction minimum
            $paymentReduction = $currentPayment - $newPayment;
        }
        
        $result = [
            'new_payment' => round($newPayment, 2),
            'payment_reduction' => round($paymentReduction, 2)
        ];
        
        Log::info('✅ Payment reduction calculation successful:', [
            'result' => $result,
            'reduction_percentage' => round(($paymentReduction / $currentPayment) * 100, 2) . '%'
        ]);
        
        return $result;
        
    } catch (\Exception $e) {
        Log::error('❌ Error in payment calculation:', [
            'error' => $e->getMessage(),
            'principal' => $principalBalance,
            'duration' => $duration,
            'rate' => $monthlyInterestDecimal
        ]);
        
        // Fallback to reasonable reduction
        $fallbackPayment = $currentPayment * 0.8; // 20% reduction
        return [
            'new_payment' => round($fallbackPayment, 2),
            'payment_reduction' => round($currentPayment - $fallbackPayment, 2)
        ];
    }
}
/**
 * HELPER: Simulate first payment only application
 */
private function simulateFirstPaymentOnlyApplication($agreement, $paymentAmount)
{
    $remainingAmount = $paymentAmount;
    
    // Get ONLY the first due payment
    $firstDuePayment = $agreement->paymentSchedule()
        ->whereIn('status', ['overdue', 'partial', 'pending'])
        ->orderByRaw("
            CASE 
                WHEN status = 'overdue' THEN 1 
                WHEN status = 'partial' THEN 2 
                WHEN status = 'pending' THEN 3 
            END
        ")
        ->orderBy('due_date', 'asc')
        ->first();
    
    $totalAppliedToSchedule = 0;
    
    // Apply to ONLY the first due payment
    if ($firstDuePayment && $remainingAmount > 0) {
        $currentPaid = $firstDuePayment->amount_paid ?? 0;
        $amountDue = $firstDuePayment->total_amount - $currentPaid;
        
        if ($amountDue > 0) {
            $appliedAmount = min($remainingAmount, $amountDue);
            $remainingAmount -= $appliedAmount;
            $totalAppliedToSchedule = $appliedAmount;
        }
    }
    
    // Calculate remaining principal after first payment + principal reduction
    $unpaidSchedules = $agreement->paymentSchedule()
        ->whereIn('status', ['pending', 'partial', 'overdue'])
        ->get();
    
    $totalRemainingPrincipal = 0;
    foreach ($unpaidSchedules as $schedule) {
        // Simulate payment application to first payment only
        $simulatedAmountPaid = $schedule->amount_paid;
        if ($firstDuePayment && $schedule->installment_number == $firstDuePayment->installment_number) {
            $simulatedAmountPaid += $totalAppliedToSchedule;
        }
        
        $paidRatio = $schedule->total_amount > 0 ? 
                    ($simulatedAmountPaid / $schedule->total_amount) : 0;
        $unpaidPrincipal = $schedule->principal_amount * (1 - $paidRatio);
        $totalRemainingPrincipal += $unpaidPrincipal;
    }
    
    // Apply remaining amount to principal reduction
    $principalReduction = $remainingAmount;
    $finalRemainingPrincipal = max(0, $totalRemainingPrincipal - $principalReduction);
    
    return [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $principalReduction,
        'remaining_principal' => $finalRemainingPrincipal
    ];
}
/**
 * CORRECTED: Replace your existing applyLumpSumToSchedule method with this
 * Priority: Due dates → Partial → One next payment → Principal reduction
 */
private function applyLumpSumToSchedule($agreementId, $paymentAmount, $paymentDate)
{
    $remainingAmount = $paymentAmount;
    
    Log::info('=== PRIORITY LUMP SUM APPLICATION ===', [
        'agreement_id' => $agreementId,
        'payment_amount' => $paymentAmount
    ]);
    
    $appliedBreakdown = [];
    $totalAppliedToSchedule = 0;
    
    // PRIORITY 1: Pay ALL overdue payments (due date passed)
    $overduePayments = PaymentSchedule::where('agreement_id', $agreementId)
        ->where('status', 'overdue')
        ->orderBy('due_date', 'asc')
        ->get();
    
    foreach ($overduePayments as $overduePayment) {
        if ($remainingAmount <= 0) break;
        
        $result = $this->applyPaymentToSingleInstallment($overduePayment, $remainingAmount, $paymentDate);
        if ($result['applied'] > 0) {
            $appliedBreakdown[] = $result['breakdown'];
            $remainingAmount -= $result['applied'];
            $totalAppliedToSchedule += $result['applied'];
            
            Log::info("Paid overdue installment {$overduePayment->installment_number}:", [
                'due_date' => $overduePayment->due_date,
                'applied' => $result['applied'],
                'new_status' => $result['breakdown']['status_after']
            ]);
        }
    }
    
    // PRIORITY 2: Complete ALL partial payments
    $partialPayments = PaymentSchedule::where('agreement_id', $agreementId)
        ->where('status', 'partial')
        ->orderBy('due_date', 'asc')
        ->get();
    
    foreach ($partialPayments as $partialPayment) {
        if ($remainingAmount <= 0) break;
        
        $result = $this->applyPaymentToSingleInstallment($partialPayment, $remainingAmount, $paymentDate);
        if ($result['applied'] > 0) {
            $appliedBreakdown[] = $result['breakdown'];
            $remainingAmount -= $result['applied'];
            $totalAppliedToSchedule += $result['applied'];
            
            Log::info("Completed partial installment {$partialPayment->installment_number}:", [
                'previous_paid' => $partialPayment->amount_paid,
                'applied' => $result['applied'],
                'new_status' => $result['breakdown']['status_after']
            ]);
        }
    }
    
    // PRIORITY 3: Pay ONLY ONE next pending payment (with interest)
    if ($remainingAmount > 0) {
        $nextPendingPayment = PaymentSchedule::where('agreement_id', $agreementId)
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->first(); // ONLY the next one
        
        if ($nextPendingPayment) {
            $result = $this->applyPaymentToSingleInstallment($nextPendingPayment, $remainingAmount, $paymentDate);
            if ($result['applied'] > 0) {
                $appliedBreakdown[] = $result['breakdown'];
                $remainingAmount -= $result['applied'];
                $totalAppliedToSchedule += $result['applied'];
                
                Log::info("Paid next pending installment {$nextPendingPayment->installment_number}:", [
                    'principal_amount' => $nextPendingPayment->principal_amount,
                    'interest_amount' => $nextPendingPayment->interest_amount,
                    'applied' => $result['applied'],
                    'new_status' => $result['breakdown']['status_after'],
                    'remaining_for_principal' => $remainingAmount
                ]);
            }
        }
    }
    
    Log::info('Priority lump sum application result:', [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $remainingAmount,
        'overdue_payments_processed' => $overduePayments->count(),
        'partial_payments_completed' => $partialPayments->count(),
        'next_payment_processed' => isset($nextPendingPayment) ? 1 : 0,
        'total_installments_affected' => count($appliedBreakdown)
    ]);
    
    return [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $remainingAmount,
        'breakdown' => $appliedBreakdown,
        'summary' => [
            'overdue_processed' => $overduePayments->count(),
            'partial_completed' => $partialPayments->count(),
            'next_payment_processed' => isset($nextPendingPayment) ? 1 : 0
        ]
    ];
}

/**
 * NEW HELPER: Apply payment to a single installment (maintains interest handling)
 */
private function applyPaymentToSingleInstallment($installment, $availableAmount, $paymentDate)
{
    $currentPaid = $installment->amount_paid ?? 0;
    $amountDue = $installment->total_amount - $currentPaid;
    
    if ($amountDue <= 0) {
        return ['applied' => 0, 'breakdown' => null];
    }
    
    $appliedAmount = min($availableAmount, $amountDue);
    $newAmountPaid = $currentPaid + $appliedAmount;
    
    // Determine new status
    if ($newAmountPaid >= $installment->total_amount) {
        $newStatus = 'paid';
    } else {
        $newStatus = ($installment->status === 'overdue') ? 'overdue' : 'partial';
    }
    
    // Update the installment
    $installment->update([
        'amount_paid' => $newAmountPaid,
        'status' => $newStatus,
        'date_paid' => $newStatus === 'paid' ? $paymentDate : $installment->date_paid,
        'days_overdue' => $newStatus === 'paid' ? 0 : $installment->days_overdue
    ]);
    
    return [
        'applied' => $appliedAmount,
        'breakdown' => [
            'installment_number' => $installment->installment_number,
            'due_date' => $installment->due_date,
            'principal_amount' => $installment->principal_amount,
            'interest_amount' => $installment->interest_amount,
            'total_amount' => $installment->total_amount,
            'amount_applied' => $appliedAmount,
            'status_before' => $installment->status,
            'status_after' => $newStatus
        ]
    ];
}

/**
 * CORRECTED: Replace your existing calculateNewPrincipalBalance method
 */
private function calculateNewPrincipalBalance($agreement, $applicationResult)
{
    Log::info('=== CALCULATING NEW PRINCIPAL BALANCE ===');
    
    // Get ALL unpaid/partial schedules (after the priority application)
    $unpaidSchedules = $agreement->paymentSchedule()
        ->whereIn('status', ['pending', 'partial', 'overdue'])
        ->orderBy('installment_number')
        ->get();
    
    Log::info('Unpaid schedules after priority application:', ['count' => $unpaidSchedules->count()]);
    
    $totalRemainingPrincipal = 0;
    
    foreach ($unpaidSchedules as $schedule) {
        // Calculate how much principal is still unpaid in this schedule
        $paidRatio = $schedule->total_amount > 0 ? 
                    ($schedule->amount_paid / $schedule->total_amount) : 0;
        $unpaidPrincipal = $schedule->principal_amount * (1 - $paidRatio);
        $totalRemainingPrincipal += $unpaidPrincipal;
        
        Log::info("Schedule {$schedule->installment_number} remaining principal:", [
            'status' => $schedule->status,
            'total_principal' => $schedule->principal_amount,
            'amount_paid' => $schedule->amount_paid,
            'total_amount' => $schedule->total_amount,
            'paid_ratio' => round($paidRatio, 4),
            'unpaid_principal' => round($unpaidPrincipal, 2)
        ]);
    }
    
    // Apply principal reduction from lump sum
    $principalReduction = $applicationResult['remaining_for_principal_reduction'];
    $newPrincipalBalance = max(0, $totalRemainingPrincipal - $principalReduction);
    
    Log::info('Final principal calculation:', [
        'total_remaining_principal' => $totalRemainingPrincipal,
        'principal_reduction_from_lump_sum' => $principalReduction,
        'new_principal_balance' => $newPrincipalBalance
    ]);
    
    return $newPrincipalBalance;
}

/**
 * CORRECTED: Replace your existing performRescheduling method
 */
private function performRescheduling($agreement, $newPrincipalBalance, $rescheduleOption, $applicationResult)
{
    Log::info('=== RESCHEDULING WITH ORIGINAL INTEREST RATE ===', [
        'agreement_id' => $agreement->id,
        'new_principal_balance' => $newPrincipalBalance,
        'reschedule_option' => $rescheduleOption
    ]);
    
    // Clear ONLY future unpaid schedules (keep paid/partial ones)
    $this->clearFuturePaymentSchedules($agreement->id);
    
    // Get remaining months and next due date
    $remainingMonths = $this->getRemainingMonths($agreement);
    $nextDueDate = $this->getNextDueDate($agreement);
    
    // Get the ORIGINAL monthly interest rate
    $originalMonthlyRate = $this->getOriginalMonthlyInterestRate($agreement);
    $monthlyInterestDecimal = $originalMonthlyRate / 100;
    
    Log::info('Using ORIGINAL interest rate for rescheduling:', [
        'original_monthly_rate' => $originalMonthlyRate . '%',
        'monthly_interest_decimal' => $monthlyInterestDecimal,
        'remaining_months' => $remainingMonths
    ]);
    
    if ($monthlyInterestDecimal <= 0) {
        throw new \Exception('Invalid original monthly interest rate: ' . $originalMonthlyRate . '%');
    }
    
    if ($rescheduleOption === 'reduce_duration') {
        return $this->rescheduleLoanReduceDuration($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal);
    } else {
        return $this->rescheduleLoanReduceInstallment($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal);
    }
}

/**
 * NEW: Get original monthly interest rate from agreement
 */
private function getOriginalMonthlyInterestRate($agreement)
{
    // Priority 1: Use monthly_interest_rate field if it exists and is valid
    if (isset($agreement->monthly_interest_rate) && $agreement->monthly_interest_rate > 0) {
        Log::info('Using stored monthly_interest_rate field:', ['rate' => $agreement->monthly_interest_rate]);
        return $agreement->monthly_interest_rate;
    }
    
    // Priority 2: Check if interest_rate field contains monthly rate (typically 1-10%)
    if ($agreement->interest_rate > 0 && $agreement->interest_rate <= 10) {
        Log::info('Using interest_rate field as monthly rate:', ['rate' => $agreement->interest_rate]);
        return $agreement->interest_rate;
    }
    
    // Priority 3: Convert annual rate to monthly (if rate > 10%)
    if ($agreement->interest_rate > 10) {
        $monthlyRate = $agreement->interest_rate / 12;
        Log::info('Converting annual rate to monthly:', [
            'annual_rate' => $agreement->interest_rate,
            'monthly_rate' => $monthlyRate
        ]);
        return $monthlyRate;
    }
    
    // Priority 4: Calculate based on deposit percentage (fallback)
    $depositPercentage = ($agreement->deposit_amount / $agreement->vehicle_price) * 100;
    $monthlyRate = $depositPercentage >= 50 ? 4.29 : 4.50;
    
    Log::warning('Using fallback deposit-based rate calculation:', [
        'deposit_percentage' => $depositPercentage,
        'monthly_rate' => $monthlyRate
    ]);
    
    return $monthlyRate;
}

/**
 * CORRECTED: Replace your existing rescheduleLoanReduceDuration method
 */
private function rescheduleLoanReduceDuration($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal)
{
    $currentMonthlyPayment = $agreement->monthly_payment;
    
    Log::info('=== REDUCE DURATION WITH ORIGINAL INTEREST ===', [
        'new_principal_balance' => $newPrincipalBalance,
        'current_monthly_payment' => $currentMonthlyPayment,
        'original_interest_decimal' => $monthlyInterestDecimal,
        'original_remaining_months' => $remainingMonths
    ]);
    
    // Calculate new duration using PMT formula: n = -ln(1 - (P*r)/PMT) / ln(1+r)
    if ($newPrincipalBalance <= 0) {
        $newDuration = 0;
    } else {
        $factor = ($newPrincipalBalance * $monthlyInterestDecimal) / $currentMonthlyPayment;
        if ($factor >= 1) {
            $newDuration = $remainingMonths;
        } else {
            $newDuration = ceil(-log(1 - $factor) / log(1 + $monthlyInterestDecimal));
        }
    }
    
    $newDuration = max(1, $newDuration);
    $durationReduction = max(0, $remainingMonths - $newDuration);
    
    // Generate new schedule with ORIGINAL interest rate
    if ($newDuration > 0) {
        $this->generateNewPaymentSchedule(
            $agreement->id,
            $newPrincipalBalance,
            $currentMonthlyPayment,
            $newDuration,
            $monthlyInterestDecimal,
            $nextDueDate
        );
    }
    
    return [
        'reschedule_type' => 'reduce_duration',
        'original_remaining_months' => $remainingMonths,
        'new_duration' => $newDuration,
        'duration_reduction' => $durationReduction,
        'monthly_payment' => $currentMonthlyPayment,
        'new_outstanding_balance' => $newPrincipalBalance,
        'new_completion_date' => $newDuration > 0 ? 
            Carbon::parse($nextDueDate)->addMonths($newDuration - 1) : now(),
        'savings_message' => "Loan duration reduced by {$durationReduction} months using original interest rate"
    ];
}

/**
 * CORRECTED: Replace your existing rescheduleLoanReduceInstallment method
 */
private function rescheduleLoanReduceInstallment($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal)
{
    Log::info('=== REDUCE INSTALLMENT WITH ORIGINAL INTEREST ===', [
        'new_principal_balance' => $newPrincipalBalance,
        'remaining_months' => $remainingMonths,
        'original_interest_decimal' => $monthlyInterestDecimal
    ]);
    
    // Calculate new monthly payment using PMT formula with ORIGINAL interest rate
    $newMonthlyPayment = $this->calculatePMT(
        $newPrincipalBalance,
        $monthlyInterestDecimal,
        $remainingMonths
    );
    
    $originalPayment = $agreement->monthly_payment;
    $paymentReduction = max(0, $originalPayment - $newMonthlyPayment);
    
    // Generate new schedule with ORIGINAL interest rate
    if ($remainingMonths > 0) {
        $this->generateNewPaymentSchedule(
            $agreement->id,
            $newPrincipalBalance,
            $newMonthlyPayment,
            $remainingMonths,
            $monthlyInterestDecimal,
            $nextDueDate
        );
    }
    
    return [
        'reschedule_type' => 'reduce_installment',
        'original_monthly_payment' => $originalPayment,
        'new_monthly_payment' => $newMonthlyPayment,
        'payment_reduction' => $paymentReduction,
        'remaining_duration' => $remainingMonths,
        'new_outstanding_balance' => $newPrincipalBalance,
        'savings_message' => "Monthly payment reduced by KSh " . number_format($paymentReduction, 2) . " using original interest rate"
    ];
}

/**
 * CORRECTED: Replace your existing generateNewPaymentSchedule method
 */
private function generateNewPaymentSchedule($agreementId, $principalAmount, $monthlyPayment, $duration, $monthlyInterestDecimal, $startDate)
{
    Log::info('=== GENERATING NEW SCHEDULE WITH ORIGINAL INTEREST ===', [
        'principal_amount' => $principalAmount,
        'monthly_payment' => $monthlyPayment,
        'duration' => $duration,
        'original_interest_decimal' => $monthlyInterestDecimal,
        'original_interest_percentage' => round($monthlyInterestDecimal * 100, 6) . '%'
    ]);
    
    // CRITICAL: Validate original interest rate
    if ($monthlyInterestDecimal <= 0) {
        throw new \Exception('CRITICAL: Original interest rate is zero! Cannot generate schedule.');
    }
    
    $remainingPrincipal = $principalAmount;
    
    // Get the next installment number (after existing payments)
    $lastInstallment = PaymentSchedule::where('agreement_id', $agreementId)
        ->max('installment_number') ?? 0;
    
    for ($month = 1; $month <= $duration; $month++) {
        // Calculate interest on REMAINING principal using ORIGINAL rate
        $monthlyInterest = $remainingPrincipal * $monthlyInterestDecimal;
        $monthlyPrincipal = $monthlyPayment - $monthlyInterest;
        
        // VALIDATION: Ensure positive interest with original rate
        if ($monthlyInterest <= 0) {
            throw new \Exception('Invalid interest calculation with original rate - got zero interest');
        }
        
        // Ensure principal payment is positive
        if ($monthlyPrincipal < 0) {
            $monthlyPrincipal = 0;
            $monthlyInterest = $monthlyPayment;
        }
        
        // For the last payment, adjust to pay off exactly
        if ($month == $duration || $monthlyPrincipal >= $remainingPrincipal) {
            $monthlyPrincipal = $remainingPrincipal;
            $actualPayment = $monthlyPrincipal + $monthlyInterest;
            $newRemainingPrincipal = 0;
        } else {
            $actualPayment = $monthlyPayment;
            $newRemainingPrincipal = $remainingPrincipal - $monthlyPrincipal;
        }
        
        // Log first few payments for verification
        if ($month <= 3) {
            Log::info("NEW Payment {$month} Details (Original Interest):", [
                'Starting Balance' => round($remainingPrincipal, 2),
                'Interest Rate' => round($monthlyInterestDecimal * 100, 6) . '%',
                'Interest Amount' => round($monthlyInterest, 2),
                'Principal Amount' => round($monthlyPrincipal, 2),
                'Total Payment' => round($actualPayment, 2),
                'Ending Balance' => round($newRemainingPrincipal, 2)
            ]);
        }
        
        // Create payment schedule entry
        PaymentSchedule::create([
            'agreement_id' => $agreementId,
            'installment_number' => $lastInstallment + $month,
            'due_date' => Carbon::parse($startDate)->addMonths($month - 1),
            'principal_amount' => round($monthlyPrincipal, 2),
            'interest_amount' => round($monthlyInterest, 2), // ORIGINAL INTEREST RATE!
            'total_amount' => round($actualPayment, 2),
            'balance_after' => round($newRemainingPrincipal, 2),
            'status' => 'pending',
            'amount_paid' => 0,
            'date_paid' => null,
            'days_overdue' => 0
        ]);
        
        $remainingPrincipal = $newRemainingPrincipal;
        
        if ($remainingPrincipal <= 0) {
            break;
        }
    }
    
    Log::info('✅ New payment schedule generated successfully with ORIGINAL interest rate');
}


/**
 * NEW: Simulate priority lump sum application (for preview)
 */
private function simulatePriorityLumpSumApplication($agreement, $paymentAmount)
{
    $remainingAmount = $paymentAmount;
    $breakdown = [];
    $totalAppliedToSchedule = 0;
    
    // Priority 1: Simulate overdue payments
    $overduePayments = $agreement->paymentSchedule()
        ->where('status', 'overdue')
        ->orderBy('due_date', 'asc')
        ->get();
    
    foreach ($overduePayments as $overduePayment) {
        if ($remainingAmount <= 0) break;
        
        $currentPaid = $overduePayment->amount_paid ?? 0;
        $amountDue = $overduePayment->total_amount - $currentPaid;
        
        if ($amountDue > 0) {
            $appliedAmount = min($remainingAmount, $amountDue);
            $newAmountPaid = $currentPaid + $appliedAmount;
            $newStatus = ($newAmountPaid >= $overduePayment->total_amount) ? 'paid' : 'overdue';
            
            $breakdown[] = [
                'installment_number' => $overduePayment->installment_number,
                'due_date' => $overduePayment->due_date,
                'amount_applied' => $appliedAmount,
                'status_before' => $overduePayment->status,
                'status_after' => $newStatus,
                'type' => 'overdue'
            ];
            
            $remainingAmount -= $appliedAmount;
            $totalAppliedToSchedule += $appliedAmount;
        }
    }
    
    // Priority 2: Simulate partial payments
    $partialPayments = $agreement->paymentSchedule()
        ->where('status', 'partial')
        ->orderBy('due_date', 'asc')
        ->get();
    
    foreach ($partialPayments as $partialPayment) {
        if ($remainingAmount <= 0) break;
        
        $currentPaid = $partialPayment->amount_paid ?? 0;
        $amountDue = $partialPayment->total_amount - $currentPaid;
        
        if ($amountDue > 0) {
            $appliedAmount = min($remainingAmount, $amountDue);
            $newAmountPaid = $currentPaid + $appliedAmount;
            $newStatus = ($newAmountPaid >= $partialPayment->total_amount) ? 'paid' : 'partial';
            
            $breakdown[] = [
                'installment_number' => $partialPayment->installment_number,
                'due_date' => $partialPayment->due_date,
                'amount_applied' => $appliedAmount,
                'status_before' => $partialPayment->status,
                'status_after' => $newStatus,
                'type' => 'partial'
            ];
            
            $remainingAmount -= $appliedAmount;
            $totalAppliedToSchedule += $appliedAmount;
        }
    }
    
    // Priority 3: Simulate ONE next pending payment
    if ($remainingAmount > 0) {
        $nextPendingPayment = $agreement->paymentSchedule()
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->first();
        
        if ($nextPendingPayment) {
            $currentPaid = $nextPendingPayment->amount_paid ?? 0;
            $amountDue = $nextPendingPayment->total_amount - $currentPaid;
            
            if ($amountDue > 0) {
                $appliedAmount = min($remainingAmount, $amountDue);
                $newAmountPaid = $currentPaid + $appliedAmount;
                $newStatus = ($newAmountPaid >= $nextPendingPayment->total_amount) ? 'paid' : 'partial';
                
                $breakdown[] = [
                    'installment_number' => $nextPendingPayment->installment_number,
                    'due_date' => $nextPendingPayment->due_date,
                    'amount_applied' => $appliedAmount,
                    'status_before' => $nextPendingPayment->status,
                    'status_after' => $newStatus,
                    'type' => 'next_pending'
                ];
                
                $remainingAmount -= $appliedAmount;
                $totalAppliedToSchedule += $appliedAmount;
            }
        }
    }
    

    // Calculate remaining principal after simulation
    $unpaidSchedules = $agreement->paymentSchedule()
        ->whereIn('status', ['pending', 'partial', 'overdue'])
        ->get();
    
    $totalRemainingPrincipal = 0;
    foreach ($unpaidSchedules as $schedule) {
        // Simulate payment application to this schedule
        $simulatedAmountPaid = $schedule->amount_paid;
        foreach ($breakdown as $application) {
            if ($application['installment_number'] == $schedule->installment_number) {
                $simulatedAmountPaid += $application['amount_applied'];
                break;
            }
        }
        
        $paidRatio = $schedule->total_amount > 0 ? 
                    ($simulatedAmountPaid / $schedule->total_amount) : 0;
        $unpaidPrincipal = $schedule->principal_amount * (1 - $paidRatio);
        $totalRemainingPrincipal += $unpaidPrincipal;
    }
    
    $principalReduction = $remainingAmount;
    $finalRemainingPrincipal = max(0, $totalRemainingPrincipal - $principalReduction);
    
    return [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $principalReduction,
        'remaining_principal' => $finalRemainingPrincipal,
        'breakdown' => $breakdown,
        'summary' => [
            'overdue_processed' => $overduePayments->count(),
            'partial_completed' => $partialPayments->count(),
            'next_payment_processed' => isset($nextPendingPayment) ? 1 : 0
        ]
    ];
}


    /**
     * FIXED: Only the reduce installment part of rescheduling
     */
    private function rescheduleLoanReduceInstallmentFixed($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal)
    {
        Log::info('=== FIXED REDUCE INSTALLMENT RESCHEDULING ===', [
            'new_principal_balance' => $newPrincipalBalance,
            'remaining_months' => $remainingMonths,
            'monthly_interest_decimal' => $monthlyInterestDecimal
        ]);
        
        $originalPayment = $agreement->monthly_payment;
        
        // Calculate new monthly payment with safety checks
        try {
            $newMonthlyPayment = $this->calculatePMTSafe(
                $newPrincipalBalance,
                $monthlyInterestDecimal,
                $remainingMonths
            );
            
            // Additional validation
            if ($newMonthlyPayment >= $originalPayment) {
                Log::warning('New payment would be higher than original, adjusting');
                $newMonthlyPayment = $originalPayment * 0.95; // 5% reduction minimum
            }
            
            $paymentReduction = $originalPayment - $newMonthlyPayment;
            
            Log::info('Payment calculation results:', [
                'original_monthly_payment' => $originalPayment,
                'new_monthly_payment' => $newMonthlyPayment,
                'payment_reduction' => $paymentReduction
            ]);
            
            // Generate new schedule with ORIGINAL interest rate
            if ($remainingMonths > 0 && $newPrincipalBalance > 0) {
                $this->generateNewPaymentSchedule(
                    $agreement->id,
                    $newPrincipalBalance,
                    $newMonthlyPayment,
                    $remainingMonths,
                    $monthlyInterestDecimal,
                    $nextDueDate
                );
            }
            
            return [
                'reschedule_type' => 'reduce_installment',
                'original_monthly_payment' => $originalPayment,
                'new_monthly_payment' => round($newMonthlyPayment, 2),
                'payment_reduction' => round($paymentReduction, 2),
                'remaining_duration' => $remainingMonths,
                'new_outstanding_balance' => $newPrincipalBalance,
                'savings_message' => "Monthly payment reduced by KSh " . number_format($paymentReduction, 2) . " using original interest rate"
            ];
            
        } catch (\Exception $e) {
            Log::error('Error in reduce installment calculation:', [
                'error' => $e->getMessage(),
                'principal' => $newPrincipalBalance,
                'months' => $remainingMonths
            ]);
            
            // Fallback to a reasonable reduction
            $fallbackPayment = $originalPayment * 0.8; // 20% reduction
            $paymentReduction = $originalPayment - $fallbackPayment;
            
            return [
                'reschedule_type' => 'reduce_installment',
                'original_monthly_payment' => $originalPayment,
                'new_monthly_payment' => round($fallbackPayment, 2),
                'payment_reduction' => round($paymentReduction, 2),
                'remaining_duration' => $remainingMonths,
                'new_outstanding_balance' => $newPrincipalBalance,
                'savings_message' => "Monthly payment reduced by KSh " . number_format($paymentReduction, 2) . " (fallback calculation)"
            ];
        }
    }
/**
 * HELPER: Calculate reduce duration option
 */
private function calculateReduceDurationOption($principalBalance, $monthlyPayment, $monthlyInterestDecimal, $currentDuration)
{
    if ($principalBalance <= 0) {
        return [
            'new_duration' => 0,
            'duration_reduction' => $currentDuration
        ];
    }
    
    // Use PMT formula to solve for duration: n = -ln(1 - (P*r)/PMT) / ln(1+r)
    if ($monthlyInterestDecimal == 0) {
        $newDuration = ceil($principalBalance / $monthlyPayment);
    } else {
        $factor = ($principalBalance * $monthlyInterestDecimal) / $monthlyPayment;
        if ($factor >= 1) {
            $newDuration = $currentDuration;
        } else {
            $newDuration = ceil(-log(1 - $factor) / log(1 + $monthlyInterestDecimal));
        }
    }
    
    $newDuration = max(1, min($newDuration, $currentDuration));
    $durationReduction = max(0, $currentDuration - $newDuration);
    
    return [
        'new_duration' => $newDuration,
        'duration_reduction' => $durationReduction
    ];
}

/**
 * HELPER: Calculate reduce payment option
 */
private function calculateReducePaymentOption($principalBalance, $duration, $monthlyInterestDecimal)
{
    if ($principalBalance <= 0) {
        return [
            'new_payment' => 0
        ];
    }
    
    // Use PMT formula to calculate new payment
    $newPayment = $this->calculatePMT($principalBalance, $monthlyInterestDecimal, $duration);
    
    return [
        'new_payment' => $newPayment
    ];
}


/**
 * CORRECTED: Apply lump sum to ONLY the next payment + principal reduction
 * NO multiple payments, NO cascading through installments
 */
private function applyLumpSumToNextPaymentOnly($agreementId, $paymentAmount, $paymentDate)
{
    $remainingAmount = $paymentAmount;
    
    Log::info('=== NEXT PAYMENT ONLY LUMP SUM APPLICATION ===', [
        'agreement_id' => $agreementId,
        'payment_amount' => $paymentAmount
    ]);
    
    // Get current loan status
    $paidCount = PaymentSchedule::where('agreement_id', $agreementId)
        ->where('status', 'paid')
        ->count();
    
    // Get ONLY the next unpaid payment (first pending/overdue/partial)
    $nextPayment = PaymentSchedule::where('agreement_id', $agreementId)
        ->whereIn('status', ['overdue', 'partial', 'pending'])
        ->orderByRaw("
            CASE 
                WHEN status = 'overdue' THEN 1 
                WHEN status = 'partial' THEN 2 
                WHEN status = 'pending' THEN 3 
            END
        ")
        ->orderBy('installment_number', 'asc')
        ->first();
    
    $appliedBreakdown = [];
    $totalAppliedToSchedule = 0;
    
    // Apply to ONLY the next payment
    if ($nextPayment && $remainingAmount > 0) {
        $currentPaid = $nextPayment->amount_paid ?? 0;
        $amountDue = $nextPayment->total_amount - $currentPaid;
        
        if ($amountDue > 0) {
            // Apply payment to this installment ONLY
            $appliedAmount = min($remainingAmount, $amountDue);
            $newAmountPaid = $currentPaid + $appliedAmount;
            
            // Determine new status
            if ($newAmountPaid >= $nextPayment->total_amount) {
                $newStatus = 'paid';
            } else {
                $newStatus = 'partial';
            }
            
            $nextPayment->update([
                'amount_paid' => $newAmountPaid,
                'status' => $newStatus,
                'date_paid' => $newStatus === 'paid' ? $paymentDate : $nextPayment->date_paid,
                'days_overdue' => $newStatus === 'paid' ? 0 : $nextPayment->days_overdue
            ]);
            
            $appliedBreakdown[] = [
                'installment_number' => $nextPayment->installment_number,
                'due_date' => $nextPayment->due_date,
                'amount_applied' => $appliedAmount,
                'status_before' => $nextPayment->status,
                'status_after' => $newStatus
            ];
            
            $remainingAmount -= $appliedAmount;
            $totalAppliedToSchedule = $appliedAmount;
            
            Log::info("Applied to ONLY next payment {$nextPayment->installment_number}:", [
                'applied' => $appliedAmount,
                'new_status' => $newStatus,
                'remaining_for_principal' => $remainingAmount
            ]);
        }
    }
    
    // Calculate final paid count
    $finalPaidCount = $paidCount;
    if (isset($appliedBreakdown[0]) && $appliedBreakdown[0]['status_after'] === 'paid') {
        $finalPaidCount++;
    }
    
    Log::info('Next payment only application result:', [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $remainingAmount,
        'paid_installments_before' => $paidCount,
        'paid_installments_after' => $finalPaidCount,
        'next_payment_affected' => $nextPayment ? $nextPayment->installment_number : 'none'
    ]);
    
    return [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $remainingAmount,
        'breakdown' => $appliedBreakdown,
        'paid_installments_before' => $paidCount,
        'paid_installments_after' => $finalPaidCount
    ];
}



/**
 * RESCHEDULING: Perform rescheduling with original interest rate
 */
private function performReschedulingWithOriginalRate($agreement, $newPrincipalBalance, $rescheduleOption, $applicationResult)
{
    Log::info('=== RESCHEDULING WITH ORIGINAL RATE ===', [
        'agreement_id' => $agreement->id,
        'new_principal_balance' => $newPrincipalBalance,
        'reschedule_option' => $rescheduleOption
    ]);
    
    // Clear future unpaid schedules (keep paid/partial ones)
    $this->clearFutureUnpaidSchedules($agreement->id);
    
    // Get remaining months and next due date
    $remainingMonths = $this->getRemainingMonths($agreement);
    $nextDueDate = $this->getNextDueDate($agreement);
    
    // Get the ORIGINAL monthly interest rate
    $monthlyInterestRate = $this->getOriginalMonthlyInterestRate($agreement);
    $monthlyInterestDecimal = $monthlyInterestRate / 100;
    
    Log::info('Using ORIGINAL interest rate for rescheduling:', [
        'monthly_interest_rate' => $monthlyInterestRate,
        'monthly_interest_decimal' => $monthlyInterestDecimal
    ]);
    
    if ($monthlyInterestDecimal <= 0) {
        throw new \Exception('Invalid original monthly interest rate: ' . $monthlyInterestRate . '%');
    }
    
    if ($rescheduleOption === 'reduce_duration') {
        return $this->reduceDurationWithOriginalRate($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal);
    } else {
        return $this->reduceInstallmentWithOriginalRate($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal);
    }
}

/**
 * GET ORIGINAL INTEREST RATE: Extract from agreement data
 */

/**
 * REDUCE DURATION: Keep same payment, reduce months
 */
private function reduceDurationWithOriginalRate($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal)
{
    $currentMonthlyPayment = $agreement->monthly_payment;
    
    // Calculate new duration: n = -ln(1 - (P*r)/PMT) / ln(1+r)
    if ($newPrincipalBalance <= 0) {
        $newDuration = 0;
    } else {
        $factor = ($newPrincipalBalance * $monthlyInterestDecimal) / $currentMonthlyPayment;
        if ($factor >= 1) {
            $newDuration = $remainingMonths; // Payment too small
        } else {
            $newDuration = ceil(-log(1 - $factor) / log(1 + $monthlyInterestDecimal));
        }
    }
    
    $newDuration = max(1, $newDuration);
    $durationReduction = max(0, $remainingMonths - $newDuration);
    
    // Generate new schedule
    if ($newDuration > 0) {
        $this->generateScheduleWithOriginalRate(
            $agreement->id,
            $newPrincipalBalance,
            $currentMonthlyPayment,
            $newDuration,
            $monthlyInterestDecimal,
            $nextDueDate
        );
    }
    
    return [
        'reschedule_type' => 'reduce_duration',
        'original_remaining_months' => $remainingMonths,
        'new_duration' => $newDuration,
        'duration_reduction' => $durationReduction,
        'monthly_payment' => $currentMonthlyPayment,
        'new_outstanding_balance' => $newPrincipalBalance,
        'savings_message' => "Duration reduced by {$durationReduction} months with original interest rate"
    ];
}

/**
 * REDUCE INSTALLMENT: Keep same duration, reduce payment
 */
private function reduceInstallmentWithOriginalRate($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal)
{
    // Calculate new payment: PMT = P * [r(1+r)^n] / [(1+r)^n - 1]
    $newMonthlyPayment = $this->calculatePMT(
        $newPrincipalBalance,
        $monthlyInterestDecimal,
        $remainingMonths
    );
    
    $originalPayment = $agreement->monthly_payment;
    $paymentReduction = max(0, $originalPayment - $newMonthlyPayment);
    
    // Generate new schedule
    if ($remainingMonths > 0) {
        $this->generateScheduleWithOriginalRate(
            $agreement->id,
            $newPrincipalBalance,
            $newMonthlyPayment,
            $remainingMonths,
            $monthlyInterestDecimal,
            $nextDueDate
        );
    }
    
    return [
        'reschedule_type' => 'reduce_installment',
        'original_monthly_payment' => $originalPayment,
        'new_monthly_payment' => $newMonthlyPayment,
        'payment_reduction' => $paymentReduction,
        'remaining_duration' => $remainingMonths,
        'new_outstanding_balance' => $newPrincipalBalance,
        'savings_message' => "Payment reduced by KSh " . number_format($paymentReduction, 2) . " with original interest rate"
    ];
}

/**
 * GENERATE SCHEDULE: Create new schedule with ORIGINAL interest rate
 */
private function generateScheduleWithOriginalRate($agreementId, $principalAmount, $monthlyPayment, $duration, $monthlyInterestDecimal, $startDate)
{
    Log::info('=== GENERATING SCHEDULE WITH ORIGINAL RATE ===', [
        'principal' => $principalAmount,
        'payment' => $monthlyPayment,
        'duration' => $duration,
        'rate' => round($monthlyInterestDecimal * 100, 6) . '%'
    ]);
    
    if ($monthlyInterestDecimal <= 0) {
        throw new \Exception('Invalid interest rate for schedule generation');
    }
    
    $remainingPrincipal = $principalAmount;
    $lastInstallment = PaymentSchedule::where('agreement_id', $agreementId)->max('installment_number') ?? 0;
    
    for ($month = 1; $month <= $duration; $month++) {
        // Calculate with ORIGINAL interest rate
        $monthlyInterest = $remainingPrincipal * $monthlyInterestDecimal;
        $monthlyPrincipal = $monthlyPayment - $monthlyInterest;
        
        // Validate positive interest
        if ($monthlyInterest <= 0) {
            throw new \Exception("Zero interest calculated for month {$month}");
        }
        
        // Adjust final payment
        if ($month == $duration || $monthlyPrincipal >= $remainingPrincipal) {
            $monthlyPrincipal = $remainingPrincipal;
            $actualPayment = $monthlyPrincipal + $monthlyInterest;
            $newRemainingPrincipal = 0;
        } else {
            $actualPayment = $monthlyPayment;
            $newRemainingPrincipal = $remainingPrincipal - $monthlyPrincipal;
        }
        
        // Create schedule entry
        PaymentSchedule::create([
            'agreement_id' => $agreementId,
            'installment_number' => $lastInstallment + $month,
            'due_date' => Carbon::parse($startDate)->addMonths($month - 1),
            'principal_amount' => round($monthlyPrincipal, 2),
            'interest_amount' => round($monthlyInterest, 2), // ORIGINAL RATE INTEREST!
            'total_amount' => round($actualPayment, 2),
            'balance_after' => round($newRemainingPrincipal, 2),
            'status' => 'pending',
            'amount_paid' => 0,
            'date_paid' => null,
            'days_overdue' => 0
        ]);
        
        $remainingPrincipal = $newRemainingPrincipal;
        
        if ($remainingPrincipal <= 0) break;
    }
    
    Log::info('✅ Schedule generated with ORIGINAL interest rate');
}
/**
 * CORRECTED: Apply lump sum correctly - ONLY first due payment + principal reduction
 */
private function applyLumpSumCorrectly($agreementId, $paymentAmount, $paymentDate)
{
    $remainingAmount = $paymentAmount;
    
    Log::info('=== CORRECT LUMP SUM APPLICATION ===', [
        'agreement_id' => $agreementId,
        'payment_amount' => $paymentAmount
    ]);
    
    // Get loan status
    $paidCount = PaymentSchedule::where('agreement_id', $agreementId)
        ->where('status', 'paid')
        ->count();
    
    // Step 1: Get ONLY the first due payment (overdue, partial, or current due)
    $firstDuePayment = PaymentSchedule::where('agreement_id', $agreementId)
        ->whereIn('status', ['overdue', 'partial', 'pending'])
        ->orderByRaw("
            CASE 
                WHEN status = 'overdue' THEN 1 
                WHEN status = 'partial' THEN 2 
                WHEN status = 'pending' THEN 3 
            END
        ")
        ->orderBy('due_date', 'asc')
        ->first();
    
    $appliedBreakdown = [];
    $totalAppliedToSchedule = 0;
    
    // Apply to ONLY the first due payment
    if ($firstDuePayment && $remainingAmount > 0) {
        $currentPaid = $firstDuePayment->amount_paid ?? 0;
        $amountDue = $firstDuePayment->total_amount - $currentPaid;
        
        if ($amountDue > 0) {
            $appliedAmount = min($remainingAmount, $amountDue);
            $newAmountPaid = $currentPaid + $appliedAmount;
            $newStatus = ($newAmountPaid >= $firstDuePayment->total_amount) ? 'paid' : 'partial';
            
            $firstDuePayment->update([
                'amount_paid' => $newAmountPaid,
                'status' => $newStatus,
                'date_paid' => $newStatus === 'paid' ? $paymentDate : $firstDuePayment->date_paid,
                'days_overdue' => $newStatus === 'paid' ? 0 : $firstDuePayment->days_overdue
            ]);
            
            $appliedBreakdown[] = [
                'installment_number' => $firstDuePayment->installment_number,
                'due_date' => $firstDuePayment->due_date,
                'amount_applied' => $appliedAmount,
                'status_before' => $firstDuePayment->status,
                'status_after' => $newStatus
            ];
            
            $remainingAmount -= $appliedAmount;
            $totalAppliedToSchedule = $appliedAmount;
            
            Log::info("Applied to first due payment {$firstDuePayment->installment_number}:", [
                'applied' => $appliedAmount,
                'new_status' => $newStatus,
                'remaining_lump_sum' => $remainingAmount
            ]);
        }
    }
    
    Log::info('Correct lump sum application result:', [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $remainingAmount,
        'paid_installments_before' => $paidCount,
        'paid_installments_after' => $paidCount + ($firstDuePayment && $appliedBreakdown[0]['status_after'] === 'paid' ? 1 : 0)
    ]);
    
    return [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $remainingAmount,
        'breakdown' => $appliedBreakdown,
        'paid_installments_before' => $paidCount,
        'paid_installments_after' => $paidCount + ($firstDuePayment && isset($appliedBreakdown[0]) && $appliedBreakdown[0]['status_after'] === 'paid' ? 1 : 0)
    ];
}

/**
 * CORRECTED: Calculate new principal balance after lump sum
 */
private function calculateNewPrincipalAfterLumpSum($agreement, $applicationResult)
{
    Log::info('=== CALCULATING NEW PRINCIPAL BALANCE ===', [
        'agreement_id' => $agreement->id
    ]);
    
    // Get ALL unpaid/partial schedules
    $unpaidSchedules = $agreement->paymentSchedule()
        ->whereIn('status', ['pending', 'partial', 'overdue'])
        ->orderBy('installment_number')
        ->get();
    
    $totalRemainingPrincipal = 0;
    
    foreach ($unpaidSchedules as $schedule) {
        // Calculate how much principal is unpaid in this schedule
        $paidRatio = $schedule->total_amount > 0 ? 
                    ($schedule->amount_paid / $schedule->total_amount) : 0;
        $unpaidPrincipal = $schedule->principal_amount * (1 - $paidRatio);
        $totalRemainingPrincipal += $unpaidPrincipal;
        
        Log::info("Schedule {$schedule->installment_number} principal:", [
            'status' => $schedule->status,
            'total_principal' => $schedule->principal_amount,
            'paid_ratio' => round($paidRatio, 4),
            'unpaid_principal' => round($unpaidPrincipal, 2)
        ]);
    }
    
    // Apply principal reduction from lump sum
    $principalReduction = $applicationResult['remaining_for_principal_reduction'];
    $newPrincipalBalance = max(0, $totalRemainingPrincipal - $principalReduction);
    
    Log::info('Final principal calculation:', [
        'total_remaining_principal' => $totalRemainingPrincipal,
        'principal_reduction_from_lump_sum' => $principalReduction,
        'new_principal_balance' => $newPrincipalBalance
    ]);
    
    return $newPrincipalBalance;
}

/**
 * CORRECTED: Perform proper rescheduling with interest calculations
 */
private function performProperRescheduling($agreement, $newPrincipalBalance, $rescheduleOption, $applicationResult)
{
    Log::info('=== PROPER RESCHEDULING START ===', [
        'agreement_id' => $agreement->id,
        'new_principal_balance' => $newPrincipalBalance,
        'reschedule_option' => $rescheduleOption
    ]);
    
    // Clear future unpaid schedules (keep paid/partial ones)
    $this->clearFutureUnpaidSchedules($agreement->id);
    
    // Get remaining months and next due date
    $remainingMonths = $this->getRemainingMonths($agreement);
    $nextDueDate = $this->getNextDueDate($agreement);
    
    // Use the stored monthly interest rate
    $monthlyInterestRate = $agreement->monthly_interest_rate; // Should be 4.29 or 4.5
    $monthlyInterestDecimal = $monthlyInterestRate / 100;
    
    Log::info('Interest rate for rescheduling:', [
        'monthly_interest_rate' => $monthlyInterestRate,
        'monthly_interest_decimal' => $monthlyInterestDecimal
    ]);
    
    if ($monthlyInterestDecimal <= 0) {
        throw new \Exception('Invalid monthly interest rate: ' . $monthlyInterestRate . '%');
    }
    
    if ($rescheduleOption === 'reduce_duration') {
        return $this->rescheduleLoanReduceDurationCorrect($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal);
    } else {
        return $this->rescheduleLoanReduceInstallmentCorrect($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal);
    }
}

/**
 * CORRECTED: Duration reduction with PROPER interest calculations
 */
private function rescheduleLoanReduceDurationCorrect($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal)
{
    $currentMonthlyPayment = $agreement->monthly_payment;
    
    Log::info('=== REDUCE DURATION RESCHEDULING ===', [
        'new_principal_balance' => $newPrincipalBalance,
        'current_monthly_payment' => $currentMonthlyPayment,
        'monthly_interest_decimal' => $monthlyInterestDecimal
    ]);
    
    // Calculate new duration using PMT formula
    if ($newPrincipalBalance <= 0) {
        $newDuration = 0;
    } else {
        // Solve for n in PMT formula: n = -ln(1 - (P*r)/PMT) / ln(1+r)
        $factor = ($newPrincipalBalance * $monthlyInterestDecimal) / $currentMonthlyPayment;
        if ($factor >= 1) {
            // Payment is too small, use maximum duration
            $newDuration = $remainingMonths;
        } else {
            $newDuration = ceil(-log(1 - $factor) / log(1 + $monthlyInterestDecimal));
        }
    }
    
    $newDuration = max(1, $newDuration);
    $durationReduction = max(0, $remainingMonths - $newDuration);
    
    Log::info('Duration calculation result:', [
        'original_remaining_months' => $remainingMonths,
        'new_duration' => $newDuration,
        'duration_reduction' => $durationReduction
    ]);
    
    // Generate new schedule with PROPER INTEREST
    if ($newDuration > 0) {
        $this->generateNewPaymentScheduleWithCorrectInterest(
            $agreement->id,
            $newPrincipalBalance,
            $currentMonthlyPayment,
            $newDuration,
            $monthlyInterestDecimal,
            $nextDueDate
        );
    }
    
    return [
        'reschedule_type' => 'reduce_duration',
        'original_remaining_months' => $remainingMonths,
        'new_duration' => $newDuration,
        'duration_reduction' => $durationReduction,
        'monthly_payment' => $currentMonthlyPayment,
        'new_outstanding_balance' => $newPrincipalBalance,
        'new_completion_date' => $newDuration > 0 ? 
            Carbon::parse($nextDueDate)->addMonths($newDuration - 1) : now(),
        'savings_message' => "Loan duration reduced by {$durationReduction} months"
    ];
}

/**
 * CORRECTED: Payment reduction with PROPER interest calculations
 */
private function rescheduleLoanReduceInstallmentCorrect($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal)
{
    Log::info('=== REDUCE INSTALLMENT RESCHEDULING ===', [
        'new_principal_balance' => $newPrincipalBalance,
        'remaining_months' => $remainingMonths,
        'monthly_interest_decimal' => $monthlyInterestDecimal
    ]);
    
    // Calculate new monthly payment using PMT formula
    $newMonthlyPayment = $this->calculatePMT(
        $newPrincipalBalance,
        $monthlyInterestDecimal,
        $remainingMonths
    );
    
    $originalPayment = $agreement->monthly_payment;
    $paymentReduction = max(0, $originalPayment - $newMonthlyPayment);
    
    Log::info('Payment calculation result:', [
        'original_monthly_payment' => $originalPayment,
        'new_monthly_payment' => $newMonthlyPayment,
        'payment_reduction' => $paymentReduction
    ]);
    
    // Generate new schedule with PROPER INTEREST
    if ($remainingMonths > 0) {
        $this->generateNewPaymentScheduleWithCorrectInterest(
            $agreement->id,
            $newPrincipalBalance,
            $newMonthlyPayment,
            $remainingMonths,
            $monthlyInterestDecimal,
            $nextDueDate
        );
    }
    
    return [
        'reschedule_type' => 'reduce_installment',
        'original_monthly_payment' => $originalPayment,
        'new_monthly_payment' => $newMonthlyPayment,
        'payment_reduction' => $paymentReduction,
        'remaining_duration' => $remainingMonths,
        'new_outstanding_balance' => $newPrincipalBalance,
        'savings_message' => "Monthly payment reduced by KSh " . number_format($paymentReduction, 2)
    ];
}

/**
 * CORRECTED: Generate payment schedule with PROPER INTEREST calculations
 */
private function generateNewPaymentScheduleWithCorrectInterest($agreementId, $principalAmount, $monthlyPayment, $duration, $monthlyInterestDecimal, $startDate)
{
    Log::info('=== GENERATING SCHEDULE WITH CORRECT INTEREST ===', [
        'principal_amount' => $principalAmount,
        'monthly_payment' => $monthlyPayment,
        'duration' => $duration,
        'monthly_interest_decimal' => $monthlyInterestDecimal,
        'monthly_interest_rate' => round($monthlyInterestDecimal * 100, 6) . '%'
    ]);
    
    // CRITICAL: Validate interest rate
    if ($monthlyInterestDecimal <= 0) {
        throw new \Exception('CRITICAL: Monthly interest rate is zero! Cannot generate schedule.');
    }
    
    $remainingPrincipal = $principalAmount;
    
    // Get the next installment number
    $lastInstallment = PaymentSchedule::where('agreement_id', $agreementId)
        ->max('installment_number') ?? 0;
    
    for ($month = 1; $month <= $duration; $month++) {
        // Calculate interest on REMAINING principal balance
        $monthlyInterest = $remainingPrincipal * $monthlyInterestDecimal;
        $monthlyPrincipal = $monthlyPayment - $monthlyInterest;
        
        // VALIDATION: Ensure positive interest
        if ($monthlyInterest <= 0) {
            Log::error('CRITICAL: Zero interest calculated!', [
                'remaining_principal' => $remainingPrincipal,
                'monthly_interest_decimal' => $monthlyInterestDecimal,
                'month' => $month
            ]);
            throw new \Exception('Invalid interest calculation - got zero interest');
        }
        
        // Ensure principal payment is positive
        if ($monthlyPrincipal < 0) {
            $monthlyPrincipal = 0;
            $monthlyInterest = $monthlyPayment;
        }
        
        // For the last payment, adjust to pay off exactly
        if ($month == $duration || $monthlyPrincipal >= $remainingPrincipal) {
            $monthlyPrincipal = $remainingPrincipal;
            $actualPayment = $monthlyPrincipal + $monthlyInterest;
            $newRemainingPrincipal = 0;
        } else {
            $actualPayment = $monthlyPayment;
            $newRemainingPrincipal = $remainingPrincipal - $monthlyPrincipal;
        }
        
        // Log first few payments for verification
        if ($month <= 3) {
            Log::info("CORRECTED Payment {$month} Details:", [
                'Starting Balance' => round($remainingPrincipal, 2),
                'Interest Rate' => round($monthlyInterestDecimal * 100, 6) . '%',
                'Interest Amount' => round($monthlyInterest, 2),
                'Principal Amount' => round($monthlyPrincipal, 2),
                'Total Payment' => round($actualPayment, 2),
                'Ending Balance' => round($newRemainingPrincipal, 2)
            ]);
        }
        
        PaymentSchedule::create([
            'agreement_id' => $agreementId,
            'installment_number' => $lastInstallment + $month,
            'due_date' => Carbon::parse($startDate)->addMonths($month - 1),
            'principal_amount' => round($monthlyPrincipal, 2),
            'interest_amount' => round($monthlyInterest, 2), // PROPER INTEREST!
            'total_amount' => round($actualPayment, 2),
            'balance_after' => round($newRemainingPrincipal, 2),
            'status' => 'pending',
            'amount_paid' => 0,
            'date_paid' => null,
            'days_overdue' => 0
        ]);
        
        $remainingPrincipal = $newRemainingPrincipal;
        
        if ($remainingPrincipal <= 0) {
            break;
        }
    }
    
    Log::info('✅ Payment schedule generated with CORRECT interest calculations');
}

/**
 * CORRECTED: Clear only future unpaid schedules, keep paid/partial ones
 */
private function clearFutureUnpaidSchedules($agreementId)
{
    // Get the highest paid/partial installment number
    $lastProcessedPayment = PaymentSchedule::where('agreement_id', $agreementId)
        ->whereIn('status', ['paid', 'partial'])
        ->max('installment_number');
    
    if ($lastProcessedPayment) {
        // Delete all pending schedules after the last processed payment
        $deletedCount = PaymentSchedule::where('agreement_id', $agreementId)
            ->where('installment_number', '>', $lastProcessedPayment)
            ->where('status', 'pending')
            ->delete();
        
        Log::info('Cleared future unpaid schedules:', [
            'after_installment' => $lastProcessedPayment,
            'deleted_count' => $deletedCount
        ]);
    } else {
        // No payments made yet, clear all pending except first one if it's partial
        $firstPartial = PaymentSchedule::where('agreement_id', $agreementId)
            ->where('status', 'partial')
            ->min('installment_number');
            
        if ($firstPartial) {
            // Keep the first partial payment, delete pending ones after it
            $deletedCount = PaymentSchedule::where('agreement_id', $agreementId)
                ->where('installment_number', '>', $firstPartial)
                ->where('status', 'pending')
                ->delete();
        } else {
            // Delete all pending payments except the first one
            $deletedCount = PaymentSchedule::where('agreement_id', $agreementId)
                ->where('installment_number', '>', 1)
                ->where('status', 'pending')
                ->delete();
        }
        
        Log::info('Cleared future schedules (no fully paid payments):', [
            'deleted_count' => $deletedCount
        ]);
    }
}



/**
 * CORRECTED: Simulate lump sum application with proper logic
 * Pay first due payment, then apply remainder to principal reduction
 */
private function simulateCorrectLumpSumApplication($agreement, $paymentAmount)
{
    $remainingAmount = $paymentAmount;
    
    Log::info('=== CORRECT LUMP SUM SIMULATION ===', [
        'agreement_id' => $agreement->id,
        'payment_amount' => $paymentAmount
    ]);
    
    // Step 1: Get the FIRST due payment only (overdue, partial, or current due)
    $firstDuePayment = $agreement->paymentSchedule()
        ->whereIn('status', ['overdue', 'partial', 'pending'])
        ->orderByRaw("
            CASE 
                WHEN status = 'overdue' THEN 1 
                WHEN status = 'partial' THEN 2 
                WHEN status = 'pending' THEN 3 
            END
        ")
        ->orderBy('due_date', 'asc')
        ->first();
    
    $breakdown = [];
    $totalAppliedToSchedule = 0;
    
    // Apply to ONLY the first due payment
    if ($firstDuePayment && $remainingAmount > 0) {
        $currentPaid = $firstDuePayment->amount_paid ?? 0;
        $amountDue = $firstDuePayment->total_amount - $currentPaid;
        
        if ($amountDue > 0) {
            $appliedAmount = min($remainingAmount, $amountDue);
            $newAmountPaid = $currentPaid + $appliedAmount;
            $newStatus = ($newAmountPaid >= $firstDuePayment->total_amount) ? 'paid' : 'partial';
            
            $breakdown[] = [
                'installment_number' => $firstDuePayment->installment_number,
                'due_date' => $firstDuePayment->due_date,
                'amount_applied' => $appliedAmount,
                'status_before' => $firstDuePayment->status,
                'status_after' => $newStatus
            ];
            
            $remainingAmount -= $appliedAmount;
            $totalAppliedToSchedule = $appliedAmount;
            
            Log::info('Applied to first due payment:', [
                'installment' => $firstDuePayment->installment_number,
                'applied' => $appliedAmount,
                'new_status' => $newStatus,
                'remaining' => $remainingAmount
            ]);
        }
    }
    
    // Step 2: Calculate remaining principal after first payment + principal reduction
    $unpaidSchedules = $agreement->paymentSchedule()
        ->whereIn('status', ['pending', 'partial', 'overdue'])
        ->get();
    
    $totalRemainingPrincipal = 0;
    foreach ($unpaidSchedules as $schedule) {
        // Simulate payment application to this schedule
        $simulatedAmountPaid = $schedule->amount_paid;
        foreach ($breakdown as $application) {
            if ($application['installment_number'] == $schedule->installment_number) {
                $simulatedAmountPaid += $application['amount_applied'];
                break;
            }
        }
        
        $paidRatio = $schedule->total_amount > 0 ? 
                    ($simulatedAmountPaid / $schedule->total_amount) : 0;
        $unpaidPrincipal = $schedule->principal_amount * (1 - $paidRatio);
        $totalRemainingPrincipal += $unpaidPrincipal;
    }
    
    // Apply remaining amount to principal reduction
    $principalReduction = $remainingAmount;
    $finalRemainingPrincipal = max(0, $totalRemainingPrincipal - $principalReduction);
    
    Log::info('Simulation result:', [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'principal_reduction' => $principalReduction,
        'remaining_principal' => $finalRemainingPrincipal
    ]);
    
    return [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $principalReduction,
        'remaining_principal' => $finalRemainingPrincipal,
        'breakdown' => $breakdown
    ];
}


/**
 * Helper method to get remaining months
 */
private function getRemainingMonths($agreement)
{
    // For reduce payment, we need to calculate based on original duration minus FULLY paid installments
    $fullyPaidCount = PaymentSchedule::where('agreement_id', $agreement->id)
        ->where('status', 'paid')
        ->count();
    
    $originalDuration = $agreement->duration_months;
    $remainingMonths = $originalDuration - $fullyPaidCount;
    
    Log::info('Remaining months calculation (FIXED):', [
        'agreement_id' => $agreement->id,
        'original_duration' => $originalDuration,
        'fully_paid_count' => $fullyPaidCount,
        'remaining_months' => $remainingMonths
    ]);
    
    // Ensure we have at least 1 month
    return max(1, $remainingMonths);
}

/**
 * Helper method to get next due date
 */
private function getNextDueDate($agreement)
{
    $nextSchedule = PaymentSchedule::where('agreement_id', $agreement->id)
        ->whereIn('status', ['pending', 'partial', 'overdue'])
        ->orderBy('due_date', 'asc')
        ->first();
    
    return $nextSchedule ? $nextSchedule->due_date : now()->addMonth();
}

/**
 * Helper method to calculate outstanding balance from schedule
 */
private function calculateCurrentOutstandingFromSchedule($agreement)
{
    Log::info('=== CALCULATING CURRENT OUTSTANDING BALANCE ===', [
        'agreement_id' => $agreement->id
    ]);
    
    // Check if payment schedule exists and has data
    if (!$agreement->paymentSchedule || $agreement->paymentSchedule->isEmpty()) {
        Log::info('No payment schedule found, using agreement outstanding balance', [
            'outstanding_balance' => $agreement->outstanding_balance
        ]);
        return $agreement->outstanding_balance;
    }
    
    // Calculate from payment schedule
    $totalScheduled = $agreement->paymentSchedule->sum('total_amount');
    $totalPaid = $agreement->paymentSchedule->sum('amount_paid');
    $calculatedOutstanding = $totalScheduled - $totalPaid;
    
    Log::info('Outstanding balance calculation:', [
        'total_scheduled' => $totalScheduled,
        'total_paid' => $totalPaid,
        'calculated_outstanding' => $calculatedOutstanding,
        'agreement_outstanding' => $agreement->outstanding_balance
    ]);
    
    // Use calculated outstanding if payment schedule exists, otherwise use agreement balance
    $finalOutstanding = $totalScheduled > 0 ? $calculatedOutstanding : $agreement->outstanding_balance;
    
    // Ensure non-negative balance
    $finalOutstanding = max(0, $finalOutstanding);
    
    Log::info('Final outstanding balance:', ['amount' => $finalOutstanding]);
    
    return $finalOutstanding;
}

/**
 * Helper method to capture original terms
 */
private function captureOriginalTerms($agreement)
{
    return [
        'duration_months' => $this->getRemainingMonths($agreement),
        'monthly_payment' => $agreement->monthly_payment,
        'completion_date' => $agreement->expected_completion_date,
        'outstanding_balance' => $this->calculateCurrentOutstandingFromSchedule($agreement)
    ];
}

/**
 * Helper method to create payment record
 */
private function createLumpSumPaymentRecord($agreement, $request, $currentOutstanding)
{
    $paymentNumber = DB::table('hire_purchase_payments')
        ->where('agreement_id', $agreement->id)
        ->max('payment_number') + 1;

    $balanceAfter = $currentOutstanding - $request->payment_amount;

    $paymentData = [
        'agreement_id' => $agreement->id,
        'amount' => $request->payment_amount,
        'payment_date' => $request->payment_date,
        'payment_method' => $request->payment_method,
        'reference_number' => $request->payment_reference,
        'notes' => ($request->payment_notes ?? '') . ' (Lump Sum Payment with Rescheduling)',
        'payment_type' => 'regular',
        'penalty_amount' => 0,
        'payment_number' => $paymentNumber,
        'recorded_by' => auth()->id() ?? 1,
        'recorded_at' => now(),
        'is_verified' => false,
        'verified_by' => null,
        'verified_at' => null,
        'balance_before' => $currentOutstanding,
        'balance_after' => $balanceAfter,
        'created_at' => now(),
        'updated_at' => now()
    ];

    $paymentId = DB::table('hire_purchase_payments')->insertGetId($paymentData);

    return [
        'payment_id' => $paymentId,
        'payment_number' => $paymentNumber,
        'balance_before' => $currentOutstanding,
        'balance_after' => $balanceAfter
    ];
}

/**
 * FIXED: Helper method to update agreement after rescheduling - preserves correct outstanding balance
 */
private function updateAgreementAfterRescheduling($agreement, $reschedulingResult, $paymentAmount)
{
    $newAmountPaid = $agreement->amount_paid + $paymentAmount;
    
    // 🔥 FIX: Use the correct outstanding balance from rescheduling result
    // NOT zero, but the actual remaining principal balance
    $newOutstanding = $reschedulingResult['new_outstanding_balance'];
    
    Log::info('Updating agreement after rescheduling:', [
        'agreement_id' => $agreement->id,
        'payment_amount' => $paymentAmount,
        'old_amount_paid' => $agreement->amount_paid,
        'new_amount_paid' => $newAmountPaid,
        'old_outstanding' => $agreement->outstanding_balance,
        'new_outstanding_from_rescheduling' => $newOutstanding,
        'reschedule_type' => $reschedulingResult['reschedule_type']
    ]);
    
    $updateData = [
        'amount_paid' => $newAmountPaid,
        'outstanding_balance' => $newOutstanding, // 🔥 This should NOT be 0 for reduce_installment
        'last_payment_date' => now(),
        'updated_at' => now()
    ];
    
    // Update monthly payment if it changed (for reduce_installment option)
    if ($reschedulingResult['reschedule_type'] === 'reduce_installment') {
        $updateData['monthly_payment'] = $reschedulingResult['new_monthly_payment'];
        
        Log::info('Updating monthly payment for reduce_installment:', [
            'old_monthly_payment' => $agreement->monthly_payment,
            'new_monthly_payment' => $reschedulingResult['new_monthly_payment']
        ]);
    }
    
    // Update completion date if it changed
    if (isset($reschedulingResult['new_completion_date'])) {
        $updateData['expected_completion_date'] = $reschedulingResult['new_completion_date'];
    }
    
    // 🔥 CRITICAL: Only mark as completed if outstanding is actually 0
    if ($newOutstanding <= 0) {
        $updateData['status'] = 'completed';
        Log::info('Marking loan as completed - outstanding balance is 0');
    } else {
        Log::info('Loan NOT completed - outstanding balance is: ' . $newOutstanding);
    }
    
    Log::info('Final update data:', $updateData);
    
    DB::table('hire_purchase_agreements')
        ->where('id', $agreement->id)
        ->update($updateData);
        
    Log::info('✅ Agreement updated successfully');
}

/**
 * Helper method to create detailed rescheduling history
 */
private function createDetailedReschedulingHistory($agreement, $paymentId, $request, $originalTerms, $reschedulingResult, $applicationResult)
{
    // Calculate comprehensive savings
    $originalTotalPayments = $originalTerms['monthly_payment'] * $originalTerms['duration_months'];
    $newTotalPayments = $reschedulingResult['reschedule_type'] === 'reduce_duration' 
        ? $reschedulingResult['monthly_payment'] * $reschedulingResult['new_duration']
        : $reschedulingResult['new_monthly_payment'] * $reschedulingResult['remaining_duration'];
    
    $interestSavings = max(0, $originalTotalPayments - $newTotalPayments - $request->payment_amount);

    // Get the completion date properly
    $newCompletionDate = null;
    if (isset($reschedulingResult['new_completion_date'])) {
        $newCompletionDate = Carbon::parse($reschedulingResult['new_completion_date'])->format('Y-m-d');
    } else {
        // Calculate new completion date
        $duration = $reschedulingResult['reschedule_type'] === 'reduce_duration' 
            ? $reschedulingResult['new_duration'] 
            : $reschedulingResult['remaining_duration'];
        $newCompletionDate = now()->addMonths($duration)->format('Y-m-d');
    }

    return DB::table('loan_rescheduling_history')->insertGetId([
        'agreement_id' => $agreement->id,
        'payment_id' => $paymentId,
        'reschedule_type' => $request->reschedule_option,
        'lump_sum_amount' => $request->payment_amount,
        'outstanding_before' => $originalTerms['outstanding_balance'],
        'outstanding_after' => $reschedulingResult['new_outstanding_balance'],
        
        // Previous terms
        'previous_duration_months' => $originalTerms['duration_months'],
        'previous_monthly_payment' => $originalTerms['monthly_payment'],
        'previous_completion_date' => $originalTerms['completion_date'] ? 
            Carbon::parse($originalTerms['completion_date'])->format('Y-m-d') : null,
        
        // New terms
        'new_duration_months' => $reschedulingResult['reschedule_type'] === 'reduce_duration' 
            ? $reschedulingResult['new_duration'] 
            : $reschedulingResult['remaining_duration'],
        'new_monthly_payment' => $reschedulingResult['reschedule_type'] === 'reduce_duration' 
            ? $reschedulingResult['monthly_payment'] 
            : $reschedulingResult['new_monthly_payment'],
        'new_completion_date' => $newCompletionDate,
        
        // Changes and savings
        'duration_change_months' => $reschedulingResult['reschedule_type'] === 'reduce_duration' 
            ? $reschedulingResult['duration_reduction'] 
            : null,
        'payment_change_amount' => $reschedulingResult['reschedule_type'] === 'reduce_installment' 
            ? $reschedulingResult['payment_reduction'] 
            : null,
        'total_interest_savings' => $interestSavings,
        
        'rescheduling_date' => now()->format('Y-m-d'),
        'processed_by' => auth()->id() ?? 1,
        'notes' => $this->generateReschedulingNotes($request, $applicationResult, $reschedulingResult),
        'status' => 'active',
        
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

/**
 * Helper method to generate rescheduling notes
 */
private function generateReschedulingNotes($request, $applicationResult, $reschedulingResult)
{
    $notes = "Lump sum payment of KSh " . number_format($request->payment_amount, 2) . " processed. ";
    $notes .= "Applied KSh " . number_format($applicationResult['total_applied_to_schedule'], 2) . " to scheduled payments. ";
    
    if ($applicationResult['remaining_for_principal_reduction'] > 0) {
        $notes .= "Applied KSh " . number_format($applicationResult['remaining_for_principal_reduction'], 2) . " to principal reduction. ";
    }
    
    $notes .= "Reschedule option: " . $request->reschedule_option . ". ";
    $notes .= $reschedulingResult['savings_message'];
    
    return $notes;
}

/**
 * CORRECTED: Apply lump sum with proper logic - pay first due only, rest to principal
 */
private function applyLumpSumToScheduleCorrect($agreementId, $paymentAmount, $paymentDate)
{
    $remainingAmount = $paymentAmount;
    $today = Carbon::parse($paymentDate)->toDateString();
    
    Log::info('=== CORRECT LUMP SUM APPLICATION ===', [
        'agreement_id' => $agreementId,
        'payment_amount' => $paymentAmount,
        'payment_date' => $paymentDate
    ]);
    
    // Get loan status
    $paidCount = PaymentSchedule::where('agreement_id', $agreementId)
        ->where('status', 'paid')
        ->count();
    
    // Step 1: Get ONLY the first due payment
    $firstDuePayment = PaymentSchedule::where('agreement_id', $agreementId)
        ->whereIn('status', ['overdue', 'partial', 'pending'])
        ->orderByRaw("
            CASE 
                WHEN status = 'overdue' THEN 1 
                WHEN status = 'partial' THEN 2 
                WHEN status = 'pending' THEN 3 
            END
        ")
        ->orderBy('due_date', 'asc')
        ->first();
    
    $appliedBreakdown = [];
    $totalAppliedToSchedule = 0;
    
    // Apply payment to ONLY the first due payment
    if ($firstDuePayment && $remainingAmount > 0) {
        $currentPaid = $firstDuePayment->amount_paid ?? 0;
        $amountDue = $firstDuePayment->total_amount - $currentPaid;
        
        if ($amountDue > 0) {
            $appliedAmount = min($remainingAmount, $amountDue);
            $newAmountPaid = $currentPaid + $appliedAmount;
            $newStatus = ($newAmountPaid >= $firstDuePayment->total_amount) ? 'paid' : 'partial';
            
            $firstDuePayment->update([
                'amount_paid' => $newAmountPaid,
                'status' => $newStatus,
                'date_paid' => $newStatus === 'paid' ? $paymentDate : $firstDuePayment->date_paid,
                'days_overdue' => $newStatus === 'paid' ? 0 : $firstDuePayment->days_overdue
            ]);
            
            $appliedBreakdown[] = [
                'installment_number' => $firstDuePayment->installment_number,
                'due_date' => $firstDuePayment->due_date,
                'amount_applied' => $appliedAmount,
                'status_before' => $firstDuePayment->status,
                'status_after' => $newStatus
            ];
            
            $remainingAmount -= $appliedAmount;
            $totalAppliedToSchedule = $appliedAmount;
            
            Log::info("Applied to first due payment {$firstDuePayment->installment_number}:", [
                'applied' => $appliedAmount,
                'new_status' => $newStatus,
                'remaining_lump_sum' => $remainingAmount
            ]);
        }
    }
    
    Log::info('Correct lump sum application result:', [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $remainingAmount,
        'paid_installments_before' => $paidCount,
        'paid_installments_after' => $paidCount + ($firstDuePayment && $appliedBreakdown[0]['status_after'] === 'paid' ? 1 : 0)
    ]);
    
    return [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $remainingAmount,
        'breakdown' => $appliedBreakdown,
        'paid_installments_before' => $paidCount,
        'paid_installments_after' => $paidCount + ($firstDuePayment && $appliedBreakdown[0]['status_after'] === 'paid' ? 1 : 0)
    ];
}

/**
 * CORRECTED: Calculate new principal balance after correct application
 */
private function calculateNewPrincipalBalanceCorrect($agreement, $principalReduction)
{
    Log::info('=== CORRECT PRINCIPAL BALANCE CALCULATION ===', [
        'agreement_id' => $agreement->id,
        'principal_reduction' => $principalReduction
    ]);
    
    // Get ALL unpaid/partial schedules
    $unpaidSchedules = $agreement->paymentSchedule()
        ->whereIn('status', ['pending', 'partial', 'overdue'])
        ->get();
    
    $totalRemainingPrincipal = 0;
    
    foreach ($unpaidSchedules as $schedule) {
        $paidRatio = $schedule->total_amount > 0 ? 
                    ($schedule->amount_paid / $schedule->total_amount) : 0;
        $unpaidPrincipal = $schedule->principal_amount * (1 - $paidRatio);
        $totalRemainingPrincipal += $unpaidPrincipal;
        
        Log::info("Schedule {$schedule->installment_number} principal:", [
            'total_principal' => $schedule->principal_amount,
            'paid_ratio' => $paidRatio,
            'unpaid_principal' => $unpaidPrincipal
        ]);
    }
    
    // Apply principal reduction
    $newPrincipalBalance = max(0, $totalRemainingPrincipal - $principalReduction);
    
    Log::info('Final principal calculation:', [
        'total_remaining_principal' => $totalRemainingPrincipal,
        'principal_reduction' => $principalReduction,
        'new_principal_balance' => $newPrincipalBalance
    ]);
    
    return $newPrincipalBalance;
}

/**
 * CORRECTED: Perform rescheduling with proper logic
 */
private function performReschedulingCorrect($agreement, $newPrincipalBalance, $rescheduleOption, $applicationResult)
{
    Log::info('=== CORRECT RESCHEDULING START ===', [
        'agreement_id' => $agreement->id,
        'new_principal_balance' => $newPrincipalBalance,
        'reschedule_option' => $rescheduleOption
    ]);
    
    // Clear future unpaid schedules (keep paid/partial ones)
    $this->clearFutureUnpaidSchedules($agreement->id);
    
    // Get remaining months and next due date
    $remainingMonths = $this->getRemainingMonths($agreement);
    $nextDueDate = $this->getNextDueDate($agreement);
    
    // Validate interest rate
    $monthlyInterestDecimal = $agreement->interest_rate / 100;
    if ($monthlyInterestDecimal <= 0) {
        throw new \Exception('Invalid interest rate: ' . $agreement->interest_rate . '%');
    }
    
    if ($rescheduleOption === 'reduce_duration') {
        return $this->rescheduleLoanReduceDurationCorrect($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal);
    } else {
        return $this->rescheduleLoanReduceInstallmentCorrect($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal);
    }
}

/**
 * CORRECTED: Generate payment schedule with PROPER INTEREST calculations
 */
private function generateNewPaymentScheduleWithInterest($agreementId, $principalAmount, $monthlyPayment, $duration, $monthlyInterestDecimal, $startDate)
{
    Log::info('=== GENERATING SCHEDULE WITH PROPER INTEREST ===', [
        'principal_amount' => $principalAmount,
        'monthly_payment' => $monthlyPayment,
        'duration' => $duration,
        'monthly_interest_decimal' => $monthlyInterestDecimal
    ]);
    
    // CRITICAL: Validate interest rate
    if ($monthlyInterestDecimal <= 0) {
        throw new \Exception('CRITICAL: Monthly interest rate is zero! Cannot generate schedule.');
    }
    
    $remainingPrincipal = $principalAmount;
    
    // Get the next installment number
    $lastInstallment = PaymentSchedule::where('agreement_id', $agreementId)
        ->max('installment_number') ?? 0;
    
    for ($month = 1; $month <= $duration; $month++) {
        // Calculate interest on remaining principal
        $monthlyInterest = $remainingPrincipal * $monthlyInterestDecimal;
        $monthlyPrincipal = $monthlyPayment - $monthlyInterest;
        
        // VALIDATION: Ensure positive interest
        if ($monthlyInterest <= 0) {
            Log::error('CRITICAL: Zero interest calculated!', [
                'remaining_principal' => $remainingPrincipal,
                'monthly_interest_decimal' => $monthlyInterestDecimal,
                'month' => $month
            ]);
            throw new \Exception('Invalid interest calculation - got zero interest');
        }
        
        // Ensure principal payment is positive
        if ($monthlyPrincipal < 0) {
            $monthlyPrincipal = 0;
            $monthlyInterest = $monthlyPayment;
        }
        
        // For the last payment, adjust to pay off exactly
        if ($month == $duration || $monthlyPrincipal >= $remainingPrincipal) {
            $monthlyPrincipal = $remainingPrincipal;
            $actualPayment = $monthlyPrincipal + $monthlyInterest;
            $newRemainingPrincipal = 0;
        } else {
            $actualPayment = $monthlyPayment;
            $newRemainingPrincipal = $remainingPrincipal - $monthlyPrincipal;
        }
        
        // Log first few payments for verification
        if ($month <= 3) {
            Log::info("CORRECTED Payment {$month} Details:", [
                'Starting Balance' => round($remainingPrincipal, 2),
                'Interest' => round($monthlyInterest, 2),
                'Principal' => round($monthlyPrincipal, 2),
                'Total Payment' => round($actualPayment, 2),
                'Ending Balance' => round($newRemainingPrincipal, 2)
            ]);
        }
        
        PaymentSchedule::create([
            'agreement_id' => $agreementId,
            'installment_number' => $lastInstallment + $month,
            'due_date' => Carbon::parse($startDate)->addMonths($month - 1),
            'principal_amount' => round($monthlyPrincipal, 2),
            'interest_amount' => round($monthlyInterest, 2), // PROPER INTEREST!
            'total_amount' => round($actualPayment, 2),
            'balance_after' => round($newRemainingPrincipal, 2),
            'status' => 'pending',
            'amount_paid' => 0,
            'date_paid' => null,
            'days_overdue' => 0
        ]);
        
        $remainingPrincipal = $newRemainingPrincipal;
        
        if ($remainingPrincipal <= 0) {
            break;
        }
    }
    
    Log::info('✅ Payment schedule generated with proper interest');
}

private function createLumpSumPaymentRecordAlternative($agreement, $request, $currentOutstanding)
{
    $paymentNumber = DB::table('hire_purchase_payments')
        ->where('agreement_id', $agreement->id)
        ->max('payment_number') + 1;

    $balanceAfter = $currentOutstanding - $request->payment_amount;

    $paymentData = [
        'agreement_id' => $agreement->id,
        'amount' => $request->payment_amount,
        'payment_date' => $request->payment_date,
        'payment_method' => $request->payment_method,
        'reference_number' => $request->payment_reference,
        'notes' => ($request->payment_notes ?? '') . ' (Lump Sum Payment with Rescheduling)',
        'payment_type' => 'early', // Use 'early' to indicate lump sum payment
        'penalty_amount' => 0,
        'payment_number' => $paymentNumber,
        'recorded_by' => auth()->id() ?? 1,
        'recorded_at' => now(),
        'is_verified' => false,
        'verified_by' => null,
        'verified_at' => null,
        'balance_before' => $currentOutstanding,
        'balance_after' => $balanceAfter,
        'created_at' => now(),
        'updated_at' => now()
    ];

    $paymentId = DB::table('hire_purchase_payments')->insertGetId($paymentData);

    return [
        'payment_id' => $paymentId,
        'payment_number' => $paymentNumber,
        'balance_before' => $currentOutstanding,
        'balance_after' => $balanceAfter
    ];
}

/**
 * Simplified show method without user table join
 */
public function show($id)
{
    $agreement = HirePurchaseAgreement::with([
        'customerVehicle',
        'carImport',
        'payments', 
        'paymentSchedule', 
        'approvedBy'
    ])->findOrFail($id);
    
    // Load rescheduling history directly without user join
    $reschedulingHistory = DB::table('loan_rescheduling_history')
        ->where('agreement_id', $id)
        ->where('status', 'active')
        ->orderBy('rescheduling_date', 'desc')
        ->get();
    
    // Convert to collection and add to agreement
    $agreement->reschedulingHistory = $reschedulingHistory->map(function($item) {
        // Convert date to Carbon instance for compatibility
        $item->rescheduling_date = \Carbon\Carbon::parse($item->rescheduling_date);
        // We don't need the processedBy relationship for basic functionality
        $item->processedBy = (object)['name' => 'System User'];
        return $item;
    });
    
    // Calculate accurate outstanding balance from payment schedule
    $totalScheduledAmount = $agreement->paymentSchedule ? $agreement->paymentSchedule->sum('total_amount') : 0;
    $totalPaidFromSchedule = $agreement->paymentSchedule ? $agreement->paymentSchedule->sum('amount_paid') : 0;
    $calculatedOutstanding = $totalScheduledAmount - $totalPaidFromSchedule;
    $actualOutstanding = $totalScheduledAmount > 0 ? $calculatedOutstanding : $agreement->outstanding_balance;
    
    // Calculate other metrics
    $totalAmountPaid = $agreement->deposit_amount + $agreement->amount_paid;
    $paymentProgress = $agreement->total_amount > 0 ? 
        (($totalAmountPaid) / $agreement->total_amount) * 100 : 0;
    
    $nextDueInstallment = $agreement->paymentSchedule ? 
        $agreement->paymentSchedule->whereIn('status', ['pending', 'overdue', 'partial'])->first() : null;
    
    $overdueAmount = $agreement->paymentSchedule ? 
        $agreement->paymentSchedule->where('status', 'overdue')->sum('total_amount') : 0;
    
    // Calculate rescheduling statistics
    $reschedulingStats = [
        'count' => $reschedulingHistory->count(),
        'total_lump_sum' => $reschedulingHistory->sum('lump_sum_amount'),
        'total_interest_savings' => $reschedulingHistory->sum('total_interest_savings'),
        'latest_rescheduling' => $reschedulingHistory->first()
    ];
    
    // Add computed properties to agreement
    $agreement->is_rescheduled = $reschedulingStats['count'] > 0;
    $agreement->rescheduling_count = $reschedulingStats['count'];
    $agreement->total_lump_sum_payments = $reschedulingStats['total_lump_sum'];
    $agreement->total_interest_savings = $reschedulingStats['total_interest_savings'];
    $agreement->latest_rescheduling = $reschedulingStats['latest_rescheduling'];
    
    return view('hirepurchase.loan-management', compact(
        'agreement',
        'actualOutstanding',
        'totalAmountPaid',
        'paymentProgress',
        'nextDueInstallment',
        'overdueAmount',
        'reschedulingStats'
    ));
}

/**
 * Simplified rescheduling history without user join
 */
public function getReschedulingHistory($agreementId)
{
    return DB::table('loan_rescheduling_history as lrh')
        ->leftJoin('hire_purchase_payments as hpp', 'lrh.payment_id', '=', 'hpp.id')
        ->where('lrh.agreement_id', $agreementId)
        ->select(
            'lrh.*',
            'hpp.reference_number as payment_reference'
        )
        ->orderBy('lrh.rescheduling_date', 'desc')
        ->get();
}


/**
 * Simplified payment creation
 */
private function createSimpleLumpSumPayment($agreement, $request, $currentOutstanding)
{
    $paymentNumber = DB::table('hire_purchase_payments')
        ->where('agreement_id', $agreement->id)
        ->max('payment_number') + 1;

    $paymentData = [
        'agreement_id' => $agreement->id,
        'amount' => $request->payment_amount,
        'payment_date' => $request->payment_date,
        'payment_method' => $request->payment_method,
        'reference_number' => $request->payment_reference,
        'notes' => ($request->payment_notes ?? '') . ' (Lump Sum Payment)',
        'payment_type' => 'lump_sum',
        'penalty_amount' => 0,
        'payment_number' => $paymentNumber,
        'recorded_by' => auth()->id() ?? 1,
        'recorded_at' => now(),
        'is_verified' => false,
        'created_at' => now(),
        'updated_at' => now()
    ];

    // Add optional columns if they exist
    if (Schema::hasColumn('hire_purchase_payments', 'is_lump_sum')) {
        $paymentData['is_lump_sum'] = true;
    }
    
    if (Schema::hasColumn('hire_purchase_payments', 'balance_before')) {
        $paymentData['balance_before'] = $currentOutstanding;
        $paymentData['balance_after'] = $currentOutstanding - $request->payment_amount;
    }

    return DB::table('hire_purchase_payments')->insertGetId($paymentData);
}


/**
 * Simplified rescheduling logic
 */
private function performSimpleRescheduling($agreement, $newBalance, $rescheduleOption)
{
    $monthlyInterestDecimal = $agreement->monthly_interest_rate / 100;
    $remainingMonths = $this->getRemainingMonths($agreement);
    $currentPayment = $agreement->monthly_payment;

    if ($rescheduleOption === 'reduce_duration') {
        // Keep same payment, reduce duration
        if ($monthlyInterestDecimal == 0) {
            $newDuration = ceil($newBalance / $currentPayment);
        } else {
            $factor = ($currentPayment - ($newBalance * $monthlyInterestDecimal)) / $currentPayment;
            $newDuration = $factor > 0 ? ceil(-log(1 - $factor) / log(1 + $monthlyInterestDecimal)) : $remainingMonths;
        }
        $newDuration = max(1, min($newDuration, $remainingMonths));
        
        return [
            'reschedule_type' => 'reduce_duration',
            'new_duration' => $newDuration,
            'duration_reduction' => $remainingMonths - $newDuration,
            'monthly_payment' => $currentPayment,
            'new_outstanding_balance' => $newBalance,
            'savings_message' => "Loan duration reduced by " . ($remainingMonths - $newDuration) . " months"
        ];
    } else {
        // Keep same duration, reduce payment
        $newPayment = $this->calculatePMT($newBalance, $monthlyInterestDecimal, $remainingMonths);
        
        return [
            'reschedule_type' => 'reduce_installment',
            'new_monthly_payment' => $newPayment,
            'payment_reduction' => $currentPayment - $newPayment,
            'remaining_duration' => $remainingMonths,
            'new_outstanding_balance' => $newBalance,
            'savings_message' => "Monthly payment reduced by KSh " . number_format($currentPayment - $newPayment, 2)
        ];
    }
}

/**
 * Simplified history creation
 */
private function createSimpleReschedulingHistory($agreement, $paymentId, $request, $oldBalance, $newBalance, $reschedulingResult)
{
    return DB::table('loan_rescheduling_history')->insertGetId([
        'agreement_id' => $agreement->id,
        'payment_id' => $paymentId,
        'reschedule_type' => $request->reschedule_option,
        'lump_sum_amount' => $request->payment_amount,
        'outstanding_before' => $oldBalance,
        'outstanding_after' => $newBalance,
        
        // Previous terms
        'previous_duration_months' => $this->getRemainingMonths($agreement),
        'previous_monthly_payment' => $agreement->monthly_payment,
        'previous_completion_date' => $agreement->expected_completion_date,
        
        // New terms
        'new_duration_months' => $reschedulingResult['reschedule_type'] === 'reduce_duration' 
            ? $reschedulingResult['new_duration'] 
            : $reschedulingResult['remaining_duration'],
        'new_monthly_payment' => $reschedulingResult['reschedule_type'] === 'reduce_duration' 
            ? $reschedulingResult['monthly_payment'] 
            : $reschedulingResult['new_monthly_payment'],
        'new_completion_date' => now()->addMonths($reschedulingResult['reschedule_type'] === 'reduce_duration' 
            ? $reschedulingResult['new_duration'] 
            : $reschedulingResult['remaining_duration'])->format('Y-m-d'),
        
        // Changes
        'duration_change_months' => $reschedulingResult['reschedule_type'] === 'reduce_duration' 
            ? $reschedulingResult['duration_reduction'] 
            : null,
        'payment_change_amount' => $reschedulingResult['reschedule_type'] === 'reduce_installment' 
            ? $reschedulingResult['payment_reduction'] 
            : null,
        'total_interest_savings' => 0, // Simplified for now
        
        'rescheduling_date' => now()->format('Y-m-d'),
        'processed_by' => auth()->id() ?? 1,
        'notes' => "Lump sum payment of KSh " . number_format($request->payment_amount, 2) . " with " . $request->reschedule_option,
        'status' => 'active',
        
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

/**
 * Add/update missing columns to hire_purchase_payments table
 * Run this method once to add missing fields
 */
public function addMissingPaymentColumns()
{
    try {
        Schema::table('hire_purchase_payments', function (Blueprint $table) {
            // Check and add missing columns
            if (!Schema::hasColumn('hire_purchase_payments', 'is_lump_sum')) {
                $table->boolean('is_lump_sum')->default(false);
            }
            
            if (!Schema::hasColumn('hire_purchase_payments', 'rescheduling_id')) {
                $table->unsignedBigInteger('rescheduling_id')->nullable();
            }
            
            if (!Schema::hasColumn('hire_purchase_payments', 'balance_before')) {
                $table->decimal('balance_before', 15, 2)->nullable();
            }
            
            if (!Schema::hasColumn('hire_purchase_payments', 'balance_after')) {
                $table->decimal('balance_after', 15, 2)->nullable();
            }
            
            if (!Schema::hasColumn('hire_purchase_payments', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable();
            }
            
            if (!Schema::hasColumn('hire_purchase_payments', 'verified_at')) {
                $table->timestamp('verified_at')->nullable();
            }
        });
        
        return "Missing columns added successfully";
        
    } catch (\Exception $e) {
        Log::error('Error adding missing payment columns: ' . $e->getMessage());
        return "Error: " . $e->getMessage();
    }
}

/**
 * Check what columns exist in hire_purchase_payments table
 */
public function checkPaymentTableStructure()
{
    try {
        $columns = DB::select("DESCRIBE hire_purchase_payments");
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = [
            'is_lump_sum',
            'rescheduling_id',
            'balance_before',
            'balance_after',
            'verified_by',
            'verified_at'
        ];
        
        $missingColumns = array_diff($requiredColumns, $columnNames);
        
        return [
            'existing_columns' => $columnNames,
            'required_columns' => $requiredColumns,
            'missing_columns' => $missingColumns,
            'all_present' => empty($missingColumns)
        ];
        
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/**
 * Enhanced duration reduction with accurate calculations
 */
private function rescheduleLoanReduceDurationEnhanced($agreement, $newPrincipalBalance, $applicationResult)
{
    $monthlyInterestDecimal = $agreement->monthly_interest_rate / 100;
    $currentMonthlyPayment = $agreement->monthly_payment;
    
    // Calculate new duration using the same PMT logic as agreement creation
    if ($monthlyInterestDecimal == 0) {
        $newDuration = ceil($newPrincipalBalance / $currentMonthlyPayment);
    } else {
        // Solve for n in PMT formula: PMT = P * [r(1+r)^n] / [(1+r)^n - 1]
        if ($newPrincipalBalance <= 0) {
            $newDuration = 0;
        } else {
            $factor = ($currentMonthlyPayment - ($newPrincipalBalance * $monthlyInterestDecimal)) / $currentMonthlyPayment;
            if ($factor <= 0) {
                // Payment is too small, use maximum reasonable duration
                $newDuration = $this->getRemainingMonths($agreement);
            } else {
                $newDuration = ceil(-log(1 - $factor) / log(1 + $monthlyInterestDecimal));
            }
        }
    }
    
    $newDuration = max(1, $newDuration);
    $originalDuration = $this->getRemainingMonths($agreement);
    $durationReduction = max(0, $originalDuration - $newDuration);
    
    // Clear future schedules and regenerate
    $nextDueDate = $this->getNextDueDate($agreement);
    $this->clearFuturePaymentSchedules($agreement->id);
    
    if ($newDuration > 0) {
        $this->generateNewPaymentSchedule(
            $agreement->id,
            $newPrincipalBalance,
            $currentMonthlyPayment,
            $newDuration,
            $monthlyInterestDecimal,
            $nextDueDate
        );
    }
    
    return [
        'reschedule_type' => 'reduce_duration',
        'original_duration' => $originalDuration,
        'new_duration' => $newDuration,
        'duration_reduction' => $durationReduction,
        'monthly_payment' => $currentMonthlyPayment,
        'new_outstanding_balance' => $newPrincipalBalance,
        'new_completion_date' => $newDuration > 0 ? 
            Carbon::parse($nextDueDate)->addMonths($newDuration - 1) : now(),
        'savings_message' => "Loan duration reduced by {$durationReduction} months"
    ];
}

/**
 * Enhanced installment reduction with accurate calculations
 */
private function rescheduleLoanReduceInstallmentEnhanced($agreement, $newPrincipalBalance, $applicationResult)
{
    $monthlyInterestDecimal = $agreement->monthly_interest_rate / 100;
    $remainingMonths = $this->getRemainingMonths($agreement);
    
    // Calculate new monthly payment using same PMT logic as agreement creation
    $newMonthlyPayment = $this->calculatePMT(
        $newPrincipalBalance,
        $monthlyInterestDecimal,
        $remainingMonths
    );
    
    $originalPayment = $agreement->monthly_payment;
    $paymentReduction = max(0, $originalPayment - $newMonthlyPayment);
    
    // Clear future schedules and regenerate
    $nextDueDate = $this->getNextDueDate($agreement);
    $this->clearFuturePaymentSchedules($agreement->id);
    
    if ($remainingMonths > 0) {
        $this->generateNewPaymentSchedule(
            $agreement->id,
            $newPrincipalBalance,
            $newMonthlyPayment,
            $remainingMonths,
            $monthlyInterestDecimal,
            $nextDueDate
        );
    }
    
    return [
        'reschedule_type' => 'reduce_installment',
        'original_monthly_payment' => $originalPayment,
        'new_monthly_payment' => $newMonthlyPayment,
        'payment_reduction' => $paymentReduction,
        'remaining_duration' => $remainingMonths,
        'new_outstanding_balance' => $newPrincipalBalance,
        'savings_message' => "Monthly payment reduced by KSh " . number_format($paymentReduction, 2)
    ];
}




    /**
     * Get interest rate based on deposit percentage (updated to match frontend)
     */
    private function getInterestRateByDeposit($depositPercentage)
    {
        if ($depositPercentage >= 50) {
            return 4.29; // 50%+ deposit gets 4.29% monthly
        } else {
            return 4.50; // Below 50% deposit gets 4.50% monthly
        }
    }


/**
 * Clear future unpaid payment schedules for rescheduling
 */
private function clearFuturePaymentSchedules($agreementId)
{
    $today = now()->toDateString();
    
    // Delete future unpaid schedules using Eloquent
    PaymentSchedule::where('agreement_id', $agreementId)
        ->where(function($query) use ($today) {
            $query->where('status', 'pending')
                  ->orWhere(function($q) use ($today) {
                      $q->where('status', 'partial')
                        ->where('due_date', '>', $today);
                  });
        })
        ->delete();
}

/**
 * Create rescheduling history record
 */
private function createReschedulingHistory($agreement, $paymentId, $lumpSumAmount, $outstandingBefore, $outstandingAfter, $originalTerms, $reschedulingResult, $rescheduleType)
{
    // Calculate interest savings (simplified calculation)
    $originalTotalPayments = $originalTerms['monthly_payment'] * $originalTerms['duration_months'];
    $newTotalPayments = $reschedulingResult['reschedule_type'] === 'reduce_duration' 
        ? $reschedulingResult['monthly_payment'] * $reschedulingResult['new_duration']
        : $reschedulingResult['new_monthly_payment'] * $reschedulingResult['remaining_duration'];
    
    $interestSavings = $originalTotalPayments - $newTotalPayments - $lumpSumAmount;

    return DB::table('loan_rescheduling_history')->insertGetId([
        'agreement_id' => $agreement->id,
        'payment_id' => $paymentId,
        'reschedule_type' => $rescheduleType,
        'lump_sum_amount' => $lumpSumAmount,
        'outstanding_before' => $outstandingBefore,
        'outstanding_after' => $outstandingAfter,
        
        // Previous terms
        'previous_duration_months' => $originalTerms['duration_months'],
        'previous_monthly_payment' => $originalTerms['monthly_payment'],
        'previous_completion_date' => $originalTerms['completion_date'],
        
        // New terms
        'new_duration_months' => $reschedulingResult['reschedule_type'] === 'reduce_duration' 
            ? $reschedulingResult['new_duration'] 
            : $reschedulingResult['remaining_duration'],
        'new_monthly_payment' => $reschedulingResult['reschedule_type'] === 'reduce_duration' 
            ? $reschedulingResult['monthly_payment'] 
            : $reschedulingResult['new_monthly_payment'],
        'new_completion_date' => now()->addMonths(
            $reschedulingResult['reschedule_type'] === 'reduce_duration' 
                ? $reschedulingResult['new_duration'] 
                : $reschedulingResult['remaining_duration']
        ),
        
        // Changes
        'duration_change_months' => $reschedulingResult['reschedule_type'] === 'reduce_duration' 
            ? $reschedulingResult['duration_reduction'] 
            : 0,
        'payment_change_amount' => $reschedulingResult['reschedule_type'] === 'reduce_installment' 
            ? $reschedulingResult['payment_reduction'] 
            : 0,
        'total_interest_savings' => max(0, $interestSavings),
        
        'rescheduling_date' => now(),
        'processed_by' => auth()->id() ?? 1,
        'notes' => "Lump sum payment of KSh " . number_format($lumpSumAmount, 2) . " applied with " . $rescheduleType,
        'status' => 'active',
        
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

/**
 * Helper methods
 */
private function calculateCurrentOutstanding($agreement)
{
    $totalScheduledAmount = $agreement->paymentSchedule->sum('total_amount');
    $totalPaidFromSchedule = $agreement->paymentSchedule->sum('amount_paid');
    
    return $totalScheduledAmount > 0 
        ? $totalScheduledAmount - $totalPaidFromSchedule 
        : $agreement->outstanding_balance;
}

private function getNextPaymentNumber($agreementId)
{
    return DB::table('hire_purchase_payments')
        ->where('agreement_id', $agreementId)
        ->max('payment_number') + 1;
}




/**
 * Show rescheduling history page
 */
public function showReschedulingHistory($agreementId)
{
    $agreement = HirePurchaseAgreement::findOrFail($agreementId);
    $history = $this->getReschedulingHistory($agreementId);
    
    return view('hire-purchase.rescheduling-history', compact('agreement', 'history'));
}

/**
 * Get loan rescheduling analytics
 */
public function getReschedulingAnalytics($agreementId = null)
{
    $query = DB::table('loan_rescheduling_history as lrh')
        ->join('hire_purchase_agreements as hpa', 'lrh.agreement_id', '=', 'hpa.id');
    
    if ($agreementId) {
        $query->where('lrh.agreement_id', $agreementId);
    }
    
    $analytics = $query->selectRaw('
        COUNT(*) as total_reschedulings,
        SUM(lrh.lump_sum_amount) as total_lump_sum_payments,
        SUM(lrh.total_interest_savings) as total_interest_savings,
        SUM(CASE WHEN lrh.reschedule_type = "reduce_duration" THEN 1 ELSE 0 END) as duration_reductions,
        SUM(CASE WHEN lrh.reschedule_type = "reduce_installment" THEN 1 ELSE 0 END) as payment_reductions,
        AVG(lrh.lump_sum_amount) as avg_lump_sum_amount,
        AVG(lrh.duration_change_months) as avg_duration_reduction,
        AVG(lrh.payment_change_amount) as avg_payment_reduction
    ')->first();
    
    return $analytics;
}

/**
 * Export rescheduling report
 */
public function exportReschedulingReport(Request $request)
{
    $startDate = $request->get('start_date', now()->subMonths(6)->toDateString());
    $endDate = $request->get('end_date', now()->toDateString());
    
    $data = DB::table('loan_rescheduling_history as lrh')
        ->join('hire_purchase_agreements as hpa', 'lrh.agreement_id', '=', 'hpa.id')
        ->join('users as u', 'lrh.processed_by', '=', 'u.id')
        ->whereBetween('lrh.rescheduling_date', [$startDate, $endDate])
        ->select([
            'hpa.client_name',
            'hpa.national_id',
            'hpa.vehicle_make',
            'hpa.vehicle_model',
            'lrh.reschedule_type',
            'lrh.lump_sum_amount',
            'lrh.outstanding_before',
            'lrh.outstanding_after',
            'lrh.previous_duration_months',
            'lrh.new_duration_months',
            'lrh.previous_monthly_payment',
            'lrh.new_monthly_payment',
            'lrh.duration_change_months',
            'lrh.payment_change_amount',
            'lrh.total_interest_savings',
            'lrh.rescheduling_date',
            'u.name as processed_by'
        ])
        ->orderBy('lrh.rescheduling_date', 'desc')
        ->get();
    
    return response()->json([
        'success' => true,
        'data' => $data,
        'summary' => $this->getReschedulingAnalytics(),
        'period' => [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]
    ]);
}

/**
 * Validate rescheduling eligibility
 */
public function validateReschedulingEligibility($agreementId, $lumpSumAmount)
{
    $agreement = HirePurchaseAgreement::findOrFail($agreementId);
    
    $validations = [
        'eligible' => true,
        'errors' => [],
        'warnings' => []
    ];
    
    // Check if agreement is active
    if ($agreement->status === 'completed') {
        $validations['eligible'] = false;
        $validations['errors'][] = 'Cannot reschedule a completed agreement';
    }
    
    if ($agreement->status === 'pending') {
        $validations['eligible'] = false;
        $validations['errors'][] = 'Agreement must be approved before rescheduling';
    }
    
    // Check minimum lump sum amount (e.g., at least one monthly payment)
    if ($lumpSumAmount < $agreement->monthly_payment) {
        $validations['warnings'][] = 'Lump sum amount is less than one monthly payment';
    }
    
    // Check if too many reschedulings already done
    $reschedulingCount = DB::table('loan_rescheduling_history')
        ->where('agreement_id', $agreementId)
        ->where('status', 'active')
        ->count();
    
    if ($reschedulingCount >= 3) {
        $validations['eligible'] = false;
        $validations['errors'][] = 'Maximum number of reschedulings (3) already reached';
    }
    
    // Check if recent rescheduling exists (within last 3 months)
    $recentRescheduling = DB::table('loan_rescheduling_history')
        ->where('agreement_id', $agreementId)
        ->where('status', 'active')
        ->where('rescheduling_date', '>', now()->subMonths(3))
        ->exists();
    
    if ($recentRescheduling) {
        $validations['warnings'][] = 'Recent rescheduling found within the last 3 months';
    }
    
    return $validations;
}

/**
 * API endpoint for rescheduling validation
 */
public function checkReschedulingEligibility(Request $request)
{
    $agreementId = $request->get('agreement_id');
    $lumpSumAmount = $request->get('lump_sum_amount', 0);
    
    if (!$agreementId) {
        return response()->json(['error' => 'Agreement ID is required'], 400);
    }
    
    try {
        $validation = $this->validateReschedulingEligibility($agreementId, $lumpSumAmount);
        return response()->json($validation);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Validation failed: ' . $e->getMessage()], 500);
    }
}
    /**  
     * Store method updated to use dynamic tracking fees
     */
   /**  
 * Store method updated to store monthly interest rate instead of annual rate
 */
public function store(Request $request)
{
    try {
        // Log incoming request for debugging
        Log::info('Hire Purchase Store Request:', $request->all());
        
        $validated = $request->validate([
            'client_name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:100',
            'national_id' => 'required|string|max:20',
            'phone_numberalt' => 'nullable|string|max:20',
            'emailalt' => 'nullable|string|max:20',
            'kra_pin' => 'nullable|string|max:20',
            'vehicle_id' => 'required|string',
            'vehicle_price' => 'required|numeric|min:1',
            'deposit_amount' => 'required|numeric|min:1',
            'tracking_fees' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:6|max:72',
            'first_due_date' => 'required|date',
        ], [
            'client_name.required' => 'Client name is required.',
            'phone_number.required' => 'Phone number is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'national_id.required' => 'National ID is required.',
            'vehicle_id.required' => 'Please select a vehicle.',
            'vehicle_price.required' => 'Vehicle price is required.',
            'vehicle_price.min' => 'Vehicle price must be greater than 0.',
            'deposit_amount.required' => 'Deposit amount is required.',
            'deposit_amount.min' => 'Deposit amount must be greater than 0.',
            'tracking_fees.required' => 'Tracking fees are required.',
            'tracking_fees.min' => 'Tracking fees must be 0 or greater.',
            'duration_months.required' => 'Loan duration is required.',
            'duration_months.min' => 'Minimum loan duration is 6 months.',
            'duration_months.max' => 'Maximum loan duration is 72 months.',
            'first_due_date.required' => 'First payment due date is required.',
            'first_due_date.after' => 'First payment due date must be after today.',
        ]);

        // Calculate loan details using dynamic tracking fee
        $vehiclePrice = $validated['vehicle_price'];
        $depositAmount = $validated['deposit_amount'];
        $trackingFee = $validated['tracking_fees'];
        $depositPercentage = ($depositAmount / $vehiclePrice) * 100;

        // Validate minimum deposit (30%)
        if ($depositPercentage < 30) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum deposit is 30% of vehicle price.',
                'errors' => [
                    'deposit_amount' => ['Minimum deposit is 30% of vehicle price.']
                ]
            ], 422);
        }

        // Calculate loan amounts with dynamic tracking fee
        $baseLoanAmount = $vehiclePrice - $depositAmount;
        $totalLoanAmount = $baseLoanAmount + $trackingFee;
        $durationMonths = $validated['duration_months'];
        
        // Get monthly interest rate based on deposit percentage
        $monthlyInterestRate = $this->getInterestRateByDeposit($depositPercentage);
        $monthlyInterestDecimal = $monthlyInterestRate / 100;
        
        Log::info('Interest Rate Calculation:', [
            'deposit_percentage' => $depositPercentage,
            'monthly_interest_rate' => $monthlyInterestRate,
            'monthly_interest_decimal' => $monthlyInterestDecimal
        ]);
        
        // Calculate monthly payment using the PMT formula
        $monthlyPayment = $this->calculatePMT(
            $totalLoanAmount, 
            $monthlyInterestDecimal, 
            $durationMonths
        );
        
        // Calculate totals
        $totalPayments = $monthlyPayment * $durationMonths;
        $totalInterest = $totalPayments - $totalLoanAmount;
        $totalAmount = $vehiclePrice + $trackingFee + $totalInterest;

        Log::info('Final Calculations:', [
            'monthly_payment' => $monthlyPayment,
            'total_payments' => $totalPayments,
            'total_interest' => $totalInterest,
            'total_amount' => $totalAmount,
            'monthly_rate_to_store' => $monthlyInterestRate // This should be 4.29 or 4.5
        ]);

        // Parse vehicle information
        [$vehicleType, $vehicleId] = explode('-', $validated['vehicle_id'], 2);
        $vehicleInfo = $this->getVehicleInfo($vehicleType, $vehicleId);

        // Validate vehicle exists
        if (!$vehicleInfo || $vehicleInfo['make'] === 'Unknown') {
            return response()->json([
                'success' => false,
                'message' => 'Selected vehicle not found. Please select a valid vehicle.',
                'errors' => [
                    'vehicle_id' => ['Selected vehicle not found.']
                ]
            ], 422);
        }

        DB::beginTransaction();
        
        $agreementData = [
            'client_name' => $validated['client_name'],
            'phone_number' => $validated['phone_number'],
            'email' => $validated['email'],
            'national_id' => $validated['national_id'],
            'kra_pin' => $validated['kra_pin'],
            'phone_numberalt' => $validated['phone_numberalt'],
            'emailalt' => $validated['emailalt'],
            'car_type' => $vehicleType,
            'car_id' => $vehicleId,
            'vehicle_make' => $vehicleInfo['make'],
            'vehicle_model' => $vehicleInfo['model'],
            'vehicle_year' => $vehicleInfo['year'] ?? null,
            'vehicle_plate' => $vehicleInfo['plate'] ?? null,
            'vehicle_price' => $vehiclePrice,
            'deposit_amount' => $depositAmount,
            'loan_amount' => $totalLoanAmount,
            'base_loan_amount' => $baseLoanAmount,
            'tracking_fee' => $trackingFee,
            // 🔥 KEY CHANGE: Store monthly interest rate instead of annual
            'interest_rate' => $monthlyInterestRate, // This will be 4.29 or 4.5, not 51.48
            'monthly_interest_rate' => $monthlyInterestRate, // Keep this field consistent
            'duration_months' => $durationMonths,
            'monthly_payment' => $monthlyPayment,
            'total_interest' => $totalInterest,
            'total_amount' => $totalAmount,
            'outstanding_balance' => $totalLoanAmount,
            'payments_remaining' => $durationMonths,
            'agreement_date' => today(),
            'first_due_date' => $validated['first_due_date'],
            'expected_completion_date' => Carbon::parse($validated['first_due_date'])->addMonths($durationMonths - 1),
            'status' => 'pending'
        ];

        // Set the appropriate foreign key based on vehicle type
        if ($vehicleType === 'import') {
            $agreementData['imported_id'] = $vehicleId;
        } elseif ($vehicleType === 'customer') {
            $agreementData['customer_id'] = $vehicleId;
        }

        $agreement = HirePurchaseAgreement::create($agreementData);

        // Generate amortization schedule with monthly interest rate
        $this->generateAmortizationSchedule($agreement, $monthlyInterestDecimal);

        DB::commit();

        Log::info("Hire Purchase Agreement created successfully", [
            'agreement_id' => $agreement->id,
            'client_name' => $agreement->client_name,
            'vehicle' => $agreement->vehicle_make . ' ' . $agreement->vehicle_model,
            'tracking_fee' => $trackingFee,
            'monthly_payment' => $monthlyPayment,
            'monthly_interest_rate_stored' => $monthlyInterestRate, // Should be 4.29 or 4.5
            'total_interest' => $totalInterest
        ]);

        return response()->json([
            'success' => true,
            'message' => "Agreement created successfully for {$agreement->client_name}!",
            'data' => [
                'agreement_id' => $agreement->id,
                'client_name' => $agreement->client_name,
                'vehicle' => $agreement->vehicle_make . ' ' . $agreement->vehicle_model,
                'loan_amount' => number_format($agreement->loan_amount, 2),
                'tracking_fee' => number_format($trackingFee, 2),
                'monthly_payment' => number_format($agreement->monthly_payment, 2),
                'monthly_interest_rate' => $monthlyInterestRate . '%',
                'duration' => $agreement->duration_months,
                'redirect_url' => route('hire-purchase.index')
            ]
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::warning('Hire Purchase Validation Error:', $e->errors());
        
        return response()->json([
            'success' => false,
            'message' => 'Please check the form data and try again.',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        DB::rollback();
        
        Log::error('Hire Purchase Creation Error:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'request_data' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred. Please try again or contact support.',
            'error_details' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}


/**
 * SIMULATION: Simulate lump sum application without saving to database
 */
private function simulateLumpSumApplication($agreement, $paymentAmount)
{
    $remainingAmount = $paymentAmount;
    $today = now()->toDateString();
    
    // Get due payments in priority order (same logic as actual application)
    $overdue = $agreement->paymentSchedule()
        ->where('status', 'overdue')
        ->orderBy('due_date', 'asc')
        ->get();
        
    $partial = $agreement->paymentSchedule()
        ->where('status', 'partial')
        ->orderBy('due_date', 'asc')
        ->get();
        
    $currentDue = $agreement->paymentSchedule()
        ->where('status', 'pending')
        ->where('due_date', '<=', $today)
        ->orderBy('due_date', 'asc')
        ->get();
    
    $allDuePayments = collect()
        ->merge($overdue)
        ->merge($partial)
        ->merge($currentDue);
    
    $breakdown = [];
    $totalAppliedToSchedule = 0;
    
    foreach ($allDuePayments as $payment) {
        if ($remainingAmount <= 0) break;
        
        $currentPaid = $payment->amount_paid ?? 0;
        $amountDue = $payment->total_amount - $currentPaid;
        
        if ($amountDue <= 0) continue;
        
        $appliedAmount = min($remainingAmount, $amountDue);
        $newAmountPaid = $currentPaid + $appliedAmount;
        $newStatus = ($newAmountPaid >= $payment->total_amount) ? 'paid' : 
                    ($payment->status === 'overdue' ? 'overdue' : 'partial');
        
        $breakdown[] = [
            'installment_number' => $payment->installment_number,
            'due_date' => $payment->due_date,
            'amount_applied' => $appliedAmount,
            'status_before' => $payment->status,
            'status_after' => $newStatus
        ];
        
        $remainingAmount -= $appliedAmount;
        $totalAppliedToSchedule += $appliedAmount;
        
        // If partial payment, stop here
        if ($newStatus !== 'paid') {
            break;
        }
    }
    
    // Calculate remaining principal after simulation
    $unpaidSchedules = $agreement->paymentSchedule()
        ->whereIn('status', ['pending', 'partial', 'overdue'])
        ->get();
    
    $totalRemainingPrincipal = 0;
    foreach ($unpaidSchedules as $schedule) {
        // Simulate payment application to this schedule
        $simulatedAmountPaid = $schedule->amount_paid;
        foreach ($breakdown as $application) {
            if ($application['installment_number'] == $schedule->installment_number) {
                $simulatedAmountPaid += $application['amount_applied'];
                break;
            }
        }
        
        $paidRatio = $schedule->total_amount > 0 ? 
                    ($simulatedAmountPaid / $schedule->total_amount) : 0;
        $unpaidPrincipal = $schedule->principal_amount * (1 - $paidRatio);
        $totalRemainingPrincipal += $unpaidPrincipal;
    }
    
    $principalReduction = $remainingAmount;
    $finalRemainingPrincipal = max(0, $totalRemainingPrincipal - $principalReduction);
    
    return [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $remainingAmount,
        'remaining_principal' => $finalRemainingPrincipal,
        'breakdown' => $breakdown
    ];
}


/**
 * FLEXIBLE: Apply lump sum payment to schedule (matches earlier implementation)
 */
private function applyLumpSumFlexible($agreementId, $paymentAmount, $paymentDate)
{
    $remainingAmount = $paymentAmount;
    $today = Carbon::parse($paymentDate)->toDateString();
    
    Log::info('=== FLEXIBLE LUMP SUM APPLICATION ===', [
        'agreement_id' => $agreementId,
        'payment_amount' => $paymentAmount,
        'payment_date' => $paymentDate
    ]);
    
    // Get current loan status
    $paidCount = PaymentSchedule::where('agreement_id', $agreementId)
        ->where('status', 'paid')
        ->count();
        
    $overdue = PaymentSchedule::where('agreement_id', $agreementId)
        ->where('status', 'overdue')
        ->orderBy('due_date', 'asc')
        ->get();
        
    $partial = PaymentSchedule::where('agreement_id', $agreementId)
        ->where('status', 'partial')
        ->orderBy('due_date', 'asc')
        ->get();
        
    $currentDue = PaymentSchedule::where('agreement_id', $agreementId)
        ->where('status', 'pending')
        ->where('due_date', '<=', $today)
        ->orderBy('due_date', 'asc')
        ->get();
    
    Log::info('Current loan status:', [
        'paid_installments' => $paidCount,
        'overdue_count' => $overdue->count(),
        'partial_count' => $partial->count(),
        'current_due_count' => $currentDue->count()
    ]);
    
    $appliedBreakdown = [];
    $totalAppliedToSchedule = 0;
    
    // Apply payment in priority order
    $allDuePayments = collect()
        ->merge($overdue)      // Highest priority
        ->merge($partial)      // Second priority
        ->merge($currentDue);  // Third priority
    
    foreach ($allDuePayments as $payment) {
        if ($remainingAmount <= 0) break;
        
        $currentPaid = $payment->amount_paid ?? 0;
        $amountDue = $payment->total_amount - $currentPaid;
        
        if ($amountDue <= 0) continue;
        
        // Apply payment to this installment
        $appliedAmount = min($remainingAmount, $amountDue);
        $newAmountPaid = $currentPaid + $appliedAmount;
        
        $newStatus = ($newAmountPaid >= $payment->total_amount) ? 'paid' : 
                    ($payment->status === 'overdue' ? 'overdue' : 'partial');
        
        $payment->update([
            'amount_paid' => $newAmountPaid,
            'status' => $newStatus,
            'date_paid' => $newStatus === 'paid' ? $paymentDate : $payment->date_paid,
            'days_overdue' => $newStatus === 'paid' ? 0 : $payment->days_overdue
        ]);
        
        $appliedBreakdown[] = [
            'installment_number' => $payment->installment_number,
            'due_date' => $payment->due_date,
            'amount_applied' => $appliedAmount,
            'status_before' => $payment->status,
            'status_after' => $newStatus,
            'was_overdue' => $payment->status === 'overdue'
        ];
        
        $remainingAmount -= $appliedAmount;
        $totalAppliedToSchedule += $appliedAmount;
        
        Log::info("Applied to installment {$payment->installment_number}:", [
            'applied' => $appliedAmount,
            'new_status' => $newStatus,
            'remaining_lump_sum' => $remainingAmount
        ]);
        
        // If partial payment, stop here
        if ($newStatus !== 'paid') {
            Log::info('Partially paid installment, stopping schedule application');
            break;
        }
    }
    
    Log::info('Flexible lump sum application result:', [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $remainingAmount,
        'installments_affected' => count($appliedBreakdown)
    ]);
    
    return [
        'total_applied_to_schedule' => $totalAppliedToSchedule,
        'remaining_for_principal_reduction' => $remainingAmount,
        'breakdown' => $appliedBreakdown,
        'paid_installments_before' => $paidCount,
        'paid_installments_after' => $paidCount + collect($appliedBreakdown)->where('status_after', 'paid')->count()
    ];
}

/**
 * Helper method to get remaining months flexibly
 */
private function getRemainingMonthsFlexible($agreement)
{
    $unpaidCount = PaymentSchedule::where('agreement_id', $agreement->id)
        ->whereIn('status', ['pending', 'partial', 'overdue'])
        ->count();
    
    return max(1, $unpaidCount);
}

/**
 * Helper method to calculate remaining principal flexibly
 */
private function calculateRemainingPrincipalFlexible($agreement, $principalReduction)
{
    $unpaidSchedules = $agreement->paymentSchedule()
        ->whereIn('status', ['pending', 'partial', 'overdue'])
        ->get();
    
    $totalRemainingPrincipal = 0;
    
    foreach ($unpaidSchedules as $schedule) {
        $paidRatio = $schedule->total_amount > 0 ? 
                    ($schedule->amount_paid / $schedule->total_amount) : 0;
        $unpaidPrincipal = $schedule->principal_amount * (1 - $paidRatio);
        $totalRemainingPrincipal += $unpaidPrincipal;
    }
    
    return max(0, $totalRemainingPrincipal - $principalReduction);
}

/**
 * Perform rescheduling with flexible logic
 */
private function performReschedulingFlexible($agreement, $newPrincipalBalance, $rescheduleOption, $applicationResult)
{
    // Clear future schedules
    $this->clearFutureSchedulesFlexible($agreement->id);
    
    // Get remaining duration and next due date
    $remainingMonths = $this->getRemainingMonthsFlexible($agreement);
    $nextDueDate = $this->getNextDueDateFlexible($agreement);
    
    // Validate interest rate
    $monthlyInterestDecimal = $agreement->interest_rate / 100;
    if ($monthlyInterestDecimal <= 0) {
        throw new \Exception('Invalid interest rate: ' . $agreement->interest_rate . '%');
    }
    
    if ($rescheduleOption === 'reduce_duration') {
        return $this->rescheduleDurationFlexible($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal);
    } else {
        return $this->reschedulePaymentFlexible($agreement, $newPrincipalBalance, $remainingMonths, $nextDueDate, $monthlyInterestDecimal);
    }
}

// Include other helper methods (clearFutureSchedulesFlexible, getNextDueDateFlexible, etc.) 
// from the previous flexible implementation...
    /**
     * Calculate monthly payment using PMT formula
     * PMT = P * [r(1+r)^n] / [(1+r)^n - 1]
     */
    private function calculatePMT($loanAmount, $monthlyRate, $termMonths)
    {
        if ($monthlyRate == 0) {
            return $loanAmount / $termMonths;
        }
        
        // PMT formula: PMT = P * [r(1+r)^n] / [(1+r)^n - 1]
        $factor = pow(1 + $monthlyRate, $termMonths);
        $pmt = $loanAmount * ($monthlyRate * $factor) / ($factor - 1);
        
        Log::info('PMT Calculation:', [
            'loan_amount' => $loanAmount,
            'monthly_rate' => $monthlyRate,
            'term_months' => $termMonths,
            'factor' => $factor,
            'pmt' => $pmt
        ]);
        
        return round($pmt, 2);
    }

    /**
     * Generate amortization schedule with dynamic tracking fee
     */
    private function generateAmortizationSchedule($agreement, $monthlyInterestDecimal)
    {
        $remainingPrincipal = $agreement->loan_amount; // This includes dynamic tracking fee
        $monthlyPayment = $agreement->monthly_payment;
        $firstDueDate = Carbon::parse($agreement->first_due_date);
        
        Log::info('Generating amortization schedule with dynamic tracking fee:', [
            'loan_amount' => $remainingPrincipal,
            'tracking_fee' => $agreement->tracking_fee,
            'monthly_payment' => $monthlyPayment,
            'monthly_interest_rate' => round($monthlyInterestDecimal * 100, 6) . '%',
            'duration' => $agreement->duration_months
        ]);
        
        for ($month = 1; $month <= $agreement->duration_months; $month++) {
            // Calculate interest on current remaining principal
            $monthlyInterest = $remainingPrincipal * $monthlyInterestDecimal;
            
            // Calculate principal payment (payment minus interest)
            $monthlyPrincipal = $monthlyPayment - $monthlyInterest;
            
            // For the last payment, adjust to pay off exactly
            if ($month == $agreement->duration_months) {
                $monthlyPrincipal = $remainingPrincipal;
                $actualPayment = $monthlyPrincipal + $monthlyInterest;
            } else {
                $actualPayment = $monthlyPayment;
            }
            
            // Calculate new remaining principal
            $newRemainingPrincipal = max(0, $remainingPrincipal - $monthlyPrincipal);
            
            // Log first 3 payments for verification
            if ($month <= 3) {
                Log::info("Payment {$month} Details (Dynamic Tracking Fee):", [
                    'Starting Balance' => round($remainingPrincipal, 2),
                    'Payment' => round($actualPayment, 2),
                    'Interest' => round($monthlyInterest, 2),
                    'Principal' => round($monthlyPrincipal, 2),
                    'Ending Balance' => round($newRemainingPrincipal, 2)
                ]);
            }
            
            // Create payment schedule record
            PaymentSchedule::create([
                'agreement_id' => $agreement->id,
                'installment_number' => $month,
                'due_date' => $firstDueDate->copy()->addMonths($month - 1),
                'principal_amount' => round($monthlyPrincipal, 2),
                'interest_amount' => round($monthlyInterest, 2),
                'total_amount' => round($actualPayment, 2),
                'balance_after' => round($newRemainingPrincipal, 2),
                'status' => 'pending',
                'amount_paid' => 0,
                'date_paid' => null,
                'days_overdue' => 0
            ]);
            
            // Update remaining principal for next iteration
            $remainingPrincipal = $newRemainingPrincipal;
        }
    }

    /**
     * Get calculation using dynamic tracking fee for AJAX requests
     */
    public function getCalculation(Request $request)
    {
        $vehiclePrice = $request->get('vehicle_price');
        $depositAmount = $request->get('deposit_amount');
        $trackingFee = $request->get('tracking_fee', 0); // NEW: Get tracking fee from request
        $duration = $request->get('duration');

        if (!$vehiclePrice || !$depositAmount || !$duration) {
            return response()->json(['error' => 'Missing required parameters']);
        }

        $depositPercentage = ($depositAmount / $vehiclePrice) * 100;
        $baseLoanAmount = $vehiclePrice - $depositAmount;
        $totalLoanAmount = $baseLoanAmount + $trackingFee; // Use dynamic tracking fee
        
        // Use dynamic interest rate based on deposit percentage
        $monthlyInterestRate = $this->getInterestRateByDeposit($depositPercentage);
        $monthlyInterestDecimal = $monthlyInterestRate / 100;
        $monthlyPayment = $this->calculatePMT($totalLoanAmount, $monthlyInterestDecimal, $duration);
        
        $totalPayments = $monthlyPayment * $duration;
        $totalInterest = $totalPayments - $totalLoanAmount;
        $totalAmount = $vehiclePrice + $trackingFee + $totalInterest;

        return response()->json([
            'base_loan_amount' => $baseLoanAmount,
            'tracking_fee' => $trackingFee, // Return dynamic tracking fee
            'total_loan_amount' => $totalLoanAmount,
            'interest_rate' => $monthlyInterestRate,
            'monthly_payment' => round($monthlyPayment, 2),
            'total_interest' => round($totalInterest, 2),
            'total_amount' => round($totalAmount, 2),
            'deposit_percentage' => round($depositPercentage, 2)
        ]);
    }

    /**
     * Get vehicle information
     */
    private function getVehicleInfo($type, $id)
    {
        try {
            if ($type === 'import') {
                $car = CarImport::find($id);
                if ($car) {
                    return [
                        'make' => $car->make ?? 'Unknown',
                        'model' => $car->model ?? 'Unknown',
                        'year' => $car->year ?? null,
                        'plate' => null
                    ];
                }
            } elseif ($type === 'customer') {
                $car = CustomerVehicle::find($id);
                if ($car) {
                    return [
                        'make' => $car->vehicle_make ?? 'Unknown',
                        'model' => $car->vehicle_model ?? 'Unknown',
                        'year' => $car->year ?? null,
                        'plate' => $car->number_plate ?? null
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Error fetching vehicle info:', [
                'type' => $type,
                'id' => $id,
                'error' => $e->getMessage()
            ]);
        }

        return [
            'make' => 'Unknown',
            'model' => 'Unknown',
            'year' => null,
            'plate' => null
        ];
    }

    // Keep all other existing methods unchanged...
    public function storePayment(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'agreement_id' => 'required|integer|exists:hire_purchase_agreements,id',
                'payment_amount' => 'required|numeric|min:1',
                'payment_date' => 'required|date',
                'payment_method' => 'required|string|in:cash,bank_transfer,mpesa,cheque,card',
                'payment_reference' => 'nullable|string|max:100',
                'payment_notes' => 'nullable|string'
            ]);
    
            // Start a database transaction
            \DB::beginTransaction();
    
            // Find the agreement with payment schedule
            $agreement = \App\Models\HirePurchaseAgreement::with('paymentSchedule')->findOrFail($request->agreement_id);
            
            // Check if agreement is completed
            if ($agreement->status === 'completed') {
                return response()->json([
                    'message' => 'Cannot add payment to a completed agreement.'
                ], 422);
            }
            
            // Calculate actual outstanding balance (same logic as Blade template)
            $totalScheduledAmount = $agreement->paymentSchedule ? $agreement->paymentSchedule->sum('total_amount') : 0;
            $totalPaidFromSchedule = $agreement->paymentSchedule ? $agreement->paymentSchedule->sum('amount_paid') : 0;
            $calculatedOutstanding = $totalScheduledAmount - $totalPaidFromSchedule;
            
            // Use the payment schedule calculation if it exists, otherwise use the agreement's outstanding balance
            $actualOutstanding = $totalScheduledAmount > 0 ? $calculatedOutstanding : $agreement->outstanding_balance;
            
            // Log the calculations for debugging
            \Log::info('Outstanding Balance Calculation:', [
                'agreement_id' => $agreement->id,
                'database_outstanding' => $agreement->outstanding_balance,
                'calculated_outstanding' => $calculatedOutstanding,
                'actual_outstanding_used' => $actualOutstanding,
                'payment_amount' => $request->payment_amount
            ]);
            
            // Check if payment amount exceeds outstanding balance (with small tolerance for precision)
            $tolerance = 0.01; // 1 cent tolerance
            if ($request->payment_amount > ($actualOutstanding + $tolerance)) {
                return response()->json([
                    'message' => 'Payment amount cannot exceed outstanding balance of KSh ' . number_format($actualOutstanding, 2)
                ], 422);
            }
    
            // Calculate payment number (next in sequence)
            $lastPayment = \DB::table('hire_purchase_payments')
                ->where('agreement_id', $agreement->id)
                ->max('payment_number');
            
            $paymentNumber = ($lastPayment ?? 0) + 1;
    
            // Calculate balances using the actual outstanding
            $balanceBefore = $actualOutstanding;
            $balanceAfter = $balanceBefore - $request->payment_amount;
    
            // Determine payment type based on amount and schedule
            $paymentType = 'regular';
            if ($request->payment_amount < $agreement->monthly_payment) {
                $paymentType = 'partial';
            } elseif ($balanceAfter <= 0) {
                $paymentType = 'final';
            }
    
            // Create payment record
            $paymentId = \DB::table('hire_purchase_payments')->insertGetId([
                'agreement_id' => $agreement->id,
                'amount' => $request->payment_amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->payment_reference,
                'notes' => $request->payment_notes,
                'payment_type' => $paymentType,
                'penalty_amount' => 0,
                'payment_number' => $paymentNumber,
                'recorded_by' => auth()->id() ?? 1,
                'recorded_at' => now(),
                'is_verified' => false,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'created_at' => now(),
                'updated_at' => now()
            ]);
    
            // Update Payment Schedule
            $this->updatePaymentSchedule($agreement->id, $request->payment_amount, $request->payment_date);
    
            // Update agreement - use the actual outstanding balance
            $newAmountPaid = $agreement->amount_paid + $request->payment_amount;
            $newOutstanding = $actualOutstanding - $request->payment_amount;
            
            // Calculate payment progress
            $totalAmount = $agreement->vehicle_price;
            $totalPaid = $newAmountPaid + $agreement->deposit_amount;
            $paymentProgress = ($totalAmount > 0) ? ($totalPaid / $totalAmount) * 100 : 0;
            
            // Update payments made count
            $paymentsMade = \DB::table('hire_purchase_payments')
                ->where('agreement_id', $agreement->id)
                ->count();
    
            // Determine new status
            $newStatus = $agreement->status;
            if ($newOutstanding <= 0) {
                $newStatus = 'completed';
            } elseif ($agreement->status === 'pending') {
                $newStatus = 'approved';
            }
    
            \DB::table('hire_purchase_agreements')
                ->where('id', $agreement->id)
                ->update([
                    'amount_paid' => $newAmountPaid,
                    'outstanding_balance' => max(0, $newOutstanding), // Ensure non-negative
                    'payment_progress' => $paymentProgress,
                    'payments_made' => $paymentsMade,
                    'last_payment_date' => $request->payment_date,
                    'status' => $newStatus,
                    'updated_at' => now()
                ]);
    
            // Commit the transaction
            \DB::commit();
    
            // Get the created payment for response
            $createdPayment = \DB::table('hire_purchase_payments')
                ->where('id', $paymentId)
                ->first();
    
            return response()->json([
                'message' => 'Payment recorded successfully!',
                'payment' => $createdPayment,
                'payment_number' => $paymentNumber,
                'new_balance' => max(0, $newOutstanding),
                'payment_progress' => round($paymentProgress, 1)
            ]);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollback();
            return response()->json([
                'message' => 'Please check your input.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \DB::rollback();
            
            // Log the actual error for debugging
            \Log::error('Payment Recording Error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Payment Schedule when payment is made
     */
    private function updatePaymentSchedule($agreementId, $paymentAmount, $paymentDate)
    {
        try {
            $remainingAmount = $paymentAmount;
            
            // Get unpaid installments in chronological order (overdue first, then pending)
            $paymentSchedules = \App\Models\PaymentSchedule::where('agreement_id', $agreementId)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->orderByRaw("
                    CASE 
                        WHEN status = 'overdue' THEN 1 
                        WHEN status = 'partial' THEN 2 
                        WHEN status = 'pending' THEN 3 
                    END
                ")
                ->orderBy('due_date', 'asc')
                ->get();
    
            if ($paymentSchedules->isEmpty()) {
                \Log::info('No payment schedules found to update for agreement: ' . $agreementId);
                return;
            }
    
            \Log::info('Updating payment schedule for agreement: ' . $agreementId . ' with amount: ' . $paymentAmount);
    
            foreach ($paymentSchedules as $schedule) {
                if ($remainingAmount <= 0) {
                    break;
                }
    
                // Calculate how much is still owed on this installment
                $currentPaid = $schedule->amount_paid ?? 0;
                $amountDue = $schedule->total_amount - $currentPaid;
                
                if ($amountDue <= 0) {
                    continue; // Skip if already fully paid
                }
    
                if ($remainingAmount >= $amountDue) {
                    // Fully pay this installment
                    $schedule->update([
                        'amount_paid' => $schedule->total_amount,
                        'status' => 'paid',
                        'date_paid' => $paymentDate,
                        'days_overdue' => 0 // Reset overdue days when paid
                    ]);
                    
                    $remainingAmount -= $amountDue;
                    
                    \Log::info('Fully paid installment ' . $schedule->installment_number . ' for agreement ' . $agreementId);
                    
                } else {
                    // Partially pay this installment
                    $newAmountPaid = $currentPaid + $remainingAmount;
                    $newStatus = 'partial';
                    
                    // If this was overdue, keep it as overdue partial payment
                    if ($schedule->status === 'overdue') {
                        $newStatus = 'overdue';
                    }
                    
                    $schedule->update([
                        'amount_paid' => $newAmountPaid,
                        'status' => $newStatus,
                        'date_paid' => $paymentDate
                    ]);
                    
                    \Log::info('Partially paid installment ' . $schedule->installment_number . ' for agreement ' . $agreementId . '. Amount: ' . $remainingAmount);
                    
                    $remainingAmount = 0;
                }
            }
    
            // If there's still remaining amount after all installments are paid,
            // it might be an overpayment or early payment
            if ($remainingAmount > 0) {
                \Log::info('Overpayment of ' . $remainingAmount . ' for agreement ' . $agreementId);
                // You could handle overpayments here - maybe apply to future installments
                // or store as credit
            }
    
            // Update overdue status for all installments
            $this->updateOverdueStatus($agreementId);
    
        } catch (\Exception $e) {
            \Log::error('Payment schedule update failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function updateOverdueStatus($agreementId)
    {
        try {
            $today = now()->toDateString();
            
            // Update overdue installments
            \App\Models\PaymentSchedule::where('agreement_id', $agreementId)
                ->where('due_date', '<', $today)
                ->where('status', 'pending')
                ->update([
                    'status' => 'overdue',
                    'days_overdue' => \DB::raw("DATEDIFF('$today', due_date)")
                ]);
    
            // Update days overdue for already overdue installments
            \App\Models\PaymentSchedule::where('agreement_id', $agreementId)
                ->where('status', 'overdue')
                ->update([
                    'days_overdue' => \DB::raw("DATEDIFF('$today', due_date)")
                ]);
    
        } catch (\Exception $e) {
            \Log::error('Overdue status update failed: ' . $e->getMessage());
            // Don't throw here as it's not critical to the payment process
        }
    }
    
    /**
     * Get payment schedule summary for an agreement
     */
    public function getPaymentScheduleSummary($agreementId)
    {
        return \App\Models\PaymentSchedule::where('agreement_id', $agreementId)
            ->selectRaw('
                status,
                COUNT(*) as count,
                SUM(total_amount) as total_amount,
                SUM(amount_paid) as amount_paid,
                SUM(total_amount - COALESCE(amount_paid, 0)) as amount_due
            ')
            ->groupBy('status')
            ->get();
    }

    /**
     * Verify a payment - Updated for your schema
     */
    public function verifyPayment(Request $request, $paymentId)
    {
        try {
            $payment = \DB::table('hire_purchase_payments')
                ->where('id', $paymentId)
                ->first();
            
            if (!$payment) {
                return response()->json([
                    'message' => 'Payment not found.'
                ], 404);
            }
            
            if ($payment->is_verified) {
                return response()->json([
                    'message' => 'Payment is already verified.'
                ], 422);
            }
            
            \DB::table('hire_purchase_payments')
                ->where('id', $paymentId)
                ->update([
                    'is_verified' => true,
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                    'updated_at' => now()
                ]);

            return response()->json([
                'message' => 'Payment verified successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Payment verification failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'An error occurred while verifying the payment.'
            ], 500);
        }
    }

    /**
     * Approve an agreement - Updated for your schema
     */
    public function approveAgreement(Request $request, $agreementId)
    {
        try {
            $agreement = \DB::table('hire_purchase_agreements')
                ->where('id', $agreementId)
                ->first();
            
            if (!$agreement) {
                return response()->json([
                    'message' => 'Agreement not found.'
                ], 404);
            }
            
            if ($agreement->status !== 'pending') {
                return response()->json([
                    'message' => 'Only pending agreements can be approved.'
                ], 422);
            }
            
            \DB::table('hire_purchase_agreements')
                ->where('id', $agreementId)
                ->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'updated_at' => now()
                ]);

            return response()->json([
                'message' => 'Agreement approved successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Agreement approval failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'An error occurred while approving the agreement.'
            ], 500);
        }
    }



    public function approve($id)
    {
        $agreement = HirePurchaseAgreement::findOrFail($id);
        
        if ($agreement->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Agreement is not in pending status']);
        }

        $agreement->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Agreement approved successfully']);
    }

    public function recordPayment(Request $request)
    {
        $validated = $request->validate([
            'agreement_id' => 'required|exists:hire_purchase_agreements,id',
            'payment_amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mpesa,cheque,card',
            'payment_reference' => 'nullable|string',
            'payment_notes' => 'nullable|string'
        ]);

        $agreement = HirePurchaseAgreement::findOrFail($validated['agreement_id']);

        if ($agreement->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Agreement is already completed']);
        }

        if ($validated['payment_amount'] > $agreement->outstanding_balance) {
            return response()->json(['success' => false, 'message' => 'Payment amount exceeds outstanding balance']);
        }

        DB::beginTransaction();
        try {
            $payment = $agreement->recordPayment(
                $validated['payment_amount'],
                $validated['payment_date'],
                $validated['payment_method'],
                $validated['payment_reference'],
                $validated['payment_notes']
            );

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment recorded successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Error recording payment: ' . $e->getMessage()]);
        }
    }

    public function export()
    {
        // Implementation for Excel export
        return response()->download('hire_purchase_export.xlsx');
    }

    public function paymentSchedule($id)
    {
        $agreement = HirePurchaseAgreement::with('paymentSchedule')->findOrFail($id);
        return view('hire-purchase.schedule', compact('agreement'));
    }

    public function printAgreement($id)
    {
        $agreement = HirePurchaseAgreement::findOrFail($id);
        return view('hire-purchase.print', compact('agreement'));
    }

    public function sendReminder($id)
    {
        $agreement = HirePurchaseAgreement::findOrFail($id);
        
        // Implementation for sending payment reminder
        // This could be SMS, email, or both
        
        return response()->json(['success' => true, 'message' => 'Reminder sent successfully']);
    }

    public function dashboard()
    {
        $stats = [
            'total_agreements' => HirePurchaseAgreement::count(),
            'active_agreements' => HirePurchaseAgreement::active()->count(),
            'overdue_agreements' => HirePurchaseAgreement::overdue()->count(),
            'total_portfolio' => HirePurchaseAgreement::sum('total_amount'),
            'outstanding_balance' => HirePurchaseAgreement::sum('outstanding_balance'),
            'payments_today' => HirePurchasePayment::today()->sum('amount'),
            'payments_this_month' => HirePurchasePayment::thisMonth()->sum('amount'),
        ];

        $recentPayments = HirePurchasePayment::with('agreement')
            ->latest()
            ->limit(10)
            ->get();

        $overdueAgreements = HirePurchaseAgreement::overdue()
            ->orderBy('overdue_days', 'desc')
            ->limit(10)
            ->get();

        return view('hire-purchase.dashboard', compact('stats', 'recentPayments', 'overdueAgreements'));
    }
    
    public function destroy(Request $request, $agreementId)
    {
        try {
            \Log::info('Attempting to delete agreement', ['id' => $agreementId]);
            
            // Check if agreement exists
            $agreement = \DB::table('hire_purchase_agreements')
                ->where('id', $agreementId)
                ->first();
            
            if (!$agreement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agreement not found.'
                ], 404);
            }
            
            // Simple delete without transaction first (for testing)
            $deleted = \DB::table('hire_purchase_agreements')
                ->where('id', $agreementId)
                ->delete();
            
            if ($deleted) {
                \Log::info('Agreement deleted successfully', ['id' => $agreementId]);
                return response()->json([
                    'success' => true,
                    'message' => 'Agreement deleted successfully!'
                ]);
            } else {
                throw new \Exception('No rows were deleted');
            }

        } catch (\Exception $e) {
            \Log::error('Delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}