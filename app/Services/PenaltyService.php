<?php

namespace App\Services;

use App\Models\Penalty;
use App\Models\PaymentSchedule;
use App\Models\HirePurchaseAgreement;
use App\Models\GentlemanAgreement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PenaltyService
{
    const PENALTY_RATE = 10; // 10% penalty rate
    const PENALTY_TYPES = [
        'hire_purchase' => 'App\Models\HirePurchaseAgreement',
        'gentleman' => 'App\Models\GentlemanAgreement'
    ];

    /**
     * Calculate cumulative penalties for an agreement
     * Each penalty includes all previous unpaid installments
     */
   /**
 * Calculate cumulative penalties for an agreement - FIXED VERSION
 */
public function calculatePenaltiesForAgreement($agreementType, $agreementId)
{
    try {
        Log::info("=== CALCULATING PENALTIES ===", [
            'agreement_type' => $agreementType,
            'agreement_id' => $agreementId
        ]);

        // Get all overdue payment schedules in chronological order
        $overdueSchedules = PaymentSchedule::where('agreement_id', $agreementId)
            ->where('status', 'overdue')
            ->orderBy('due_date', 'asc')
            ->get();

        if ($overdueSchedules->isEmpty()) {
            Log::info("No overdue schedules found for agreement {$agreementId}");
            return ['penalties_created' => 0, 'penalties_updated' => 0];
        }

        // IMPROVED SCENARIO DETECTION
        $totalSchedules = PaymentSchedule::where('agreement_id', $agreementId)->count();
        $paidSchedules = PaymentSchedule::where('agreement_id', $agreementId)
            ->where('status', 'paid')->count();
        $pendingSchedules = PaymentSchedule::where('agreement_id', $agreementId)
            ->where('status', 'pending')->count();

        // Check if we're dealing with a final installment scenario
        // Criteria: Only 1 overdue schedule AND no pending schedules (all others are paid)
        $isFinalInstallment = ($overdueSchedules->count() == 1 && $pendingSchedules == 0);

        Log::info("IMPROVED Scenario detection:", [
            'total_schedules' => $totalSchedules,
            'paid_schedules' => $paidSchedules,
            'pending_schedules' => $pendingSchedules,
            'overdue_count' => $overdueSchedules->count(),
            'is_final_installment' => $isFinalInstallment
        ]);

        if ($isFinalInstallment) {
            // SCENARIO 1: Final installment - progressive monthly penalty
            Log::info("Applying PROGRESSIVE MONTHLY PENALTIES for final installment");
            return $this->calculateProgressiveMonthlyPenalties($agreementType, $agreementId, $overdueSchedules->first());
        } else {
            // SCENARIO 2: Multiple installments - cumulative penalty
            Log::info("Applying CUMULATIVE PENALTIES for multiple installments");
            return $this->calculateCumulativePenalties($agreementType, $agreementId, $overdueSchedules);
        }

    } catch (\Exception $e) {
        Log::error('Penalty calculation failed:', [
            'error' => $e->getMessage(),
            'agreement_type' => $agreementType,
            'agreement_id' => $agreementId
        ]);
        throw $e;
    }
}
/**
 * SCENARIO 1: Progressive monthly penalties for final installment
 */
private function calculateProgressiveMonthlyPenalties($agreementType, $agreementId, $overdueSchedule)
{
    $unpaidAmount = $overdueSchedule->total_amount - ($overdueSchedule->amount_paid ?? 0);
    $dueDate = Carbon::parse($overdueSchedule->due_date);
    $today = Carbon::today();
    
    // FIX: Better month calculation logic
    $monthsOverdue = $dueDate->diffInMonths($today);
    
    // If current day is >= due day OR we're in a later month, count full months
    if ($today->day >= $dueDate->day || $monthsOverdue > 0) {
        $monthsOverdue = $monthsOverdue + 1;
    }
    
    // Ensure at least 1 month if overdue
    $monthsOverdue = max(1, $monthsOverdue);

    Log::info("DEBUGGING: Progressive monthly penalty calculation:", [
        'unpaid_amount' => $unpaidAmount,
        'due_date' => $dueDate->format('Y-m-d'),
        'today' => $today->format('Y-m-d'),
        'calculated_months_overdue' => $monthsOverdue,
        'due_day' => $dueDate->day,
        'today_day' => $today->day
    ]);

    $penaltiesCreated = 0;
    $penaltiesUpdated = 0;

    // Create penalty for each month overdue UP TO TODAY
    for ($month = 1; $month <= $monthsOverdue; $month++) {
        $penaltyAmount = $unpaidAmount * $month * (self::PENALTY_RATE / 100);
        $penaltyDate = $dueDate->copy()->addMonths($month - 1);
        
        Log::info("Processing month {$month}:", [
            'penalty_date' => $penaltyDate->format('Y-m-d'),
            'penalty_amount' => $penaltyAmount,
            'is_penalty_date_past' => $penaltyDate->lte($today)
        ]);
        
        // Only create penalty if the penalty month has started
        if ($penaltyDate->lte($today)) {
            $existingPenalty = Penalty::where('agreement_type', $agreementType)
                ->where('agreement_id', $agreementId)
                ->where('payment_schedule_id', $overdueSchedule->id)
                ->where('penalty_sequence', $month)
                ->first();

            if ($existingPenalty) {
                if (abs($existingPenalty->penalty_amount - $penaltyAmount) > 0.01) {
                    $existingPenalty->update([
                        'penalty_amount' => $penaltyAmount,
                        'days_overdue' => $today->diffInDays($dueDate),
                        'updated_at' => now()
                    ]);
                    $penaltiesUpdated++;
                    
                    Log::info("Updated existing penalty for month {$month}");
                }
            } else {
                Penalty::create([
                    'agreement_type' => $agreementType,
                    'agreement_id' => $agreementId,
                    'payment_schedule_id' => $overdueSchedule->id,
                    'installment_number' => $overdueSchedule->installment_number,
                    'expected_amount' => $overdueSchedule->total_amount,
                    'penalty_rate' => self::PENALTY_RATE,
                    'penalty_amount' => $penaltyAmount,
                    'due_date' => $penaltyDate,
                    'days_overdue' => $today->diffInDays($dueDate),
                    'status' => 'pending',
                    'amount_paid' => 0,
                    'created_by' => auth()->id() ?? 1,
                    'cumulative_unpaid_amount' => $unpaidAmount,
                    'penalty_sequence' => $month,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $penaltiesCreated++;
                
                Log::info("Created penalty for month {$month}:", [
                    'penalty_date' => $penaltyDate->format('Y-m-d'),
                    'penalty_amount' => $penaltyAmount
                ]);
            }
        } else {
            Log::info("Skipping month {$month} - penalty date {$penaltyDate->format('Y-m-d')} is in the future");
        }
    }

    return [
        'penalties_created' => $penaltiesCreated,
        'penalties_updated' => $penaltiesUpdated,
        'calculation_type' => 'progressive_monthly',
        'months_calculated' => $monthsOverdue
    ];
}
/**
 * SCENARIO 2: Cumulative penalties for multiple installments
 */
private function calculateCumulativePenalties($agreementType, $agreementId, $overdueSchedules)
{
    $penaltiesCreated = 0;
    $penaltiesUpdated = 0;
    $cumulativeUnpaidAmount = 0;

    foreach ($overdueSchedules as $index => $schedule) {
        $unpaidAmount = $schedule->total_amount - ($schedule->amount_paid ?? 0);
        $cumulativeUnpaidAmount += $unpaidAmount;
        $penaltyAmount = $cumulativeUnpaidAmount * (self::PENALTY_RATE / 100);

        Log::info("Cumulative penalty for installment {$schedule->installment_number}:", [
            'unpaid_amount' => $unpaidAmount,
            'cumulative_unpaid' => $cumulativeUnpaidAmount,
            'penalty_amount' => $penaltyAmount
        ]);

        $existingPenalty = Penalty::where('agreement_type', $agreementType)
            ->where('agreement_id', $agreementId)
            ->where('payment_schedule_id', $schedule->id)
            ->first();

        if ($existingPenalty) {
            if (abs($existingPenalty->penalty_amount - $penaltyAmount) > 0.01) {
                $existingPenalty->update([
                    'penalty_amount' => $penaltyAmount,
                    'cumulative_unpaid_amount' => $cumulativeUnpaidAmount,
                    'penalty_sequence' => $index + 1,
                    'updated_at' => now()
                ]);
                $penaltiesUpdated++;
            }
        } else {
            Penalty::create([
                'agreement_type' => $agreementType,
                'agreement_id' => $agreementId,
                'payment_schedule_id' => $schedule->id,
                'installment_number' => $schedule->installment_number,
                'expected_amount' => $schedule->total_amount,
                'penalty_rate' => self::PENALTY_RATE,
                'penalty_amount' => $penaltyAmount,
                'due_date' => $schedule->due_date,
                'days_overdue' => $this->calculateDaysOverdue($schedule->due_date),
                'status' => 'pending',
                'amount_paid' => 0,
                'created_by' => auth()->id() ?? 1,
                'cumulative_unpaid_amount' => $cumulativeUnpaidAmount,
                'penalty_sequence' => $index + 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $penaltiesCreated++;
        }
    }

    return [
        'penalties_created' => $penaltiesCreated,
        'penalties_updated' => $penaltiesUpdated,
        'calculation_type' => 'cumulative'
    ];
}
    /**
     * Get penalty summary with cumulative totals
     */
    public function getPenaltySummary($agreementType, $agreementId)
    {
        $penalties = Penalty::forAgreement($agreementType, $agreementId)->get();
        
        $summary = [
            'total_penalties' => $penalties->sum('penalty_amount'),
            'pending_penalties' => $penalties->where('status', 'pending')->sum('penalty_amount'),
            'paid_penalties' => $penalties->where('status', 'paid')->sum('amount_paid'),
            'waived_penalties' => $penalties->where('status', 'waived')->sum('penalty_amount'),
            'outstanding_penalties' => $penalties->where('status', 'pending')->sum(function($penalty) {
                return $penalty->penalty_amount - $penalty->amount_paid;
            }),
            'penalty_count' => $penalties->count(),
            'overdue_installments' => $penalties->where('status', 'pending')->count(),
            'max_cumulative_unpaid' => $penalties->max('cumulative_unpaid_amount') ?? 0,
            'penalty_breakdown' => $penalties->groupBy('status')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('penalty_amount')
                ];
            })
        ];

        return $summary;
    }

    /**
     * Calculate days overdue from due date
     */
    private function calculateDaysOverdue($dueDate)
    {
        $due = Carbon::parse($dueDate);
        $today = Carbon::now();
        
        return $today->gt($due) ? $today->diffInDays($due) : 0;
    }

    /**
     * Clean up penalties for schedules that are no longer overdue
     */
    private function cleanupResolvedPenalties($agreementType, $agreementId, $currentOverdueIds)
    {
        $penaltiesToRemove = Penalty::where('agreement_type', $agreementType)
            ->where('agreement_id', $agreementId)
            ->where('status', 'pending')
            ->whereNotIn('payment_schedule_id', $currentOverdueIds)
            ->get();

        foreach ($penaltiesToRemove as $penalty) {
            Log::info("Removing penalty for resolved schedule:", [
                'penalty_id' => $penalty->id,
                'installment_number' => $penalty->installment_number
            ]);
            $penalty->delete();
        }
    }

    /**
     * Apply payment to penalties (oldest first)
     */
    public function applyPaymentToPenalties($agreementType, $agreementId, $paymentAmount, $paymentDate)
    {
        $remainingAmount = $paymentAmount;
        $penalties = Penalty::forAgreement($agreementType, $agreementId)
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->get();

        foreach ($penalties as $penalty) {
            if ($remainingAmount <= 0) break;

            $outstanding = $penalty->penalty_amount - $penalty->amount_paid;
            if ($outstanding <= 0) continue;

            $appliedAmount = min($remainingAmount, $outstanding);
            $newAmountPaid = $penalty->amount_paid + $appliedAmount;
            $newStatus = ($newAmountPaid >= $penalty->penalty_amount) ? 'paid' : 'pending';

            $penalty->update([
                'amount_paid' => $newAmountPaid,
                'status' => $newStatus,
                'date_paid' => $newStatus === 'paid' ? $paymentDate : $penalty->date_paid
            ]);

            $remainingAmount -= $appliedAmount;

            Log::info("Applied payment to penalty:", [
                'penalty_id' => $penalty->id,
                'applied_amount' => $appliedAmount,
                'new_status' => $newStatus
            ]);
        }

        return $paymentAmount - $remainingAmount; // Amount actually applied to penalties
    }

    /**
     * Example validation for your scenario
     */
    public function validateCumulativePenaltyCalculation()
    {
        // Example scenario from your description:
        // 3 overdue installments of 50,000 each on 2025-04-20, 2025-05-20, 2025-06-20
        
        $installmentAmount = 50000;
        $penaltyRate = 10; // 10%
        
        $expectedPenalties = [
            1 => $installmentAmount * ($penaltyRate / 100), // 50,000 * 10% = 5,000
            2 => ($installmentAmount * 2) * ($penaltyRate / 100), // 100,000 * 10% = 10,000
            3 => ($installmentAmount * 3) * ($penaltyRate / 100), // 150,000 * 10% = 15,000
        ];
        
        Log::info("Expected cumulative penalties for validation:", $expectedPenalties);
        
        return $expectedPenalties;
    }
}