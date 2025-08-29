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
        Log::info("=== CALCULATING CUMULATIVE PENALTIES ===", [
            'agreement_type' => $agreementType,
            'agreement_id' => $agreementId
        ]);

        // Get all overdue payment schedules in chronological order
        $overdueSchedules = PaymentSchedule::where('agreement_id', $agreementId)
            ->where('status', 'overdue')
            ->orderBy('due_date', 'asc') // Chronological order is crucial
            ->get();

        if ($overdueSchedules->isEmpty()) {
            Log::info("No overdue schedules found for agreement {$agreementId}");
            return ['penalties_created' => 0, 'penalties_updated' => 0];
        }

        Log::info("Found {$overdueSchedules->count()} overdue schedules", [
            'overdue_dates' => $overdueSchedules->pluck('due_date')->toArray()
        ]);

        $penaltiesCreated = 0;
        $penaltiesUpdated = 0;
        $cumulativeUnpaidAmount = 0;

        foreach ($overdueSchedules as $index => $schedule) {
            // Add current installment to cumulative unpaid amount
            $unpaidAmount = $schedule->total_amount - ($schedule->amount_paid ?? 0);
            $cumulativeUnpaidAmount += $unpaidAmount;

            // Calculate penalty on cumulative unpaid amount
            $penaltyAmount = $cumulativeUnpaidAmount * (self::PENALTY_RATE / 100);

            Log::info("Processing overdue schedule {$schedule->installment_number}:", [
                'due_date' => $schedule->due_date,
                'unpaid_amount' => $unpaidAmount,
                'cumulative_unpaid' => $cumulativeUnpaidAmount,
                'penalty_amount' => $penaltyAmount,
                'penalty_sequence' => $index + 1
            ]);

            // Check if penalty already exists for this schedule
            $existingPenalty = Penalty::where('agreement_type', $agreementType)
                ->where('agreement_id', $agreementId)
                ->where('payment_schedule_id', $schedule->id)
                ->first();

            if ($existingPenalty) {
                // Update existing penalty if amount changed
                if (abs($existingPenalty->penalty_amount - $penaltyAmount) > 0.01) {
                    $existingPenalty->update([
                        'penalty_amount' => $penaltyAmount,
                        'cumulative_unpaid_amount' => $cumulativeUnpaidAmount,
                        'penalty_sequence' => $index + 1,
                        'updated_at' => now()
                    ]);
                    $penaltiesUpdated++;
                    
                    Log::info("Updated existing penalty for installment {$schedule->installment_number}");
                }
            } else {
                // Create new penalty - FIXED to match your table schema
                $penalty = Penalty::create([
                    'agreement_type' => $agreementType,
                    'agreement_id' => $agreementId,
                    'payment_schedule_id' => $schedule->id,
                    'installment_number' => $schedule->installment_number, // ADD THIS LINE
                    // ✅ FIXED: Use expected_amount (from your table) instead of installment_number
                    'expected_amount' => $schedule->total_amount, // The expected payment amount
                    'penalty_rate' => self::PENALTY_RATE,
                    'penalty_amount' => $penaltyAmount,
                    'due_date' => $schedule->due_date,
                    'days_overdue' => $this->calculateDaysOverdue($schedule->due_date),
                    'status' => 'pending',
                    'amount_paid' => 0,
                    // ✅ FIXED: Add required created_by field
                    'created_by' => auth()->id() ?? 1, // Use authenticated user or system user
                    // ✅ FIXED: Add cumulative_unpaid_amount and penalty_sequence
                    'cumulative_unpaid_amount' => $cumulativeUnpaidAmount,
                    'penalty_sequence' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $penaltiesCreated++;
                
                Log::info("Created new penalty for installment {$schedule->installment_number}:", [
                    'penalty_id' => $penalty->id,
                    'penalty_amount' => $penaltyAmount
                ]);
            }
        }

        // Clean up penalties for schedules that are no longer overdue
        $this->cleanupResolvedPenalties($agreementType, $agreementId, $overdueSchedules->pluck('id'));

        Log::info("Cumulative penalty calculation completed", [
            'penalties_created' => $penaltiesCreated,
            'penalties_updated' => $penaltiesUpdated
        ]);

        return [
            'penalties_created' => $penaltiesCreated,
            'penalties_updated' => $penaltiesUpdated,
            'total_overdue_schedules' => $overdueSchedules->count(),
            'final_cumulative_amount' => $cumulativeUnpaidAmount
        ];

    } catch (\Exception $e) {
        Log::error('Cumulative penalty calculation failed:', [
            'error' => $e->getMessage(),
            'agreement_type' => $agreementType,
            'agreement_id' => $agreementId
        ]);
        throw $e;
    }
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