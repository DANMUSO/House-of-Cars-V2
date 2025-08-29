<?php

namespace App\Services;

use App\Models\Penalty;
use App\Models\PaymentSchedule;
use App\Models\HirePurchaseAgreement;
use App\Models\GentlemanAgreement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenaltyService
{
    const DEFAULT_PENALTY_RATE = 10.00; // 10%

    /**
     * Calculate and apply penalties for overdue payments
     */
    public function calculatePenaltiesForAgreement($agreementType, $agreementId)
    {
        Log::info("Calculating penalties for {$agreementType} agreement ID: {$agreementId}");
        
        try {
            DB::beginTransaction();
            
            // Get overdue payment schedules
            $overdueSchedules = $this->getOverdueSchedules($agreementType, $agreementId);
            $penaltiesCreated = 0;
            
            foreach ($overdueSchedules as $schedule) {
                $existingPenalty = Penalty::where('agreement_type', $agreementType)
                    ->where('agreement_id', $agreementId)
                    ->where('payment_schedule_id', $schedule->id)
                    ->first();
                
                if (!$existingPenalty) {
                    $this->createPenaltyForSchedule($agreementType, $agreementId, $schedule);
                    $penaltiesCreated++;
                }
            }
            
            DB::commit();
            
            Log::info("Created {$penaltiesCreated} penalties for {$agreementType} agreement {$agreementId}");
            
            return [
                'success' => true,
                'penalties_created' => $penaltiesCreated,
                'total_overdue' => $overdueSchedules->count()
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error calculating penalties: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get overdue payment schedules
     */
    private function getOverdueSchedules($agreementType, $agreementId)
    {
        $today = Carbon::today();
        
        return PaymentSchedule::where('agreement_id', $agreementId)
            ->whereIn('status', ['overdue', 'partial'])
            ->where('due_date', '<', $today)
            ->get();
    }

    /**
     * Create penalty for specific payment schedule
     */
    private function createPenaltyForSchedule($agreementType, $agreementId, $schedule)
    {
        $today = Carbon::today();
        $dueDate = Carbon::parse($schedule->due_date);
        $daysOverdue = $today->diffInDays($dueDate);
        
        // Calculate expected amount (remaining unpaid amount)
        $expectedAmount = $schedule->total_amount - ($schedule->amount_paid ?? 0);
        $penaltyAmount = Penalty::calculatePenaltyAmount($expectedAmount, self::DEFAULT_PENALTY_RATE);
        
        return Penalty::create([
            'agreement_type' => $agreementType,
            'agreement_id' => $agreementId,
            'payment_schedule_id' => $schedule->id,
            'expected_amount' => $expectedAmount,
            'penalty_rate' => self::DEFAULT_PENALTY_RATE,
            'penalty_amount' => $penaltyAmount,
            'due_date' => $schedule->due_date,
            'days_overdue' => $daysOverdue,
            'status' => 'pending',
            'created_by' => auth()->id() ?? 1,
        ]);
    }

    /**
     * Apply payment to penalties
     */
    public function applyPaymentToPenalties($agreementType, $agreementId, $paymentAmount, $paymentDate = null, $reference = null)
    {
        $remainingAmount = $paymentAmount;
        $paymentDate = $paymentDate ?? now();
        
        // Get pending penalties ordered by oldest first
        $penalties = Penalty::forAgreement($agreementType, $agreementId)
            ->pending()
            ->orderBy('due_date', 'asc')
            ->get();
        
        foreach ($penalties as $penalty) {
            if ($remainingAmount <= 0) break;
            
            $outstandingPenalty = $penalty->outstanding_amount;
            $paymentToPenalty = min($remainingAmount, $outstandingPenalty);
            
            if ($paymentToPenalty > 0) {
                $penalty->markAsPaid(
                    $penalty->amount_paid + $paymentToPenalty,
                    $paymentDate,
                    $reference
                );
                
                $remainingAmount -= $paymentToPenalty;
            }
        }
        
        return $paymentAmount - $remainingAmount; // Amount applied to penalties
    }

    /**
     * Get penalty summary for agreement
     */
    public function getPenaltySummary($agreementType, $agreementId)
    {
        return Penalty::forAgreement($agreementType, $agreementId)
            ->selectRaw('
                COUNT(*) as total_penalties,
                COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = "paid" THEN 1 END) as paid_count,
                COUNT(CASE WHEN status = "waived" THEN 1 END) as waived_count,
                SUM(penalty_amount) as total_penalty_amount,
                SUM(amount_paid) as total_paid_amount,
                SUM(penalty_amount - amount_paid) as total_outstanding
            ')
            ->first();
    }

    /**
     * Run daily penalty calculation for all agreements
     */
    public function runDailyPenaltyCalculation()
    {
        Log::info('Starting daily penalty calculation');
        
        // Process Hire Purchase agreements
        $hpAgreements = HirePurchaseAgreement::whereIn('status', ['approved', 'active'])->get();
        $hpResults = [];
        
        foreach ($hpAgreements as $agreement) {
            $result = $this->calculatePenaltiesForAgreement('hire_purchase', $agreement->id);
            if ($result['penalties_created'] > 0) {
                $hpResults[] = $agreement->id;
            }
        }
        
        // Process Gentleman agreements
        $gentlemanAgreements = GentlemanAgreement::whereIn('status', ['approved', 'active'])->get();
        $gentlemanResults = [];
        
        foreach ($gentlemanAgreements as $agreement) {
            $result = $this->calculatePenaltiesForAgreement('gentleman', $agreement->id);
            if ($result['penalties_created'] > 0) {
                $gentlemanResults[] = $agreement->id;
            }
        }
        
        Log::info('Daily penalty calculation completed', [
            'hp_agreements_processed' => count($hpResults),
            'gentleman_agreements_processed' => count($gentlemanResults)
        ]);
        
        return [
            'hire_purchase' => $hpResults,
            'gentleman' => $gentlemanResults
        ];
    }
}