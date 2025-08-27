<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
class HirePurchaseAgreement extends Model
{
    protected $fillable = [
        'client_name', 'phone_number', 'email', 'national_id', 'kra_pin', 'address',
        'car_type', 'car_id', 'imported_id', 'customer_id',
        'vehicle_make', 'vehicle_model', 'vehicle_year', 'vehicle_plate', 'chassis_number',
        'vehicle_price', 'deposit_amount', 'loan_amount', 'interest_rate', 
        'duration_months', 'monthly_payment', 'total_interest', 'total_amount',
        'amount_paid', 'outstanding_balance', 'payment_progress', 'payments_made', 
        'payments_remaining',
        'agreement_date', 'first_due_date', 'last_payment_date', 'expected_completion_date',
        'status', 'is_overdue', 'overdue_days', 'notes',
        'approved_by', 'approved_at','phone_numberalt','emailalt'
    ];

    protected $casts = [
        'vehicle_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'loan_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'total_interest' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'payment_progress' => 'decimal:2',
        'agreement_date' => 'date',
        'first_due_date' => 'date',
        'last_payment_date' => 'date',
        'expected_completion_date' => 'date',
        'approved_at' => 'datetime',
        'is_overdue' => 'boolean',
    ];

     // Relationship with AgreementFile
    public function agreementFiles()
    {
        return $this->hasMany(AgreementFile::class, 'agreement_id');
    }
    // Relationships
    public function paymentSchedule()
{
    return $this->hasMany(PaymentSchedule::class, 'agreement_id');
}

public function reschedulingHistory()
{
    return $this->hasMany(LoanReschedulingHistory::class, 'agreement_id');
}

public function activeReschedulings()
{
    return $this->hasMany(LoanReschedulingHistory::class, 'agreement_id')->where('status', 'active');
}

public function getIsRescheduledAttribute()
{
    return $this->reschedulingHistory()->where('status', 'active')->exists();
}

public function getTotalLumpSumPaymentsAttribute()
{
    return $this->payments()->where('is_lump_sum', true)->sum('amount');
}

public function getReschedulingCountAttribute()
{
    return $this->reschedulingHistory()->where('status', 'active')->count();
}

    public function payments(): HasMany
    {
        return $this->hasMany(HirePurchasePayment::class, 'agreement_id');
    }

   

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Vehicle Relationships
    public function carImport(): BelongsTo
    {
        return $this->belongsTo(CarImport::class, 'imported_id');
    }

    public function customerVehicle(): BelongsTo
    {
        return $this->belongsTo(CustomerVehicle::class, 'customer_id');
    }

    // Dynamic relationship based on car_type
    public function vehicle()
    {
        if ($this->car_type === 'import') {
            return $this->carImport();
        } elseif ($this->car_type === 'customer') {
            return $this->customerVehicle();
        }
        return null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['approved', 'active']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_overdue', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Accessors
    public function getDepositPercentageAttribute()
    {
        return $this->vehicle_price > 0 ? ($this->deposit_amount / $this->vehicle_price) * 100 : 0;
    }

    public function getNextPaymentDateAttribute()
    {
        $lastPayment = $this->payments()->latest('payment_date')->first();
        if ($lastPayment) {
            return Carbon::parse($lastPayment->payment_date)->addMonth();
        }
        return $this->first_due_date;
    }

    public function getIsDefaultedAttribute()
    {
        return $this->overdue_days > 90; // Consider defaulted after 90 days
    }

    public function getDaysUntilNextPaymentAttribute()
    {
        return Carbon::now()->diffInDays($this->next_payment_date, false);
    }

    // Methods
    public function calculatePaymentProgress()
    {
        if ($this->total_amount > 0) {
            $totalPaid = $this->deposit_amount + $this->amount_paid;
            $this->payment_progress = ($totalPaid / $this->total_amount) * 100;
            $this->save();
        }
    }

    public function updateOverdueStatus()
    {
        $gracePeriod = LoanSetting::getValue('grace_period_days', 7);
        $nextPaymentDate = $this->next_payment_date;
        
        if (Carbon::now()->gt($nextPaymentDate->addDays($gracePeriod))) {
            $this->is_overdue = true;
            $this->overdue_days = Carbon::now()->diffInDays($nextPaymentDate);
            $this->save();
        }
    }

    public function generatePaymentSchedule()
    {
        // Clear existing schedule
        $this->paymentSchedule()->delete();
        
        $balance = $this->loan_amount;
        $monthlyPrincipal = $this->loan_amount / $this->duration_months;
        $monthlyInterest = ($this->loan_amount * $this->interest_rate) / 100;
        
        for ($i = 1; $i <= $this->duration_months; $i++) {
            $dueDate = Carbon::parse($this->first_due_date)->addMonths($i - 1);
            $principalAmount = min($monthlyPrincipal, $balance);
            $balance -= $principalAmount;
            
            PaymentSchedule::create([
                'agreement_id' => $this->id,
                'installment_number' => $i,
                'due_date' => $dueDate,
                'principal_amount' => $principalAmount,
                'interest_amount' => $monthlyInterest,
                'total_amount' => $principalAmount + $monthlyInterest,
                'balance_after' => $balance,
                'status' => 'pending'
            ]);
        }
    }

    public function recordPayment($amount, $paymentDate, $paymentMethod, $reference = null, $notes = null)
    {
        $balanceBefore = $this->outstanding_balance;
        
        $payment = HirePurchasePayment::create([
            'agreement_id' => $this->id,
            'amount' => $amount,
            'payment_date' => $paymentDate,
            'payment_method' => $paymentMethod,
            'reference_number' => $reference,
            'notes' => $notes,
            'payment_type' => 'regular',
            'payment_number' => $this->payments_made + 1,
            'recorded_by' => auth()->id(),
            'recorded_at' => now(),
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceBefore - $amount,
        ]);

        // Update agreement
        $this->amount_paid += $amount;
        $this->outstanding_balance -= $amount;
        $this->payments_made += 1;
        $this->payments_remaining -= 1;
        $this->last_payment_date = $paymentDate;
        
        if ($this->outstanding_balance <= 0) {
            $this->status = 'completed';
            $this->is_overdue = false;
            $this->overdue_days = 0;
        }
        
        $this->calculatePaymentProgress();
        $this->save();

        return $payment;
    }
}
