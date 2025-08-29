<?php
// app/Services/PenaltyService.php

namespace App\Services;

use App\Models\Penalty;
use App\Models\PaymentSchedule;
use App\Models\HirePurchaseAgreement;
use App\Models\GentlemanAgreement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PenaltyService
{
    const DEFAULT_PENALTY_RATE = 10.00; // 10% penalty rate
    
    public function calculatePenaltiesForAgreement($agreementType, $agreementId, $penaltyRate = null)
    {
        $penaltyRate = $penaltyRate ?? self::DEFAULT_PENALTY_RATE;
        $today = Carbon::today();
        
        Log::info("Calculating penalties for {$agreementType} agreement {$agreementId}");
        
        // Get overdue payment schedules
        $overdueSchedules = PaymentSchedule::where('agreement_id', $agreementId)
            ->where('due_date', '<', $today)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->get();
            
        $penaltiesCreated = 0;
        
        foreach ($overdueSchedules as $schedule) {
            // Check if penalty already exists for this schedule
            $existingPenalty = Penalty::where('agreement_type', $agreementType)
                ->where('agreement_id', $agreementId)
                ->where('payment_schedule_id', $schedule->id)
                ->first();
                
            if (!$existingPenalty) {
                $this->createPenaltyForSchedule($schedule, $agreementType, $agreementId, $penaltyRate);
                $penaltiesCreated++;
            } else {
                // Update existing penalty if days overdue changed
                $this->updatePenaltyDaysOverdue($existingPenalty, $schedule);
            }
        }
        
        return [
            'penalties_created' => $penaltiesCreated,
            'penalties_updated' => $overdueSchedules->count() - $penaltiesCreated
        ];
    }
    
    private function createPenaltyForSchedule($schedule, $agreementType, $agreementId, $penaltyRate)
    {
        $expectedAmount = $schedule->total_amount - ($schedule->amount_paid ?? 0);
        $penaltyAmount = ($expectedAmount * $penaltyRate) / 100;
        $daysOverdue = Carbon::parse($schedule->due_date)->diffInDays(Carbon::today());
        
        Penalty::create([
            'agreement_type' => $agreementType,
            'agreement_id' => $agreementId,
            'payment_schedule_id' => $schedule->id,
            'expected_amount' => $expectedAmount,
            'penalty_rate' => $penaltyRate,
            'penalty_amount' => $penaltyAmount,
            'due_date' => $schedule->due_date,
            'days_overdue' => $daysOverdue,
            'status' => 'pending',
            'amount_paid' => 0,
            'created_by' => auth()->id() ?? 1,
        ]);
        
        Log::info("Created penalty for schedule {$schedule->id}: KSh {$penaltyAmount}");
    }
    
    private function updatePenaltyDaysOverdue($penalty, $schedule)
    {
        $daysOverdue = Carbon::parse($schedule->due_date)->diffInDays(Carbon::today());
        
        if ($penalty->days_overdue != $daysOverdue) {
            $penalty->update(['days_overdue' => $daysOverdue]);
        }
    }
    
    public function getPenaltySummary($agreementType, $agreementId)
    {
        $penalties = Penalty::forAgreement($agreementType, $agreementId)->get();
        
        return (object) [
            'total_penalties' => $penalties->count(),
            'pending_count' => $penalties->where('status', 'pending')->count(),
            'paid_count' => $penalties->where('status', 'paid')->count(),
            'waived_count' => $penalties->where('status', 'waived')->count(),
            'total_penalty_amount' => $penalties->sum('penalty_amount'),
            'total_outstanding' => $penalties->where('status', 'pending')->sum(function($p) {
                return $p->penalty_amount - $p->amount_paid;
            })
        ];
    }
}