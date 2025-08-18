<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetAcquisition extends Model
{
    use HasFactory;

    protected $fillable = [
        // Vehicle Information
        'vehicle_make',
        'vehicle_model',
        'vehicle_year',
        'engine_capacity',
        'chassis_number',
        'engine_number',
        'registration_number',
        'vehicle_category',
        'purchase_price',
        'market_value',
        'vehicle_photos',
        
        // Financial Details
        'down_payment',
        'monthly_installment',
        'interest_rate',
        'total_interest',
        'loan_duration_months',
        'total_amount_payable',
        'first_payment_date',
        'insurance_premium',
        
        // Legal & Compliance
        'hp_agreement_number',
        'logbook_custody',
        'insurance_policy_number',
        'insurance_company',
        'insurance_expiry_date',
        'company_kra_pin',
        'business_permit_number',
        
        // Vendor/Financier Information
        'financing_institution',
        'financier_contact_person',
        'financier_phone',
        'financier_email',
        'financier_agreement_ref',
        
        // Status and Tracking
        'status',
        'amount_paid',
        'outstanding_balance',
        'payments_made',
        'completion_date'
    ];

    protected $casts = [
        'vehicle_year' => 'integer',
        'purchase_price' => 'decimal:2',
        'market_value' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_interest' => 'decimal:2',
        'total_amount_payable' => 'decimal:2',
        'insurance_premium' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'first_payment_date' => 'date',
        'insurance_expiry_date' => 'date',
        'completion_date' => 'date',
        'vehicle_photos' => 'array',
    ];

    // Accessors
    public function getVehicleFullNameAttribute()
    {
        return "{$this->vehicle_make} {$this->vehicle_model} ({$this->vehicle_year})";
    }

    public function getPaidPercentageAttribute()
    {
        if ($this->total_amount_payable > 0) {
            return round(($this->amount_paid / $this->total_amount_payable) * 100, 2);
        }
        return 0;
    }

    public function getRemainingPaymentsAttribute()
    {
        return $this->loan_duration_months - $this->payments_made;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByInstitution($query, $institution)
    {
        return $query->where('financing_institution', $institution);
    }

    // Methods
    public function calculateOutstandingBalance()
    {
        return $this->total_amount_payable - $this->amount_paid;
    }

    public function updatePayment($amount)
    {
        $this->amount_paid += $amount;
        $this->outstanding_balance = $this->calculateOutstandingBalance();
        $this->payments_made += 1;
        
        if ($this->outstanding_balance <= 0) {
            $this->status = 'completed';
            $this->completion_date = now();
        }
        
        $this->save();
    }

    // Relationships
    public function payments()
    {
        return $this->hasMany(FleetPayment::class);
    }

    public function confirmedPayments()
    {
        return $this->hasMany(FleetPayment::class)->where('status', 'confirmed');
    }
}
