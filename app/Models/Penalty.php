<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Penalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'agreement_type',
        'agreement_id', 
        'payment_schedule_id',
        'installment_number',           // NEW: Added for cumulative calculation
        'expected_amount',              // ADD THIS LINE
        'penalty_rate',
        'penalty_amount',
        'cumulative_unpaid_amount',     // NEW: Added for cumulative calculation  
        'penalty_sequence',             // NEW: Added for cumulative calculation
        'due_date',
        'days_overdue',
        'status',
        'amount_paid',
        'date_paid',
        'payment_reference',
        'notes',
        'created_by',                   // Keep your existing field name
        'waived_by',
        'waived_at',
        'waiver_reason'
    ];

    protected $casts = [
        'due_date' => 'date',
        'date_paid' => 'date',
        'waived_at' => 'datetime',
        'penalty_amount' => 'decimal:2',
        'cumulative_unpaid_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'penalty_rate' => 'decimal:2',
        'days_overdue' => 'integer',
        'penalty_sequence' => 'integer',
        'installment_number' => 'integer'
    ];

    // Relationships
    public function paymentSchedule()
    {
        return $this->belongsTo(PaymentSchedule::class);
    }

    public function hirePurchaseAgreement()
    {
        return $this->belongsTo(HirePurchaseAgreement::class, 'agreement_id')
            ->where('agreement_type', 'hire_purchase');
    }

    public function gentlemanAgreement()
    {
        return $this->belongsTo(GentlemanAgreement::class, 'agreement_id')
            ->where('agreement_type', 'gentleman');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function waivedBy()
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    // Scopes
    public function scopeForAgreement($query, $agreementType, $agreementId)
    {
        return $query->where('agreement_type', $agreementType)
                     ->where('agreement_id', $agreementId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeWaived($query)
    {
        return $query->where('status', 'waived');
    }

    public function scopeOverdue($query)
    {
        return $query->where('days_overdue', '>', 0);
    }

    // Accessors
    public function getOutstandingAmountAttribute()
    {
        return $this->penalty_amount - $this->amount_paid;
    }

    public function getIsFullyPaidAttribute()
    {
        return $this->amount_paid >= $this->penalty_amount;
    }

    public function getIsPartiallyPaidAttribute()
    {
        return $this->amount_paid > 0 && $this->amount_paid < $this->penalty_amount;
    }

    public function getFormattedPenaltyAmountAttribute()
    {
        return 'KSh ' . number_format($this->penalty_amount, 2);
    }

    public function getFormattedOutstandingAttribute()
    {
        return 'KSh ' . number_format($this->outstanding_amount, 2);
    }

    public function getFormattedCumulativeUnpaidAttribute()
    {
        return 'KSh ' . number_format($this->cumulative_unpaid_amount, 2);
    }

    public function getPenaltyDescriptionAttribute()
    {
        return "Penalty #{$this->penalty_sequence} for installment #{$this->installment_number} - " .
               "Cumulative unpaid: {$this->formatted_cumulative_unpaid}";
    }

    public function getCalculationExplanationAttribute()
    {
        $rate = $this->penalty_rate ?? 10;
        return "Penalty #{$this->penalty_sequence}: " .
               "KSh " . number_format($this->cumulative_unpaid_amount, 2) . 
               " × {$rate}% = " .
               "KSh " . number_format($this->penalty_amount, 2);
    }

    // Methods
    public function waive($reason, $waivedBy = null)
    {
        $this->update([
            'status' => 'waived',
            'waiver_reason' => $reason,
            'waived_by' => $waivedBy ?? auth()->id(),
            'waived_at' => now()
        ]);

        Log::info("Penalty {$this->id} waived", [
            'installment_number' => $this->installment_number,
            'penalty_amount' => $this->penalty_amount,
            'reason' => $reason
        ]);
    }

    public function recordPayment($amount, $paymentDate, $reference = null)
    {
        if ($amount <= 0 || $amount > $this->outstanding_amount) {
            throw new \InvalidArgumentException('Invalid payment amount');
        }

        $newAmountPaid = $this->amount_paid + $amount;
        $newStatus = ($newAmountPaid >= $this->penalty_amount) ? 'paid' : 'pending';

        $this->update([
            'amount_paid' => $newAmountPaid,
            'status' => $newStatus,
            'date_paid' => $newStatus === 'paid' ? $paymentDate : $this->date_paid,
            'payment_reference' => $reference
        ]);

        Log::info("Payment recorded for penalty {$this->id}", [
            'amount' => $amount,
            'new_amount_paid' => $newAmountPaid,
            'new_status' => $newStatus
        ]);

        return $this;
    }

    /**
     * Calculate penalty amount based on cumulative unpaid and rate
     */
    public static function calculatePenaltyAmount($cumulativeUnpaidAmount, $penaltyRate = 10)
    {
        return $cumulativeUnpaidAmount * ($penaltyRate / 100);
    }

    /**
     * Static method to create cumulative penalty
     */
    public static function createCumulativePenalty(
        $agreementType,
        $agreementId, 
        $paymentScheduleId,
        $installmentNumber,
        $dueDate,
        $cumulativeUnpaidAmount,
        $penaltySequence,
        $penaltyRate = 10
    ) {
        $penaltyAmount = self::calculatePenaltyAmount($cumulativeUnpaidAmount, $penaltyRate);
        $daysOverdue = now()->diffInDays(\Carbon\Carbon::parse($dueDate));

        return self::create([
            'agreement_type' => $agreementType,
            'agreement_id' => $agreementId,
            'payment_schedule_id' => $paymentScheduleId,
            'installment_number' => $installmentNumber,
            'penalty_rate' => $penaltyRate,
            'penalty_amount' => $penaltyAmount,
            'cumulative_unpaid_amount' => $cumulativeUnpaidAmount,
            'penalty_sequence' => $penaltySequence,
            'due_date' => $dueDate,
            'days_overdue' => $daysOverdue,
            'status' => 'pending',
            'amount_paid' => 0,
            'created_by' => auth()->id() ?? 1
        ]);
    }
}