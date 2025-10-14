<?php

namespace App\Services;

use App\Models\Penalty;
use App\Models\PaymentSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PenaltyService
{
    const PENALTY_RATE = 10;

    /**
     * Calculate penalties - ATOMIC
     */
    public function calculatePenaltiesForAgreement($agreementType, $agreementId)
    {
        return DB::transaction(function () use ($agreementType, $agreementId) {
            try {
                Log::info("=== PENALTY CALCULATION START ===", [
                    'agreement_type' => $agreementType,
                    'agreement_id' => $agreementId
                ]);

                $overdueSchedules = PaymentSchedule::where('agreement_id', $agreementId)
                    ->where('status', 'overdue')
                    ->orderBy('due_date', 'asc')
                    ->get();

                if ($overdueSchedules->isEmpty()) {
                    return ['penalties_created' => 0, 'message' => 'No overdue schedules'];
                }

                // Delete all pending penalties
                $deleted = Penalty::where('agreement_type', $agreementType)
                    ->where('agreement_id', $agreementId)
                    ->where('status', 'pending')
                    ->delete();

                Log::info("Deleted {$deleted} pending penalties");

                // AUTO-DETECT scenario type
                $scenarioType = $this->detectScenarioType($overdueSchedules);
                Log::info("Detected scenario type: {$scenarioType}");

                if ($scenarioType === 'final_payment') {
                    return $this->calculateFinalPaymentPenalties($agreementType, $agreementId, $overdueSchedules);
                } else {
                    return $this->calculateContinuingInstallmentPenalties($agreementType, $agreementId, $overdueSchedules);
                }

            } catch (\Exception $e) {
                Log::error('Penalty calculation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }

    /**
     * AUTO-DETECT: Is this a FINAL PAYMENT or CONTINUING INSTALLMENTS?
     * 
     * FINAL PAYMENT: Only ONE schedule is overdue
     * CONTINUING INSTALLMENTS: Multiple schedules are overdue
     */
    private function detectScenarioType($overdueSchedules)
    {
        $count = $overdueSchedules->count();
        
        Log::info("Detecting scenario type", [
            'overdue_schedule_count' => $count,
            'schedules' => $overdueSchedules->map(fn($s) => [
                'id' => $s->id,
                'installment' => $s->installment_number,
                'due_date' => $s->due_date
            ])->toArray()
        ]);

        if ($count === 1) {
            return 'final_payment';
        } else {
            return 'continuing_installments';
        }
    }

    /**
     * SCENARIO 1: FINAL PAYMENT
     * ========================
     * ONE schedule overdue with MULTIPLE MONTHS
     * Creates ONE ROW PER MONTH with compound interest
     * 
     * Example: 20,000 unpaid, 5 months overdue
     * Month 1: 20,000 + (20,000 × 10%) = 22,000
     * Month 2: 22,000 + (22,000 × 10%) = 24,200
     * Month 3: 24,200 + (24,200 × 10%) = 26,620
     * Month 4: 26,620 + (26,620 × 10%) = 29,282
     * Month 5: 29,282 + (29,282 × 10%) = 32,210
     */
    private function calculateFinalPaymentPenalties($agreementType, $agreementId, $overdueSchedules)
    {
        $today = Carbon::today();
        $rate = self::PENALTY_RATE / 100;
        $penaltiesCreated = 0;

        foreach ($overdueSchedules as $schedule) {
            $expected = $schedule->total_amount ?? $schedule->expected_amount ?? 0;
            $amountPaid = $schedule->amount_paid ?? 0;
            $dueDate = Carbon::parse($schedule->due_date);

            if ($dueDate->gt($today)) {
                Log::info("Skipping future schedule", ['id' => $schedule->id]);
                continue;
            }

            $unpaid = max(0, $expected - $amountPaid);
            if ($unpaid <= 0) {
                Log::info("Skipping fully paid schedule", ['id' => $schedule->id]);
                continue;
            }

            $monthsOverdue = max(1, $dueDate->diffInMonths($today));

            Log::info("FINAL PAYMENT MODE", [
                'schedule_id' => $schedule->id,
                'unpaid' => $unpaid,
                'months_overdue' => $monthsOverdue
            ]);

            $runningTotal = $unpaid;

            for ($month = 1; $month <= $monthsOverdue; $month++) {
                // Penalty = Current Total × Rate
                $monthlyPenalty = round($runningTotal * $rate, 2);
                $runningTotal = round($runningTotal + $monthlyPenalty, 2);

                $penaltyDueDate = $dueDate->copy()->addMonths($month - 1);

                Log::info("Final Payment - Month {$month}", [
                    'due_date' => $penaltyDueDate->format('Y-m-d'),
                    'base_total' => round($runningTotal - $monthlyPenalty, 2),
                    'penalty' => $monthlyPenalty,
                    'new_total' => $runningTotal
                ]);

                Penalty::create([
                    'agreement_type' => $agreementType,
                    'agreement_id' => $agreementId,
                    'payment_schedule_id' => $schedule->id,
                    'installment_number' => $schedule->installment_number,
                    'expected_amount' => $expected,
                    'penalty_rate' => self::PENALTY_RATE,
                    'penalty_amount' => $monthlyPenalty,
                    'cumulative_unpaid_amount' => $runningTotal,
                    'penalty_sequence' => $month,
                    'due_date' => $penaltyDueDate,
                    'days_overdue' => $penaltyDueDate->diffInDays($today),
                    'status' => 'pending',
                    'amount_paid' => 0,
                    'created_by' => auth()->id() ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $penaltiesCreated++;
            }
        }

        Log::info("Final Payment calculation complete", ['penalties_created' => $penaltiesCreated]);

        return [
            'penalties_created' => $penaltiesCreated,
            'calculation_type' => 'final_payment_compound_monthly',
            'processed_schedules' => $overdueSchedules->count()
        ];
    }

    /**
     * SCENARIO 2: CONTINUING INSTALLMENTS
     * ===================================
     * MULTIPLE schedules each on different dates
     * Creates ONE ROW PER SCHEDULE
     * Formula: (Expected Amount + Previous Penalty) × 10%
     * 
     * Example: 300,000 per schedule
     * Schedule 1: 300,000 × 10% = 30,000
     * Schedule 2: (300,000 + 30,000) × 10% = 33,000
     * Schedule 3: (300,000 + 33,000) × 10% = 33,300
     */
private function calculateContinuingInstallmentPenalties($agreementType, $agreementId, $overdueSchedules)
{
    $today = Carbon::today();
    $rate = self::PENALTY_RATE / 100;
    $penaltiesCreated = 0;
    $previousSchedulePenalty = 0;
    $cumulativeUnpaidInstallments = 0; // Initialize cumulative unpaid amount

    Log::info("CONTINUING INSTALLMENTS MODE", [
        'schedule_count' => $overdueSchedules->count()
    ]);

    foreach ($overdueSchedules as $index => $schedule) {
        $expected = $schedule->total_amount ?? $schedule->expected_amount ?? 0;
        $amountPaid = $schedule->amount_paid ?? 0;
        $dueDate = Carbon::parse($schedule->due_date);

        if ($dueDate->gt($today)) {
            Log::info("Skipping future schedule", ['id' => $schedule->id]);
            continue;
        }

        $unpaid = max(0, $expected - $amountPaid);
        if ($unpaid <= 0) {
            Log::info("Skipping fully paid schedule", ['id' => $schedule->id]);
            continue;
        }

        // Accumulate unpaid installments
        $cumulativeUnpaidInstallments += $expected;

        $daysOverdue = $today->diffInDays($dueDate);

        // Formula: (Cumulative Unpaid Installments + Previous Penalty) × Rate
        $baseForPenalty = $cumulativeUnpaidInstallments + $previousSchedulePenalty;
        $penaltyAmount = round($baseForPenalty * $rate, 2);
        $totalOwed = round($cumulativeUnpaidInstallments + $penaltyAmount, 2);

        Log::info("Continuing Installment - Schedule {$schedule->installment_number}", [
            'expected' => $expected,
            'cumulative_unpaid' => $cumulativeUnpaidInstallments,
            'previous_penalty' => $previousSchedulePenalty,
            'base_for_penalty' => $baseForPenalty,
            'formula' => "({$cumulativeUnpaidInstallments} + {$previousSchedulePenalty}) × 10%",
            'penalty_amount' => $penaltyAmount,
            'total_owed' => $totalOwed
        ]);

        Penalty::create([
            'agreement_type' => $agreementType,
            'agreement_id' => $agreementId,
            'payment_schedule_id' => $schedule->id,
            'installment_number' => $schedule->installment_number,
            'expected_amount' => $expected,
            'penalty_rate' => self::PENALTY_RATE,
            'penalty_amount' => $penaltyAmount,
            'cumulative_unpaid_amount' => $totalOwed,
            'penalty_sequence' => $index + 1,
            'due_date' => $dueDate,
            'days_overdue' => $daysOverdue,
            'status' => 'pending',
            'amount_paid' => 0,
            'created_by' => auth()->id() ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penaltiesCreated++;

        // Update for next schedule
        $previousSchedulePenalty = $penaltyAmount;
    }

    Log::info("Continuing Installments calculation complete", [
        'penalties_created' => $penaltiesCreated,
        'schedules_processed' => $overdueSchedules->count(),
        'final_cumulative_unpaid' => $cumulativeUnpaidInstallments
    ]);

    return [
        'penalties_created' => $penaltiesCreated,
        'calculation_type' => 'continuing_installments_progressive',
        'processed_schedules' => $overdueSchedules->count()
    ];
}

    public function getPenaltySummary($agreementType, $agreementId)
    {
        $penalties = Penalty::forAgreement($agreementType, $agreementId)->get();
        
        return [
            'total_penalties' => $penalties->sum('penalty_amount'),
            'pending_penalties' => $penalties->where('status', 'pending')->sum('penalty_amount'),
            'paid_penalties' => $penalties->where('status', 'paid')->sum('amount_paid'),
            'penalty_count' => $penalties->count(),
        ];
    }

    public function applyPaymentToPenalties($agreementType, $agreementId, $paymentAmount, $paymentDate)
    {
        $remaining = $paymentAmount;
        $penalties = Penalty::forAgreement($agreementType, $agreementId)
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->orderBy('penalty_sequence', 'asc')
            ->get();

        foreach ($penalties as $penalty) {
            if ($remaining <= 0) break;

            $outstanding = $penalty->penalty_amount - $penalty->amount_paid;
            if ($outstanding <= 0) continue;

            $applied = min($remaining, $outstanding);
            $penalty->update([
                'amount_paid' => $penalty->amount_paid + $applied,
                'status' => ($penalty->amount_paid + $applied >= $penalty->penalty_amount) ? 'paid' : 'pending',
                'date_paid' => ($penalty->amount_paid + $applied >= $penalty->penalty_amount) ? $paymentDate : null
            ]);

            $remaining -= $applied;
        }

        return $paymentAmount - $remaining;
    }

    public function validateCalculations($agreementType, $agreementId)
    {
        $overdueSchedules = PaymentSchedule::where('agreement_id', $agreementId)
            ->where('status', 'overdue')
            ->orderBy('due_date', 'asc')
            ->get();

        if ($overdueSchedules->isEmpty()) {
            return ['message' => 'No overdue schedules'];
        }

        $scenarioType = $this->detectScenarioType($overdueSchedules);
        $today = Carbon::today();
        $rate = self::PENALTY_RATE / 100;
        $results = ['scenario' => $scenarioType];

        if ($scenarioType === 'final_payment') {
            foreach ($overdueSchedules as $schedule) {
                $expected = $schedule->total_amount ?? $schedule->expected_amount ?? 0;
                $paid = $schedule->amount_paid ?? 0;
                $unpaid = max(0, $expected - $paid);
                $dueDate = Carbon::parse($schedule->due_date);
                $monthsOverdue = max(1, $dueDate->diffInMonths($today));

                if ($unpaid <= 0) continue;

                $results['final_payment'] = [
                    'installment' => $schedule->installment_number,
                    'unpaid' => $unpaid,
                    'months_overdue' => $monthsOverdue,
                    'breakdown' => []
                ];

                $total = $unpaid;
                for ($m = 1; $m <= $monthsOverdue; $m++) {
                    $penalty = round($total * $rate, 2);
                    $total = round($total + $penalty, 2);
                    
                    $results['final_payment']['breakdown'][$m] = [
                        'month' => $m,
                        'base' => round($total - $penalty, 2),
                        'penalty' => $penalty,
                        'total' => $total
                    ];
                }
            }
        } else {
            $results['continuing'] = [];
            $previousPenalty = 0;

            foreach ($overdueSchedules as $schedule) {
                $expected = $schedule->total_amount ?? $schedule->expected_amount ?? 0;
                $paid = $schedule->amount_paid ?? 0;
                $unpaid = max(0, $expected - $paid);

                if ($unpaid <= 0) continue;

                $base = $expected + $previousPenalty;
                $penalty = round($base * $rate, 2);

                $results['continuing'][] = [
                    'installment' => $schedule->installment_number,
                    'expected' => $expected,
                    'previous_penalty' => $previousPenalty,
                    'formula' => "({$expected} + {$previousPenalty}) × 10%",
                    'penalty' => $penalty,
                    'total_owed' => round($expected + $penalty, 2)
                ];

                $previousPenalty = $penalty;
            }
        }

        Log::info("Validation Results:", $results);
        return $results;
    }
}