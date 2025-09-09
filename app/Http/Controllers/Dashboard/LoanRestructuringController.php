<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HirePurchaseAgreement;
use App\Models\PaymentSchedule;
use App\Models\Penalty;
use App\Services\PenaltyService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class LoanRestructuringController extends Controller
{
    // Constants
    const DEFAULT_RESTRUCTURING_FEE_RATE = 3.0; // 3% default fee
    
    /**
     * CRITICAL: Get original monthly interest rate - SAME METHOD as HirePurchasesController
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
     * CRITICAL: PMT calculation - SAME METHOD as HirePurchasesController
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
     * CRITICAL: Generate new payment schedule with ORIGINAL interest rate
     */
    private function generateNewPaymentSchedule($agreementId, $principalAmount, $monthlyPayment, $duration, $monthlyInterestDecimal, $startDate, $restructuringType = null)
    {
        Log::info('=== GENERATING RESTRUCTURED SCHEDULE WITH ORIGINAL INTEREST ===', [
            'principal_amount' => $principalAmount,
            'monthly_payment' => $monthlyPayment,
            'duration' => $duration,
            'original_interest_decimal' => $monthlyInterestDecimal,
            'original_interest_percentage' => round($monthlyInterestDecimal * 100, 6) . '%'
        ]);
        
        // CRITICAL: Validate original interest rate
        if ($monthlyInterestDecimal <= 0) {
            throw new \Exception('CRITICAL: Original interest rate is zero! Cannot generate restructured schedule.');
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
                Log::info("RESTRUCTURED Payment {$month} Details (Original Interest):", [
                    'Starting Balance' => round($remainingPrincipal, 2),
                    'Interest Rate' => round($monthlyInterestDecimal * 100, 6) . '%',
                    'Interest Amount' => round($monthlyInterest, 2),
                    'Principal Amount' => round($monthlyPrincipal, 2),
                    'Total Payment' => round($actualPayment, 2),
                    'Ending Balance' => round($newRemainingPrincipal, 2)
                ]);
            }
            
            // Create payment schedule entry with ORIGINAL INTEREST
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
                'days_overdue' => 0,
                'schedule_type'      => 'restructured',      // ✅ mark as restructured
                'restructuring_type' => $restructuringType,  // ✅ use the parameter instead
            ]);
            
            $remainingPrincipal = $newRemainingPrincipal;
            
            if ($remainingPrincipal <= 0) {
                break;
            }
        }
        
        Log::info('✅ Restructured payment schedule generated successfully with ORIGINAL interest rate');
    }

    /**
     * Show restructuring options page
     */
    public function showRestructuringPage($agreementId)
    {
        try {
            $agreement = HirePurchaseAgreement::with([
                'paymentSchedule', 
                'penalties',
                'customerVehicle',
                 'penalties',
                'carImport'
            ])->findOrFail($agreementId);
            
            // Calculate current financial position
            $financialSummary = $this->calculateCurrentFinancialPosition($agreement);
            
            // Calculate remaining months for JavaScript
            $currentRemainingMonths = $this->getRemainingMonths($agreement);
            
            // Get ORIGINAL interest rate for display
            $originalMonthlyRate = (float) ($this->getOriginalMonthlyInterestRate($agreement) ?? 0);

            
            $test = Log::info('Loading restructuring page with original interest rate:', [
                'agreement_id' => $agreementId,
                'original_monthly_rate' => $originalMonthlyRate,
                'remaining_months' => $currentRemainingMonths,
                'financial_summary' => $financialSummary
            ]);
            
            
            return view('loan-restructuring.options', compact(
                'agreement', 
                'financialSummary', 
                'currentRemainingMonths',
                'originalMonthlyRate'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading restructuring page: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load restructuring options. Please try again.');
        }
    }
    
    
/**
 * Get restructuring options via API with ORIGINAL interest rate (ELIGIBILITY REMOVED)
 */
public function getRestructuringOptions(Request $request)
{
    try {
        $agreementId = $request->get('agreement_id');
        $restructuringType = $request->get('restructuring_type');
        $newDuration = $request->get('new_duration');
        
        if (!$agreementId) {
            return response()->json(['error' => 'Agreement ID is required'], 400);
        }
        
        $agreement = HirePurchaseAgreement::with(['paymentSchedule', 'penalties'])->findOrFail($agreementId);
        
        // REMOVED: Eligibility validation
        /*
        $eligibility = $this->validateRestructuringEligibility($agreement);
        if (!$eligibility['eligible']) {
            return response()->json([
                'error' => 'Not eligible for restructuring',
                'reasons' => $eligibility['errors']
            ], 422);
        }
        */
        
        // Calculate current financial position
        $financialPosition = $this->calculateCurrentFinancialPosition($agreement);
        
        // Calculate restructuring fee
        $restructuringFee = $this->calculateRestructuringFee($financialPosition['total_outstanding']);
        $newLoanAmount = $financialPosition['total_outstanding'] + $restructuringFee;
        
        // Get ORIGINAL monthly interest rate - CRITICAL!
        $originalMonthlyRate = $this->getOriginalMonthlyInterestRate($agreement);
        $monthlyInterestDecimal = $originalMonthlyRate / 100;
        
        Log::info('Restructuring options calculation with ORIGINAL rate (NO ELIGIBILITY CHECK):', [
            'agreement_id' => $agreementId,
            'original_monthly_rate' => $originalMonthlyRate,
            'monthly_interest_decimal' => $monthlyInterestDecimal,
            'new_loan_amount' => $newLoanAmount,
            'restructuring_fee' => $restructuringFee
        ]);
        
        // Calculate remaining months for baseline
        $remainingMonths = $this->getRemainingMonths($agreement);
        
        // Calculate options with ORIGINAL interest rate
        $options = [];
        
        if (!$restructuringType || $restructuringType === 'reduce_duration') {
            $options['reduce_duration'] = $this->calculateReduceDurationOption(
                $newLoanAmount, 
                $monthlyInterestDecimal,  // ORIGINAL RATE!
                $remainingMonths,
                $agreement->monthly_payment,
                $newDuration 
            );
        }
        
        if (!$restructuringType || $restructuringType === 'increase_duration') {
            $options['increase_duration'] = $this->calculateIncreaseDurationOption(
                $newLoanAmount, 
                $monthlyInterestDecimal,  // ORIGINAL RATE!
                $remainingMonths,
                $agreement->monthly_payment,
                $newDuration
            );
        }
        
        return response()->json([
            'success' => true,
            'financial_position' => $financialPosition,
            'restructuring_fee' => $restructuringFee,
            'restructuring_fee_rate' => $this->getRestructuringFeeRate(),
            'new_loan_amount' => $newLoanAmount,
            'original_monthly_rate' => $originalMonthlyRate,  // ORIGINAL RATE!
            'remaining_months' => $remainingMonths,
            'options' => $options,
            'eligibility_check' => 'DISABLED' // Indicates eligibility check is bypassed
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error calculating restructuring options: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to calculate restructuring options: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Process restructuring with ORIGINAL interest rate
     */
    public function processRestructuring(Request $request)
{
    try {
        $validated = $request->validate([
            'agreement_id' => 'required|integer|exists:hire_purchase_agreements,id',
            'restructuring_type' => 'nullable|string|in:reduce_duration,increase_duration',
            'new_duration' => 'nullable|integer|min:1|max:120',
            'restructuring_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'admin_payment_duration' => 'nullable|integer|min:1|max:120',
            'admin_fee_rate' => 'nullable|numeric|min:0|max:10',
            'selected_fee_rate' => 'nullable|numeric|min:0|max:10',
            'selected_duration' => 'nullable|integer|min:1|max:120'
        ]);
        
        DB::beginTransaction();
        
        $agreement = HirePurchaseAgreement::with(['paymentSchedule', 'penalties'])->findOrFail($validated['agreement_id']);
        
        // Get ORIGINAL interest rate before any modifications
        $originalMonthlyRate = $this->getOriginalMonthlyInterestRate($agreement);
        $monthlyInterestDecimal = $originalMonthlyRate / 100;
        
        Log::info('Processing restructuring with ORIGINAL interest rate (NO ELIGIBILITY CHECK):', [
            'agreement_id' => $agreement->id,
            'original_monthly_rate' => $originalMonthlyRate,
            'restructuring_type' => $validated['restructuring_type']
        ]);
        
        // REMOVED: Eligibility validation
        /*
        $eligibility = $this->validateRestructuringEligibility($agreement);
        if (!$eligibility['eligible']) {
            throw new \Exception('Agreement is not eligible for restructuring: ' . implode(', ', $eligibility['errors']));
        }
        */
        
        // Calculate restructuring with individual fee rates
        $financialPosition = $this->calculateCurrentFinancialPosition($agreement);
        
        $actualFeeRate = $validated['selected_fee_rate'] ?? 
                        $validated['admin_fee_rate'] ?? 
                        $this->getRestructuringFeeRate();
        
        $restructuringFee = $this->calculateRestructuringFee($financialPosition['total_outstanding'], $actualFeeRate);
        $newLoanAmount = $financialPosition['total_outstanding'] + $restructuringFee;
        
        // Store original terms for history
        $originalTerms = $this->captureOriginalTerms($agreement);
        
        // Use selected duration if provided
        $customDuration = $validated['selected_duration'] ?? 
                         $validated['admin_payment_duration'] ?? 
                         $validated['new_duration'];
        
        $modifiedValidated = $validated;
        if ($customDuration) {
            $modifiedValidated['new_duration'] = $customDuration;
        }
        
        // Perform restructuring with ORIGINAL interest rate
        if ($validated['restructuring_type'] === 'reduce_duration') {
            $result = $this->performReduceDurationRestructuring(
                $agreement, 
                $newLoanAmount, 
                $monthlyInterestDecimal,  // ORIGINAL RATE!
                $modifiedValidated, 
                $actualFeeRate
            );
        } else {
            $result = $this->performIncreaseDurationRestructuring(
                $agreement, 
                $newLoanAmount, 
                $monthlyInterestDecimal,  // ORIGINAL RATE!
                $modifiedValidated, 
                $actualFeeRate
            );
        }
        
        // Update agreement with new terms
        $this->updateAgreementAfterRestructuring($agreement, $result, $restructuringFee);
        
        // Create history record
        $historyId = $this->createRestructuringHistory(
            $agreement,
            $validated,
            $originalTerms,
            $result,
            $restructuringFee,
            $financialPosition,
            $actualFeeRate,
            $customDuration,
            $originalMonthlyRate  // Pass original rate to history
        );
        
        DB::commit();
        
        Log::info('✅ Loan restructuring completed with ORIGINAL interest rate (NO ELIGIBILITY CHECK)', [
            'agreement_id' => $agreement->id,
            'original_interest_rate' => $originalMonthlyRate,
            'restructuring_type' => $validated['restructuring_type'],
            'new_payment' => $result['new_payment'],
            'new_duration' => $result['new_duration'],
            'history_id' => $historyId
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Loan restructured successfully with original interest rate (eligibility bypassed)!',
            'restructuring_details' => $result,
            'original_interest_rate' => $originalMonthlyRate,
            'restructuring_fee' => $restructuringFee,
            'fee_rate_used' => $actualFeeRate,
            'new_loan_amount' => $newLoanAmount,
            'history_id' => $historyId,
            'eligibility_check' => 'BYPASSED'
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('❌ Loan restructuring failed: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

    /**
     * UPDATED: Reduce duration restructuring with ORIGINAL interest rate
     */
    private function performReduceDurationRestructuring($agreement, $newLoanAmount, $monthlyInterestDecimal, $validated, $actualFeeRate)
    {
        $currentRemainingMonths = $this->getRemainingMonths($agreement);
        
        Log::info('=== REDUCE DURATION WITH ORIGINAL INTEREST ===', [
            'new_loan_amount' => $newLoanAmount,
            'current_monthly_payment' => $agreement->monthly_payment,
            'original_interest_decimal' => $monthlyInterestDecimal,
            'original_remaining_months' => $currentRemainingMonths
        ]);
        
        // Use custom duration or calculate optimal duration
        $customDuration = $validated['new_duration'] ?? null;
        
        if ($customDuration) {
            // Use custom duration, calculate new payment
            $newDuration = $customDuration;
            $newPayment = $this->calculatePMT($newLoanAmount, $monthlyInterestDecimal, $newDuration);
        } else {
            // Keep current payment, calculate how much duration can be reduced
            $currentMonthlyPayment = $agreement->monthly_payment;
            
            // Calculate new duration: n = -ln(1 - (P*r)/PMT) / ln(1+r)
            if ($newLoanAmount <= 0) {
                $newDuration = 0;
            } else {
                $factor = ($newLoanAmount * $monthlyInterestDecimal) / $currentMonthlyPayment;
                if ($factor >= 1) {
                    $newDuration = $currentRemainingMonths;
                } else {
                    $newDuration = ceil(-log(1 - $factor) / log(1 + $monthlyInterestDecimal));
                }
            }
            
            $newDuration = max(1, $newDuration);
            $newPayment = $currentMonthlyPayment;
        }
        
        $durationReduction = max(0, $currentRemainingMonths - $newDuration);
        $paymentIncrease = max(0, $newPayment - $agreement->monthly_payment);
        
        // Clear existing unpaid schedules
        $this->clearUnpaidSchedules($agreement->id);
        
        // Generate new schedule with ORIGINAL interest rate
        if ($newDuration > 0) {
            $this->generateNewPaymentSchedule(
                $agreement->id,
                $newLoanAmount,
                $newPayment,
                $newDuration,
                $monthlyInterestDecimal,  // ORIGINAL RATE!
                $this->getNextDueDate($agreement),
                'reduce_duration'
            );
        }
        
        return [
            'type' => 'reduce_duration',
            'current_duration' => $currentRemainingMonths,
            'new_duration' => $newDuration,
            'duration_reduction' => $durationReduction,
            'current_payment' => $agreement->monthly_payment,
            'new_payment' => $newPayment,
            'payment_increase' => $paymentIncrease,
            'fee_rate_used' => $actualFeeRate,
            'original_interest_rate' => round($monthlyInterestDecimal * 100, 6),
            'description' => $customDuration ? 
                "Custom duration set to {$newDuration} months with payment of KSh " . number_format($newPayment, 2) :
                "Duration reduced by {$durationReduction} months using original interest rate"
        ];
    }

    /**
     * UPDATED: Increase duration restructuring with ORIGINAL interest rate
     */
    private function performIncreaseDurationRestructuring($agreement, $newLoanAmount, $monthlyInterestDecimal, $validated, $actualFeeRate)
    {
        $currentRemainingMonths = $this->getRemainingMonths($agreement);
        
        Log::info('=== INCREASE DURATION WITH ORIGINAL INTEREST ===', [
            'new_loan_amount' => $newLoanAmount,
            'remaining_months' => $currentRemainingMonths,
            'original_interest_decimal' => $monthlyInterestDecimal
        ]);
        
        // Use custom duration or calculate extended duration
        $customDuration = $validated['new_duration'] ?? null;
        
        if ($customDuration) {
            $newDuration = $customDuration;
        } else {
            $newDuration = round($currentRemainingMonths * 1.5); // 50% increase as default
            $newDuration = min($newDuration, 72); // Cap at 72 months total
        }
        
        // Calculate new payment with ORIGINAL interest rate
        $newPayment = $this->calculatePMT($newLoanAmount, $monthlyInterestDecimal, $newDuration);
        
        $durationIncrease = max(0, $newDuration - $currentRemainingMonths);
        $paymentReduction = max(0, $agreement->monthly_payment - $newPayment);
        
        // Clear existing unpaid schedules
        $this->clearUnpaidSchedules($agreement->id);
        
        // Generate new schedule with ORIGINAL interest rate
        if ($newDuration > 0) {
            $this->generateNewPaymentSchedule(
                $agreement->id,
                $newLoanAmount,
                $newPayment,
                $newDuration,
                $monthlyInterestDecimal,  // ORIGINAL RATE!
                $this->getNextDueDate($agreement),
                'increase_duration'
            );
        }
        
        return [
            'type' => 'increase_duration',
            'current_duration' => $currentRemainingMonths,
            'new_duration' => $newDuration,
            'duration_increase' => $durationIncrease,
            'current_payment' => $agreement->monthly_payment,
            'new_payment' => $newPayment,
            'payment_reduction' => $paymentReduction,
            'fee_rate_used' => $actualFeeRate,
            'original_interest_rate' => round($monthlyInterestDecimal * 100, 6),
            'description' => $customDuration ?
                "Custom duration set to {$newDuration} months with payment of KSh " . number_format($newPayment, 2) :
                "Duration increased by {$durationIncrease} months using original interest rate"
        ];
    }

    // Helper methods (same as before but ensuring original interest rate usage)
    
    private function validateRestructuringEligibility($agreement)
    {
        $eligibility = [
            'eligible' => true,
            'errors' => [],
            'warnings' => []
        ];
        
        if ($agreement->status === 'completed') {
            $eligibility['eligible'] = false;
            $eligibility['errors'][] = 'Cannot restructure a completed loan';
        }
        
        if ($agreement->status === 'pending') {
            $eligibility['eligible'] = false;
            $eligibility['errors'][] = 'Loan must be approved before restructuring';
        }
        
         // REMOVED: Payment requirement check
        /*
        $paymentsMade = $agreement->paymentSchedule()->where('status', 'paid')->count();
        if ($paymentsMade === 0) {
            $eligibility['eligible'] = false;
            $eligibility['errors'][] = 'At least one payment must be made before restructuring';
        }
        */
        
        $recentRestructuring = DB::table('loan_rescheduling_history')
            ->where('agreement_id', $agreement->id)
            ->where('status', 'active')
            ->where('rescheduling_date', '>', now()->subMonths(6))
            ->exists();
            
        if ($recentRestructuring) {
            $eligibility['warnings'][] = 'Recent restructuring found within the last 6 months';
        }
        
        $restructuringCount = DB::table('loan_rescheduling_history')
            ->where('agreement_id', $agreement->id)
            ->where('status', 'active')
            ->count();
            
        if ($restructuringCount >= 3) {
            $eligibility['eligible'] = false;
            $eligibility['errors'][] = 'Maximum number of restructurings (3) already reached';
        }
        
        return $eligibility;
    }

    private function calculateCurrentFinancialPosition($agreement)
    {
        $today = Carbon::today();
        $duePayments = 0;
        $overduePayments = $agreement->paymentSchedule()
            ->where(function($query) use ($today) {
                $query->where('status', 'overdue')
                      ->orWhere(function($q) use ($today) {
                          $q->where('status', 'partial')
                            ->where('due_date', '<=', $today);
                      })
                      ->orWhere(function($q) use ($today) {
                          $q->where('status', 'pending')
                            ->where('due_date', '<=', $today);
                      });
            })
            ->get();
            
        foreach ($overduePayments as $payment) {
            $duePayments += ($payment->total_amount - ($payment->amount_paid ?? 0));
        }
        
        $principalBalance = 0;
        $unpaidSchedules = $agreement->paymentSchedule()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->get();
            
        foreach ($unpaidSchedules as $schedule) {
            $paidRatio = $schedule->total_amount > 0 ? 
                        ($schedule->amount_paid / $schedule->total_amount) : 0;
            $unpaidPrincipal = $schedule->principal_amount * (1 - $paidRatio);
            $principalBalance += $unpaidPrincipal;
        }
        
        $penaltyService = app(PenaltyService::class);
        $penaltyService->calculatePenaltiesForAgreement('hire_purchase', $agreement->id);
        $penaltySummary = $penaltyService->getPenaltySummary('hire_purchase', $agreement->id);
        $totalPenalties = $penaltySummary['total_pending'] ?? 0;
        
        $totalOutstanding = $duePayments + $principalBalance + $totalPenalties;
        
        return [
            'due_payments' => $duePayments,
            'principal_balance' => $principalBalance,
            'total_penalties' => $totalPenalties,
            'total_outstanding' => $totalOutstanding,
            'breakdown' => [
                'overdue_count' => $overduePayments->count(),
                'unpaid_count' => $unpaidSchedules->count(),
                'penalty_count' => $penaltySummary['pending_count'] ?? 0
            ]
        ];
    }
    
    private function calculateRestructuringFee($totalOutstanding, $feeRate = null)
    {
        $actualFeeRate = $feeRate ?? $this->getRestructuringFeeRate();
        return ($totalOutstanding * $actualFeeRate) / 100;
    }
    
    private function getRestructuringFeeRate()
    {
        return config('loan.restructuring_fee_rate', self::DEFAULT_RESTRUCTURING_FEE_RATE);
    }
    
    private function getRemainingMonths($agreement)
    {
        $unpaidCount = PaymentSchedule::where('agreement_id', $agreement->id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->count();
        
        return max(1, $unpaidCount);
    }
    
    private function getNextDueDate($agreement)
    {
        $nextSchedule = PaymentSchedule::where('agreement_id', $agreement->id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->first();
        
        return $nextSchedule ? $nextSchedule->due_date : now()->addMonth();
    }
    
     private function clearUnpaidSchedules($agreementId, $restructuringType = null)
        {
            $deletedCount = PaymentSchedule::where('agreement_id', $agreementId)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->delete();
                
            return $deletedCount;
        }
    
    private function captureOriginalTerms($agreement)
    {
        return [
            'duration_months' => $this->getRemainingMonths($agreement),
            'monthly_payment' => $agreement->monthly_payment,
            'completion_date' => $agreement->expected_completion_date,
            'outstanding_balance' => $agreement->outstanding_balance,
            'original_interest_rate' => $this->getOriginalMonthlyInterestRate($agreement)
        ];
    }
    
    private function updateAgreementAfterRestructuring($agreement, $result, $restructuringFee)
    {
        $updateData = [
            'monthly_payment' => $result['new_payment'],
            'loan_amount' => $agreement->loan_amount + $restructuringFee,
            'outstanding_balance' => $agreement->outstanding_balance + $restructuringFee,
            'expected_completion_date' => now()->addMonths($result['new_duration']),
            'last_payment_date' => now(),
            'updated_at' => now()
        ];
        
        DB::table('hire_purchase_agreements')
            ->where('id', $agreement->id)
            ->update($updateData);
    }

    /**
     * UPDATED: Create restructuring history with original interest rate tracking
     */
    private function createRestructuringHistory($agreement, $validated, $originalTerms, $result, $restructuringFee, $financialPosition, $actualFeeRate, $customDuration = null, $originalMonthlyRate = null)
    {
        // Ensure we have the original interest rate
        if (!$originalMonthlyRate) {
            $originalMonthlyRate = $this->getOriginalMonthlyInterestRate($agreement);
        }
        
        $historyData = [
            'agreement_id' => $agreement->id,
            'payment_id' => null,
            'reschedule_type' => $validated['restructuring_type'],
            'lump_sum_amount' => 0,
            'outstanding_before' => $originalTerms['outstanding_balance'],
            'outstanding_after' => $agreement->outstanding_balance + $restructuringFee,
            
            // Previous terms
            'previous_duration_months' => $originalTerms['duration_months'],
            'previous_monthly_payment' => $originalTerms['monthly_payment'],
            'previous_completion_date' => $originalTerms['completion_date'] ? 
                Carbon::parse($originalTerms['completion_date'])->format('Y-m-d') : null,
            
            // New terms
            'new_duration_months' => $result['new_duration'],
            'new_monthly_payment' => $result['new_payment'],
            'new_completion_date' => now()->addMonths($result['new_duration'])->format('Y-m-d'),
            
            // Changes
            'duration_change_months' => $validated['restructuring_type'] === 'reduce_duration' 
                ? -($result['duration_reduction'] ?? 0)
                : ($result['duration_increase'] ?? 0),
            'payment_change_amount' => $validated['restructuring_type'] === 'reduce_duration'
                ? ($result['payment_increase'] ?? 0)
                : -($result['payment_reduction'] ?? 0),
            'total_interest_savings' => $result['total_interest_saved'] ?? 0,
            
            'rescheduling_date' => $validated['restructuring_date'],
            'processed_by' => auth()->id() ?? 1,
            'notes' => ($validated['notes'] ?? '') . " (Restructured with original interest rate: {$originalMonthlyRate}%, Individual fee rate: {$actualFeeRate}%)",
            'status' => 'active',
            
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Add restructuring-specific fields if columns exist
        if (Schema::hasColumn('loan_rescheduling_history', 'restructuring_fee')) {
            $historyData['restructuring_fee'] = $restructuringFee;
        }
        
        if (Schema::hasColumn('loan_rescheduling_history', 'restructuring_fee_rate')) {
            $historyData['restructuring_fee_rate'] = $actualFeeRate;
        }
        
        if (Schema::hasColumn('loan_rescheduling_history', 'operation_type')) {
            $historyData['operation_type'] = 'loan_restructuring';
        }
        
        if (Schema::hasColumn('loan_rescheduling_history', 'additional_metadata')) {
            $historyData['additional_metadata'] = json_encode([
                'original_interest_rate' => $originalMonthlyRate, // CRITICAL: Track original rate
                'breakdown_details' => $financialPosition['breakdown'],
                'restructuring_reason' => $validated['notes'] ?? 'Customer requested restructuring',
                'fee_calculation' => [
                    'base_amount' => $financialPosition['total_outstanding'],
                    'fee_rate' => $actualFeeRate,
                    'fee_amount' => $restructuringFee
                ],
                'individual_settings' => [
                    'selected_fee_rate' => $validated['selected_fee_rate'] ?? null,
                    'selected_duration' => $validated['selected_duration'] ?? null,
                    'admin_fee_rate' => $validated['admin_fee_rate'] ?? null,
                    'admin_duration' => $validated['admin_payment_duration'] ?? null,
                    'final_fee_rate_used' => $actualFeeRate,
                    'final_duration_used' => $customDuration,
                    'settings_source' => $this->determineSettingsSource($validated)
                ],
                'interest_rate_validation' => [
                    'original_monthly_rate' => $originalMonthlyRate,
                    'source' => 'getOriginalMonthlyInterestRate_method',
                    'confirmed_usage' => 'yes',
                    'schedule_generated_with_original_rate' => true
                ]
            ]);
        }
        
        return DB::table('loan_rescheduling_history')->insertGetId($historyData);
    }
    
    /**
     * Determine the source of settings for tracking purposes
     */
    private function determineSettingsSource($validated)
    {
        $sources = [];
        
        if ($validated['selected_fee_rate']) {
            $sources[] = 'individual_fee_rate';
        } elseif ($validated['admin_fee_rate']) {
            $sources[] = 'admin_fee_rate';
        } else {
            $sources[] = 'default_fee_rate';
        }
        
        if ($validated['selected_duration']) {
            $sources[] = 'individual_duration';
        } elseif ($validated['admin_payment_duration']) {
            $sources[] = 'admin_duration';
        } elseif ($validated['new_duration']) {
            $sources[] = 'user_duration';
        } else {
            $sources[] = 'auto_calculated_duration';
        }
        
        return implode(',', $sources);
    }
    
    /**
     * Calculate reduce duration option with ORIGINAL interest rate
     */
    private function calculateReduceDurationOption($newLoanAmount, $monthlyInterestDecimal, $currentRemainingMonths, $currentPayment, $customDuration = null)
    {
        Log::info('Calculating reduce duration option with ORIGINAL interest rate:', [
            'new_loan_amount' => $newLoanAmount,
            'monthly_interest_decimal' => $monthlyInterestDecimal,
            'current_remaining_months' => $currentRemainingMonths,
            'current_payment' => $currentPayment
        ]);
        
        if ($customDuration) {
            $targetDuration = $customDuration;
            $newMonthlyPayment = $this->calculatePMT($newLoanAmount, $monthlyInterestDecimal, $targetDuration);
        } else {
            // For reduce duration, we increase monthly payment to finish faster
            $targetDuration = max(1, round($currentRemainingMonths * 0.75)); // 25% reduction as example
            
            // Calculate new payment using PMT formula with ORIGINAL rate
            $newMonthlyPayment = $this->calculatePMT($newLoanAmount, $monthlyInterestDecimal, $targetDuration);
            
            // Alternative: Calculate minimum duration with current payment + some increase
            $minPaymentIncrease = $currentPayment * 0.2; // 20% increase minimum
            $alternativePayment = $currentPayment + $minPaymentIncrease;
            
            if ($monthlyInterestDecimal > 0) {
                $factor = ($newLoanAmount * $monthlyInterestDecimal) / $alternativePayment;
                if ($factor < 1) {
                    $alternativeDuration = ceil(-log(1 - $factor) / log(1 + $monthlyInterestDecimal));
                } else {
                    $alternativeDuration = $targetDuration;
                }
            } else {
                $alternativeDuration = ceil($newLoanAmount / $alternativePayment);
            }
            
            // Use the option that gives reasonable payment increase
            if ($newMonthlyPayment - $currentPayment > $currentPayment * 0.5) { // If increase is more than 50%
                $targetDuration = $alternativeDuration;
                $newMonthlyPayment = $alternativePayment;
            }
        }
        
        $paymentIncrease = $newMonthlyPayment - $currentPayment;
        $durationReduction = max(0, $currentRemainingMonths - $targetDuration);
        
        return [
            'type' => 'reduce_duration',
            'current_duration' => $currentRemainingMonths,
            'new_duration' => $targetDuration,
            'duration_reduction' => $durationReduction,
            'current_payment' => $currentPayment,
            'new_payment' => $newMonthlyPayment,
            'payment_increase' => $paymentIncrease,
            'total_interest_saved' => $this->calculateInterestSaved($currentPayment, $currentRemainingMonths, $newMonthlyPayment, $targetDuration),
            'original_interest_rate' => round($monthlyInterestDecimal * 100, 6),
            'description' => "Increase monthly payment to KSh " . number_format($newMonthlyPayment, 2) . 
                           " and finish {$durationReduction} months earlier using original interest rate"
        ];
    }
    
    /**
     * Calculate increase duration option with ORIGINAL interest rate
     */
    private function calculateIncreaseDurationOption($newLoanAmount, $monthlyInterestDecimal, $currentRemainingMonths, $currentPayment, $targetDuration = null)
    {
        Log::info('Calculating increase duration option with ORIGINAL interest rate:', [
            'new_loan_amount' => $newLoanAmount,
            'monthly_interest_decimal' => $monthlyInterestDecimal,
            'current_remaining_months' => $currentRemainingMonths,
            'current_payment' => $currentPayment,
            'target_duration' => $targetDuration
        ]);
        
        // For increase duration, we extend the loan to reduce monthly payment
        if (!$targetDuration) {
            $targetDuration = round($currentRemainingMonths * 1.5); // 50% increase as default
            $targetDuration = min($targetDuration, 72); // Cap at 72 months total
        }
        
        // Calculate new payment using PMT formula with ORIGINAL rate
        $newMonthlyPayment = $this->calculatePMT($newLoanAmount, $monthlyInterestDecimal, $targetDuration);
        
        // Ensure the new payment is actually lower
        if ($newMonthlyPayment >= $currentPayment) {
            // Try with longer duration
            $targetDuration = min($targetDuration + 12, 84); // Add 12 months, cap at 84
            $newMonthlyPayment = $this->calculatePMT($newLoanAmount, $monthlyInterestDecimal, $targetDuration);
        }
        
        $paymentReduction = max(0, $currentPayment - $newMonthlyPayment);
        $durationIncrease = $targetDuration - $currentRemainingMonths;
        
        return [
            'type' => 'increase_duration',
            'current_duration' => $currentRemainingMonths,
            'new_duration' => $targetDuration,
            'duration_increase' => max(0, $durationIncrease),
            'current_payment' => $currentPayment,
            'new_payment' => $newMonthlyPayment,
            'payment_reduction' => $paymentReduction,
            'additional_interest' => $this->calculateAdditionalInterest($currentPayment, $currentRemainingMonths, $newMonthlyPayment, $targetDuration),
            'original_interest_rate' => round($monthlyInterestDecimal * 100, 6),
            'description' => "Reduce monthly payment to KSh " . number_format($newMonthlyPayment, 2) . 
                           " and extend loan by {$durationIncrease} months using original interest rate"
        ];
    }
    
    private function calculateInterestSaved($oldPayment, $oldDuration, $newPayment, $newDuration)
    {
        $oldTotal = $oldPayment * $oldDuration;
        $newTotal = $newPayment * $newDuration;
        return max(0, $oldTotal - $newTotal);
    }
    
    private function calculateAdditionalInterest($oldPayment, $oldDuration, $newPayment, $newDuration)
    {
        $oldTotal = $oldPayment * $oldDuration;
        $newTotal = $newPayment * $newDuration;
        return max(0, $newTotal - $oldTotal);
    }
    
    /**
     * API endpoints for loan restructuring
     */
    
    /**
     * Check eligibility (API endpoint)
     */
    public function checkEligibility($agreementId)
    {
        try {
            $agreement = HirePurchaseAgreement::findOrFail($agreementId);
            $eligibility = $this->validateRestructuringEligibility($agreement);
            
            return response()->json([
                'success' => true,
                'eligibility' => $eligibility,
                'original_interest_rate' => $this->getOriginalMonthlyInterestRate($agreement)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get financial summary with original interest rate (API endpoint)
     */
    public function getFinancialSummary($agreementId)
    {
        try {
            $agreement = HirePurchaseAgreement::with(['paymentSchedule', 'penalties'])->findOrFail($agreementId);
            $financialSummary = $this->calculateCurrentFinancialPosition($agreement);
            $originalMonthlyRate = $this->getOriginalMonthlyInterestRate($agreement);
            
            return response()->json([
                'success' => true,
                'financial_summary' => $financialSummary,
                'original_interest_rate' => $originalMonthlyRate,
                'restructuring_fee' => $this->calculateRestructuringFee($financialSummary['total_outstanding']),
                'restructuring_fee_rate' => $this->getRestructuringFeeRate()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get restructuring history for an agreement
     */
    public function getRestructuringHistory($agreementId)
    {
        try {
            $history = DB::table('loan_rescheduling_history')
                ->where('agreement_id', $agreementId)
                ->where('status', 'active')
                ->whereNotNull('restructuring_fee') // Only restructuring records (not lump sum)
                ->orderBy('rescheduling_date', 'desc')
                ->get();
                
            // Parse additional_metadata to show original interest rates used
            $history = $history->map(function($record) {
                if ($record->additional_metadata) {
                    $metadata = json_decode($record->additional_metadata, true);
                    $record->original_interest_rate_used = $metadata['original_interest_rate'] ?? null;
                    $record->interest_rate_confirmed = $metadata['interest_rate_validation']['confirmed_usage'] ?? null;
                }
                return $record;
            });
                
            return response()->json([
                'success' => true,
                'history' => $history,
                'count' => $history->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export restructuring analytics with original interest rate tracking
     */
    public function exportRestructuringAnalytics(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->subMonths(6)->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());
            
            $analytics = DB::table('loan_rescheduling_history as lrh')
                ->join('hire_purchase_agreements as hpa', 'lrh.agreement_id', '=', 'hpa.id')
                ->whereBetween('lrh.rescheduling_date', [$startDate, $endDate])
                ->whereNotNull('lrh.restructuring_fee') // Only restructuring records
                ->selectRaw('
                    COUNT(*) as total_restructurings,
                    SUM(lrh.restructuring_fee) as total_fees_collected,
                    AVG(lrh.restructuring_fee) as avg_restructuring_fee,
                    SUM(CASE WHEN lrh.reschedule_type = "reduce_duration" THEN 1 ELSE 0 END) as reduce_duration_count,
                    SUM(CASE WHEN lrh.reschedule_type = "increase_duration" THEN 1 ELSE 0 END) as increase_duration_count,
                    AVG(ABS(lrh.duration_change_months)) as avg_duration_change,
                    AVG(ABS(lrh.payment_change_amount)) as avg_payment_change
                ')
                ->first();
                
            $detailedData = DB::table('loan_rescheduling_history as lrh')
                ->join('hire_purchase_agreements as hpa', 'lrh.agreement_id', '=', 'hpa.id')
                ->whereBetween('lrh.rescheduling_date', [$startDate, $endDate])
                ->whereNotNull('lrh.restructuring_fee')
                ->select([
                    'hpa.client_name',
                    'hpa.national_id',
                    'hpa.vehicle_make',
                    'hpa.vehicle_model',
                    'hpa.interest_rate as stored_interest_rate',
                    'hpa.monthly_interest_rate as stored_monthly_rate',
                    'lrh.reschedule_type',
                    'lrh.restructuring_fee',
                    'lrh.restructuring_fee_rate',
                    'lrh.outstanding_before',
                    'lrh.outstanding_after',
                    'lrh.previous_duration_months',
                    'lrh.new_duration_months',
                    'lrh.previous_monthly_payment',
                    'lrh.new_monthly_payment',
                    'lrh.duration_change_months',
                    'lrh.payment_change_amount',
                    'lrh.rescheduling_date',
                    'lrh.additional_metadata'
                ])
                ->orderBy('lrh.rescheduling_date', 'desc')
                ->get();
                
            return response()->json([
                'success' => true,
                'analytics' => $analytics,
                'detailed_data' => $detailedData,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'note' => 'All restructurings use the original interest rate from the agreement'
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting restructuring analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update database schema for restructuring with original interest rate tracking
     */
    public function addRestructuringColumns()
    {
        try {
            $added = [];
            
            if (!Schema::hasColumn('loan_rescheduling_history', 'restructuring_fee')) {
                Schema::table('loan_rescheduling_history', function (Blueprint $table) {
                    $table->decimal('restructuring_fee', 15, 2)->nullable()->after('lump_sum_amount');
                });
                $added[] = 'restructuring_fee';
            }
            
            if (!Schema::hasColumn('loan_rescheduling_history', 'restructuring_fee_rate')) {
                Schema::table('loan_rescheduling_history', function (Blueprint $table) {
                    $table->decimal('restructuring_fee_rate', 5, 2)->nullable()->after('restructuring_fee');
                });
                $added[] = 'restructuring_fee_rate';
            }
            
            if (!Schema::hasColumn('loan_rescheduling_history', 'operation_type')) {
                Schema::table('loan_rescheduling_history', function (Blueprint $table) {
                    $table->enum('operation_type', ['lump_sum_rescheduling', 'loan_restructuring'])->nullable()->after('reschedule_type');
                });
                $added[] = 'operation_type';
            }
            
            if (!Schema::hasColumn('loan_rescheduling_history', 'original_interest_rate_used')) {
                Schema::table('loan_rescheduling_history', function (Blueprint $table) {
                    $table->decimal('original_interest_rate_used', 8, 6)->nullable()->after('operation_type');
                });
                $added[] = 'original_interest_rate_used';
            }
            
            if (!empty($added)) {
                Log::info('Restructuring columns added successfully: ' . implode(', ', $added));
                return "Restructuring columns added successfully: " . implode(', ', $added);
            }
            
            return "All restructuring columns already exist";
            
        } catch (\Exception $e) {
            Log::error('Error adding restructuring columns: ' . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Validate that restructuring maintains original interest rate
     */
    public function validateRestructuredSchedule($agreementId)
    {
        try {
            $agreement = HirePurchaseAgreement::findOrFail($agreementId);
            $originalRate = $this->getOriginalMonthlyInterestRate($agreement);
            
            // Check recent payment schedules to ensure they use original rate
            $recentSchedules = PaymentSchedule::where('agreement_id', $agreementId)
                ->where('created_at', '>', now()->subDays(1)) // Recent schedules
                ->orderBy('installment_number')
                ->take(3)
                ->get();
                
            $validation = [
                'agreement_id' => $agreementId,
                'original_interest_rate' => $originalRate,
                'schedules_validated' => [],
                'all_valid' => true,
                'errors' => []
            ];
            
            foreach ($recentSchedules as $schedule) {
                // Calculate what the interest should be with original rate
                $expectedInterest = $schedule->balance_after * ($originalRate / 100);
                $actualInterest = $schedule->interest_amount;
                $tolerance = 0.01; // 1 cent tolerance
                
                $isValid = abs($actualInterest - $expectedInterest) <= $tolerance;
                
                $validation['schedules_validated'][] = [
                    'installment_number' => $schedule->installment_number,
                    'balance_after' => $schedule->balance_after,
                    'expected_interest' => round($expectedInterest, 2),
                    'actual_interest' => $actualInterest,
                    'difference' => round(abs($actualInterest - $expectedInterest), 4),
                    'is_valid' => $isValid
                ];
                
                if (!$isValid) {
                    $validation['all_valid'] = false;
                    $validation['errors'][] = "Installment {$schedule->installment_number} has incorrect interest calculation";
                }
            }
            
            return response()->json([
                'success' => true,
                'validation' => $validation
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}