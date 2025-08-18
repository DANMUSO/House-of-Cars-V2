<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'agreement_id', 'installment_number', 'due_date', 'principal_amount',
        'interest_amount', 'total_amount', 'balance_after', 'status',
        'amount_paid', 'date_paid', 'days_overdue'
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
        'date_paid' => 'date',
    ];

    // Relationships
    public function agreement(): BelongsTo
    {
        return $this->belongsTo(HirePurchaseAgreement::class, 'agreement_id');
    }

    // Scopes
    public function scopeDueToday($query)
    {
        return $query->where('due_date', today())->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', today())->where('status', 'pending');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return $this->due_date < today() && $this->status === 'pending';
    }
}
