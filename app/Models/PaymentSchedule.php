<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PaymentSchedule extends Model
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

    // Existing Scopes
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

    // Additional Scopes for Rescheduling Feature
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending', 'partial', 'overdue']);
    }

    // Existing Accessors
    public function getIsOverdueAttribute()
    {
        return $this->due_date < today() && $this->status === 'pending';
    }

    // Additional Accessors for Rescheduling Feature
    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->amount_paid;
    }

    public function getPaymentProgressAttribute()
    {
        return $this->total_amount > 0 ? ($this->amount_paid / $this->total_amount) * 100 : 0;
    }

    public function getIsFullyPaidAttribute()
    {
        return $this->status === 'paid';
    }

    public function getIsPartiallyPaidAttribute()
    {
        return $this->status === 'partial';
    }

    // Methods for Rescheduling Feature
    public function updateOverdueStatus()
    {
        if ($this->due_date < today() && in_array($this->status, ['pending', 'partial'])) {
            $this->update([
                'status' => $this->status === 'pending' ? 'overdue' : $this->status,
                'days_overdue' => now()->diffInDays($this->due_date)
            ]);
        }
    }

    public function markAsPaid($paymentDate = null, $amount = null)
    {
        $paymentDate = $paymentDate ?: today();
        $amount = $amount ?: $this->total_amount;

        if ($amount >= $this->remaining_amount) {
            // Full payment
            $this->update([
                'amount_paid' => $this->total_amount,
                'status' => 'paid',
                'date_paid' => $paymentDate,
                'days_overdue' => 0
            ]);
        } else {
            // Partial payment
            $this->update([
                'amount_paid' => $this->amount_paid + $amount,
                'status' => 'partial',
                'date_paid' => $paymentDate
            ]);
        }

        return $this;
    }

    public function applyPayment($amount, $paymentDate = null)
    {
        $paymentDate = $paymentDate ?: today();
        $remainingAmount = $this->remaining_amount;
        
        if ($amount <= 0 || $remainingAmount <= 0) {
            return 0; // No payment applied
        }

        $appliedAmount = min($amount, $remainingAmount);
        $newAmountPaid = $this->amount_paid + $appliedAmount;

        // Determine new status
        $newStatus = 'partial';
        if ($newAmountPaid >= $this->total_amount) {
            $newStatus = 'paid';
            $newAmountPaid = $this->total_amount; // Ensure we don't overpay
        }

        $this->update([
            'amount_paid' => $newAmountPaid,
            'status' => $newStatus,
            'date_paid' => $paymentDate,
            'days_overdue' => $newStatus === 'paid' ? 0 : $this->days_overdue
        ]);

        return $appliedAmount;
    }

    // Static methods for bulk operations
    public static function updateOverdueStatusForAgreement($agreementId)
    {
        $today = today();
        
        // Update pending to overdue
        static::where('agreement_id', $agreementId)
            ->where('due_date', '<', $today)
            ->where('status', 'pending')
            ->update([
                'status' => 'overdue',
                'days_overdue' => \DB::raw("DATEDIFF('$today', due_date)")
            ]);

        // Update days overdue for already overdue
        static::where('agreement_id', $agreementId)
            ->where('status', 'overdue')
            ->update([
                'days_overdue' => \DB::raw("DATEDIFF('$today', due_date)")
            ]);
    }

    public static function getScheduleSummary($agreementId)
    {
        return static::where('agreement_id', $agreementId)
            ->selectRaw('
                status,
                COUNT(*) as count,
                SUM(total_amount) as total_amount,
                SUM(amount_paid) as amount_paid,
                SUM(total_amount - COALESCE(amount_paid, 0)) as amount_due
            ')
            ->groupBy('status')
            ->get();
    }
}