<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Penalty extends Model
{
    protected $fillable = [
        'agreement_type',
        'agreement_id',
        'payment_schedule_id',
        'expected_amount',
        'penalty_rate',
        'penalty_amount',
        'due_date',
        'days_overdue',
        'status',
        'amount_paid',
        'date_paid',
        'payment_reference',
        'notes',
        'created_by',
        'waived_by',
        'waived_at',
        'waiver_reason'
    ];

    protected $casts = [
        'expected_amount' => 'decimal:2',
        'penalty_rate' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
        'date_paid' => 'date',
        'waived_at' => 'datetime',
    ];

    // Relationships
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

    public function paymentSchedule()
    {
        return $this->belongsTo(PaymentSchedule::class);
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

    public function scopeForAgreement($query, $agreementType, $agreementId)
    {
        return $query->where('agreement_type', $agreementType)
                     ->where('agreement_id', $agreementId);
    }

    // Mutators & Accessors
    public function getOutstandingAmountAttribute()
    {
        return $this->penalty_amount - $this->amount_paid;
    }

    public function getIsFullyPaidAttribute()
    {
        return $this->amount_paid >= $this->penalty_amount;
    }

    // Methods
    public function markAsPaid($amount, $paymentDate = null, $reference = null)
    {
        $this->update([
            'amount_paid' => min($amount, $this->penalty_amount),
            'status' => $amount >= $this->penalty_amount ? 'paid' : 'pending',
            'date_paid' => $paymentDate ?? now(),
            'payment_reference' => $reference,
        ]);
    }

    public function waive($reason = null, $waivedBy = null)
    {
        $this->update([
            'status' => 'waived',
            'waived_by' => $waivedBy ?? auth()->id(),
            'waived_at' => now(),
            'waiver_reason' => $reason,
        ]);
    }

    public static function calculatePenaltyAmount($expectedAmount, $penaltyRate = 10.00)
    {
        return ($expectedAmount * $penaltyRate) / 100;
    }
}
