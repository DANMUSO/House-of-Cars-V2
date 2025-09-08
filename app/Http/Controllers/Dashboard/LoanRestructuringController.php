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
use Carbon\Carbon;

class LoanRestructuringController extends Controller
{
    // Constants
    const DEFAULT_RESTRUCTURING_FEE_RATE = 3.0; // 3% default fee
    
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
                'carImport'
            ])->findOrFail($agreementId);
            
            // Validate eligibility
            $eligibility = $this->validateRestructuringEligibility($agreement);
            
            if (!$eligibility['eligible']) {
                return redirect()->back()->with('error', implode(', ', $eligibility['errors']));
            }
            
            // Calculate current financial position
            $financialSummary = $this->calculateCurrentFinancialPosition($agreement);
            
            return view('loan-restructuring.options', compact('agreement', 'financialSummary', 'eligibility'));
            
        } catch (\Exception $e) {
            Log::error('Error loading restructuring page: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load restructuring options. Please try again.');
        }
    }
    
    /**
     * Get restructuring options via API
     */
    public function getRestructuringOptions(Request $request)
    {
        try {
            $agreementId = $request->get('agreement_id');
            $restructuringType = $request->get('restructuring_type'); // 'reduce_duration' or 'increase_duration'
            $newDuration = $request->get('new_duration'); // Optional: specific duration for increase_duration
            
            if (!$agreementId) {
                return response()->json(['error' => 'Agreement ID is required'], 400);
            }
            
            $agreement = HirePurchaseAgreement::with(['paymentSchedule', 'penalties'])->findOrFail($agreementId);
            
            // Validate eligibility
            $eligibility = $this->validateRestructuringEligibility($agreement);
            if (!$eligibility['eligible']) {
                return response()->json([
                    'error' => 'Not eligible for restructuring',
                    'reasons' => $eligibility['errors']
                ], 422);
            }
            
            // Calculate current financial position
            $financialPosition = $this->calculateCurrentFinancialPosition($agreement);
            
            // Calculate restructuring fee
            $restructuringFee = $this->calculateRestructuringFee($financialPosition['total_outstanding']);
            $newLoanAmount = $financialPosition['total_outstanding'] + $restructuringFee;
            
            // Get original interest rate
            $originalMonthlyRate = $this->getOriginalMonthlyInterestRate($agreement);
            $monthlyInterestDecimal = $originalMonthlyRate / 100;
            
            // Calculate remaining months for baseline
            $remainingMonths = $this->getRemainingMonths($agreement);
            
            // Calculate options
            $options = [];
            
            if (!$restructuringType || $restructuringType === 'reduce_duration') {
                $options['reduce_duration'] = $this->calculateReduceDurationOption(
                    $newLoanAmount, 
                    $monthlyInterestDecimal,
                    $remainingMonths,
                    $agreement->monthly_payment
                );
            }
            
            if (!$restructuringType || $restructuringType === 'increase_duration') {
                $options['increase_duration'] = $this->calculateIncreaseDurationOption(
                    $newLoanAmount, 
                    $monthlyInterestDecimal,
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
                'original_monthly_rate' => $originalMonthlyRate,
                'remaining_months' => $remainingMonths,
                'options' => $options
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error calculating restructuring options: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to calculate restructuring options: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process loan restructuring
     */
    public function processRestructuring(Request $request)
    {
        try {
            $validated = $request->validate([
                'agreement_id' => 'required|integer|exists:hire_purchase_agreements,id',
                'restructuring_type' => 'required|string|in:reduce_duration,increase_duration',
                'new_duration' => 'nullable|integer|min:1|max:120', // Required for increase_duration
                'restructuring_date' => 'required|date',
                'notes' => 'nullable|string|max:1000'
            ]);
            
            DB::beginTransaction();
            
            $agreement = HirePurchaseAgreement::with(['paymentSchedule', 'penalties'])->findOrFail($validated['agreement_id']);
            
            // Validate eligibility again
            $eligibility = $this->validateRestructuringEligibility($agreement);
            if (!$eligibility['eligible']) {
                throw new \Exception('Agreement is not eligible for restructuring: ' . implode(', ', $eligibility['errors']));
            }
            
            // Calculate restructuring fee and new loan amount
            $financialPosition = $this->calculateCurrentFinancialPosition($agreement);
            $restructuringFee = $this->calculateRestructuringFee($financialPosition['total_outstanding']);
            $newLoanAmount = $financialPosition['total_outstanding'] + $restructuringFee;
            
            // Store original terms for history
            $originalTerms = $this->captureOriginalTerms($agreement);
            
            // Perform restructuring based on type
            if ($validated['restructuring_type'] === 'reduce_duration') {
                $result = $this->performReduceDurationRestructuring($agreement, $newLoanAmount, $validated);
            } else {
                $result = $this->performIncreaseDurationRestructuring($agreement, $newLoanAmount, $validated);
            }
            
            // Update agreement with new terms
            $this->updateAgreementAfterRestructuring($agreement, $result, $restructuringFee);
            
            // Create restructuring history record
            $historyId = $this->createRestructuringHistory(
                $agreement,
                $validated,
                $originalTerms,
                $result,
                $restructuringFee,
                $financialPosition
            );
            
            DB::commit();
            
            Log::info('Loan restructuring completed successfully', [
                'agreement_id' => $agreement->id,
                'restructuring_type' => $validated['restructuring_type'],
                'restructuring_fee' => $restructuringFee,
                'history_id' => $historyId
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Loan restructured successfully!',
                'restructuring_details' => $result,
                'restructuring_fee' => $restructuringFee,
                'new_loan_amount' => $newLoanAmount,
                'history_id' => $historyId
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Loan restructuring failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Validate restructuring eligibility
     */
    private function validateRestructuringEligibility($agreement)
    {
        $eligibility = [
            'eligible' => true,
            'errors' => [],
            'warnings' => []
        ];
        
        // Check agreement status
        if ($agreement->status === 'completed') {
            $eligibility['eligible'] = false;
            $eligibility['errors'][] = 'Cannot restructure a completed loan';
        }
        
        if ($agreement->status === 'pending') {
            $eligibility['eligible'] = false;
            $eligibility['errors'][] = 'Loan must be approved before restructuring';
        }
        
        // Check if any payments have been made
        $paymentsMade = $agreement->paymentSchedule()->where('status', 'paid')->count();
        if ($paymentsMade === 0) {
            $eligibility['eligible'] = false;
            $eligibility['errors'][] = 'At least one payment must be made before restructuring';
        }
        
        // Check for recent restructuring (within last 6 months)
        $recentRestructuring = DB::table('loan_rescheduling_history')
            ->where('agreement_id', $agreement->id)
            ->where('status', 'active')
            ->where('rescheduling_date', '>', now()->subMonths(6))
            ->exists();
            
        if ($recentRestructuring) {
            $eligibility['warnings'][] = 'Recent restructuring found within the last 6 months';
        }
        
        // Check maximum restructuring count
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
    
    /**
     * Calculate current financial position
     */
    private function calculateCurrentFinancialPosition($agreement)
    {
        // Calculate due payments (overdue + current due)
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
        
        // Calculate principal balance (remaining principal from unpaid installments)
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
        
        // Calculate total penalties
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
    
    /**
     * Calculate restructuring fee
     */
    private function calculateRestructuringFee($totalOutstanding)
    {
        $feeRate = $this->getRestructuringFeeRate();
        return ($totalOutstanding * $feeRate) / 100;
    }
    
    /**
     * Get restructuring fee rate (from config or default)
     */
    private function getRestructuringFeeRate()
    {
        return config('loan.restructuring_fee_rate', self::DEFAULT_RESTRUCTURING_FEE_RATE);
    }
    
    /**
     * Calculate reduce duration option
     */
    private function calculateReduceDurationOption($newLoanAmount, $monthlyInterestDecimal, $currentRemainingMonths, $currentPayment)
    {
        // For reduce duration, we increase monthly payment to finish faster
        $targetDuration = max(1, round($currentRemainingMonths * 0.75)); // 25% reduction as example
        
        // Calculate new payment using PMT formula
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
            $finalDuration = $alternativeDuration;
            $finalPayment = $alternativePayment;
        } else {
            $finalDuration = $targetDuration;
            $finalPayment = $newMonthlyPayment;
        }
        
        $paymentIncrease = $finalPayment - $currentPayment;
        $durationReduction = $currentRemainingMonths - $finalDuration;
        
        return [
            'type' => 'reduce_duration',
            'current_duration' => $currentRemainingMonths,
            'new_duration' => $finalDuration,
            'duration_reduction' => max(0, $durationReduction),
            'current_payment' => $currentPayment,
            'new_payment' => $finalPayment,
            'payment_increase' => $paymentIncrease,
            'total_interest_saved' => $this->calculateInterestSaved($currentPayment, $currentRemainingMonths, $finalPayment, $finalDuration),
            'description' => "Increase monthly payment to KSh " . number_format($finalPayment, 2) . 
                           " and finish {$durationReduction} months earlier"
        ];
    }
    
    /**
     * Calculate increase duration option
     */
    private function calculateIncreaseDurationOption($newLoanAmount, $monthlyInterestDecimal, $currentRemainingMonths, $currentPayment, $targetDuration = null)
    {
        // For increase duration, we extend the loan to reduce monthly payment
        if (!$targetDuration) {
            $targetDuration = round($currentRemainingMonths * 1.5); // 50% increase as default
            $targetDuration = min($targetDuration, 72); // Cap at 72 months total
        }
        
        // Calculate new payment using PMT formula
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
            'description' => "Reduce monthly payment to KSh " . number_format($newMonthlyPayment, 2) . 
                           " and extend loan by {$durationIncrease} months"
        ];
    }
    
    /**
     * Perform reduce duration restructuring
     */
    private function performReduceDurationRestructuring($agreement, $newLoanAmount, $validated)
    {
        $originalMonthlyRate = $this->getOriginalMonthlyInterestRate($agreement);
        $monthlyInterestDecimal = $originalMonthlyRate / 100;
        $currentRemainingMonths = $this->getRemainingMonths($agreement);
        
        $option = $this->calculateReduceDurationOption(
            $newLoanAmount,
            $monthlyInterestDecimal,
            $currentRemainingMonths,
            $agreement->monthly_payment
        );
        
        // Clear existing unpaid schedules
        $this->clearUnpaidSchedules($agreement->id);
        
        // Generate new schedule
        $this->generateNewPaymentSchedule(
            $agreement->id,
            $newLoanAmount,
            $option['new_payment'],
            $option['new_duration'],
            $monthlyInterestDecimal,
            $this->getNextDueDate($agreement)
        );
        
        return $option;
    }
    
    /**
     * Perform increase duration restructuring
     */
    private function performIncreaseDurationRestructuring($agreement, $newLoanAmount, $validated)
    {
        $originalMonthlyRate = $this->getOriginalMonthlyInterestRate($agreement);
        $monthlyInterestDecimal = $originalMonthlyRate / 100;
        $currentRemainingMonths = $this->getRemainingMonths($agreement);
        
        $option = $this->calculateIncreaseDurationOption(
            $newLoanAmount,
            $monthlyInterestDecimal,
            $currentRemainingMonths,
            $agreement->monthly_payment,
            $validated['new_duration']
        );
        
        // Clear existing unpaid schedules
        $this->clearUnpaidSchedules($agreement->id);
        
        // Generate new schedule
        $this->generateNewPaymentSchedule(
            $agreement->id,
            $newLoanAmount,
            $option['new_payment'],
            $option['new_duration'],
            $monthlyInterestDecimal,
            $this->getNextDueDate($agreement)
        );
        
        return $option;
    }
    
    /**
     * Helper methods (reused from HirePurchasesController)
     */
    private function getOriginalMonthlyInterestRate($agreement)
    {
        if (isset($agreement->monthly_interest_rate) && $agreement->monthly_interest_rate > 0) {
            return $agreement->monthly_interest_rate;
        }
        
        if ($agreement->interest_rate > 0 && $agreement->interest_rate <= 10) {
            return $agreement->interest_rate;
        }
        
        if ($agreement->interest_rate > 10) {
            return $agreement->interest_rate / 12;
        }
        
        // Fallback based on deposit percentage
        $depositPercentage = ($agreement->deposit_amount / $agreement->vehicle_price) * 100;
        return $depositPercentage >= 50 ? 4.29 : 4.50;
    }
    
    private function calculatePMT($loanAmount, $monthlyRate, $termMonths)
    {
        if ($monthlyRate == 0) {
            return $loanAmount / $termMonths;
        }
        
        $factor = pow(1 + $monthlyRate, $termMonths);
        $pmt = $loanAmount * ($monthlyRate * $factor) / ($factor - 1);
        
        return round($pmt, 2);
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
    
    private function clearUnpaidSchedules($agreementId)
    {
        PaymentSchedule::where('agreement_id', $agreementId)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->delete();
    }
    
    private function generateNewPaymentSchedule($agreementId, $principalAmount, $monthlyPayment, $duration, $monthlyInterestDecimal, $startDate)
    {
        $remainingPrincipal = $principalAmount;
        
        $lastInstallment = PaymentSchedule::where('agreement_id', $agreementId)
            ->max('installment_number') ?? 0;
        
        for ($month = 1; $month <= $duration; $month++) {
            $monthlyInterest = $remainingPrincipal * $monthlyInterestDecimal;
            $monthlyPrincipal = $monthlyPayment - $monthlyInterest;
            
            if ($monthlyPrincipal < 0) {
                $monthlyPrincipal = 0;
                $monthlyInterest = $monthlyPayment;
            }
            
            if ($month == $duration || $monthlyPrincipal >= $remainingPrincipal) {
                $monthlyPrincipal = $remainingPrincipal;
                $actualPayment = $monthlyPrincipal + $monthlyInterest;
                $newRemainingPrincipal = 0;
            } else {
                $actualPayment = $monthlyPayment;
                $newRemainingPrincipal = $remainingPrincipal - $monthlyPrincipal;
            }
            
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
    }
    
    private function captureOriginalTerms($agreement)
    {
        return [
            'duration_months' => $this->getRemainingMonths($agreement),
            'monthly_payment' => $agreement->monthly_payment,
            'completion_date' => $agreement->expected_completion_date,
            'outstanding_balance' => $agreement->outstanding_balance
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
 * Create restructuring history record with backward compatibility
 * This method handles both loan restructuring (new) and lump sum rescheduling (existing)
 */
private function createRestructuringHistory($agreement, $validated, $originalTerms, $result, $restructuringFee, $financialPosition)
{
    // Base data that works for both old and new functionality
    $historyData = [
        'agreement_id' => $agreement->id,
        'payment_id' => null, // No payment involved in pure restructuring
        'reschedule_type' => $validated['restructuring_type'],
        'lump_sum_amount' => 0, // No lump sum in restructuring
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
        'notes' => ($validated['notes'] ?? '') . " (Restructuring with {$this->getRestructuringFeeRate()}% fee)",
        'status' => 'active',
        
        'created_at' => now(),
        'updated_at' => now()
    ];
    
    // Add new restructuring-specific fields if columns exist
    if (Schema::hasColumn('loan_rescheduling_history', 'restructuring_fee')) {
        $historyData['restructuring_fee'] = $restructuringFee;
    }
    
    if (Schema::hasColumn('loan_rescheduling_history', 'restructuring_fee_rate')) {
        $historyData['restructuring_fee_rate'] = $this->getRestructuringFeeRate();
    }
    
    if (Schema::hasColumn('loan_rescheduling_history', 'operation_type')) {
        $historyData['operation_type'] = 'loan_restructuring';
    }
    
    if (Schema::hasColumn('loan_rescheduling_history', 'due_payments_component')) {
        $historyData['due_payments_component'] = $financialPosition['due_payments'];
    }
    
    if (Schema::hasColumn('loan_rescheduling_history', 'principal_component')) {
        $historyData['principal_component'] = $financialPosition['principal_balance'];
    }
    
    if (Schema::hasColumn('loan_rescheduling_history', 'penalties_component')) {
        $historyData['penalties_component'] = $financialPosition['total_penalties'];
    }
    
    if (Schema::hasColumn('loan_rescheduling_history', 'additional_metadata')) {
        $historyData['additional_metadata'] = json_encode([
            'original_interest_rate' => $this->getOriginalMonthlyInterestRate($agreement),
            'breakdown_details' => $financialPosition['breakdown'],
            'restructuring_reason' => $validated['notes'] ?? 'Customer requested restructuring',
            'fee_calculation' => [
                'base_amount' => $financialPosition['total_outstanding'],
                'fee_rate' => $this->getRestructuringFeeRate(),
                'fee_amount' => $restructuringFee
            ]
        ]);
    }
    
    return DB::table('loan_rescheduling_history')->insertGetId($historyData);
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
     * Additional helper methods
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
                'eligibility' => $eligibility
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get financial summary (API endpoint)
     */
    public function getFinancialSummary($agreementId)
    {
        try {
            $agreement = HirePurchaseAgreement::with(['paymentSchedule', 'penalties'])->findOrFail($agreementId);
            $financialSummary = $this->calculateCurrentFinancialPosition($agreement);
            
            return response()->json([
                'success' => true,
                'financial_summary' => $financialSummary,
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
     * Export restructuring analytics
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
                    'lrh.reschedule_type',
                    'lrh.restructuring_fee',
                    'lrh.outstanding_before',
                    'lrh.outstanding_after',
                    'lrh.previous_duration_months',
                    'lrh.new_duration_months',
                    'lrh.previous_monthly_payment',
                    'lrh.new_monthly_payment',
                    'lrh.duration_change_months',
                    'lrh.payment_change_amount',
                    'lrh.rescheduling_date'
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
                ]
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
     * Update database schema for restructuring fee tracking
     * This method adds restructuring_fee column to loan_rescheduling_history table
     */
    public function addRestructuringFeeColumn()
    {
        try {
            if (!Schema::hasColumn('loan_rescheduling_history', 'restructuring_fee')) {
                Schema::table('loan_rescheduling_history', function (Blueprint $table) {
                    $table->decimal('restructuring_fee', 15, 2)->nullable()->after('lump_sum_amount');
                    $table->decimal('restructuring_fee_rate', 5, 2)->nullable()->after('restructuring_fee');
                });
                
                Log::info('Restructuring fee columns added successfully');
                return "Restructuring fee columns added successfully";
            }
            
            return "Restructuring fee columns already exist";
            
        } catch (\Exception $e) {
            Log::error('Error adding restructuring fee columns: ' . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }
}