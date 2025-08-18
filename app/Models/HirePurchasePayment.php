<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HirePurchasePayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'agreement_id', 'amount', 'payment_date', 'payment_method', 'reference_number',
        'notes', 'payment_type', 'penalty_amount', 'payment_number',
        'recorded_by', 'recorded_at', 'is_verified', 'verified_by', 'verified_at',
        'balance_before', 'balance_after'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'payment_date' => 'date',
        'recorded_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    // Relationships
    public function rescheduling()
{
    return $this->hasOne(LoanReschedulingHistory::class, 'payment_id');
}

public function scopeLumpSum($query)
{
    return $query->where('is_lump_sum', true);
}

public function getIsLumpSumPaymentAttribute()
{
    return $this->is_lump_sum;
}
    public function agreement(): BelongsTo
    {
        return $this->belongsTo(HirePurchaseAgreement::class, 'agreement_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year);
    }
}
