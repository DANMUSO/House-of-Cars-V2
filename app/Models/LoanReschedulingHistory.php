<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanReschedulingHistory extends Model
{
    use HasFactory;

    protected $table = 'loan_rescheduling_history';

    protected $fillable = [
        'agreement_id',
        'payment_id',
        'reschedule_type',
        'lump_sum_amount',
        'outstanding_before',
        'outstanding_after',
        'previous_duration_months',
        'previous_monthly_payment',
        'previous_completion_date',
        'new_duration_months',
        'new_monthly_payment',
        'new_completion_date',
        'duration_change_months',
        'payment_change_amount',
        'total_interest_savings',
        'rescheduling_date',
        'processed_by',
        'notes',
        'status',
    ];

    protected $casts = [
        'lump_sum_amount' => 'decimal:2',
        'outstanding_before' => 'decimal:2',
        'outstanding_after' => 'decimal:2',
        'previous_monthly_payment' => 'decimal:2',
        'new_monthly_payment' => 'decimal:2',
        'payment_change_amount' => 'decimal:2',
        'total_interest_savings' => 'decimal:2',
        'rescheduling_date' => 'date',
        'previous_completion_date' => 'date',
        'new_completion_date' => 'date',
    ];

    /**
     * Get the hire purchase agreement
     */
    public function agreement(): BelongsTo
    {
        return $this->belongsTo(HirePurchaseAgreement::class, 'agreement_id');
    }

    /**
     * Get the associated payment
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(HirePurchasePayment::class, 'payment_id');
    }

    /**
     * Get the user who processed the rescheduling
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope for active reschedulings
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for duration reductions
     */
    public function scopeDurationReductions($query)
    {
        return $query->where('reschedule_type', 'reduce_duration');
    }

    /**
     * Scope for payment reductions
     */
    public function scopePaymentReductions($query)
    {
        return $query->where('reschedule_type', 'reduce_installment');
    }

    /**
     * Get formatted savings message
     */
    public function getSavingsMessageAttribute()
    {
        if ($this->reschedule_type === 'reduce_duration') {
            return "Loan duration reduced by {$this->duration_change_months} months";
        } else {
            return "Monthly payment reduced by KSh " . number_format($this->payment_change_amount, 2);
        }
    }

    /**
     * Get the total benefit amount
     */
    public function getTotalBenefitAttribute()
    {
        if ($this->reschedule_type === 'reduce_duration') {
            return $this->total_interest_savings;
        } else {
            // Calculate total savings over remaining duration
            return $this->payment_change_amount * $this->new_duration_months;
        }
    }
}
