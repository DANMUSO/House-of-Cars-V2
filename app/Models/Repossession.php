<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repossession extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'agreement_id',
        'agreement_type',
        'repossession_date',
        'remaining_balance',
        'total_penalties',
        'repossession_expenses',
        'car_value',
        'expected_sale_price',
        'actual_sale_price',
        'sale_date',
        'status',
        'repossession_reason',
        'vehicle_condition',
        'repossession_notes',
        'storage_location',
        'repossessed_by',
        'sold_by'
    ];

    protected $casts = [
        'repossession_date' => 'date',
        'sale_date' => 'date',
        'remaining_balance' => 'decimal:2',
        'total_penalties' => 'decimal:2',
        'repossession_expenses' => 'decimal:2',
        'car_value' => 'decimal:2',
        'expected_sale_price' => 'decimal:2',
        'actual_sale_price' => 'decimal:2',
    ];

    // Relationships
    public function agreement()
    {
        if ($this->agreement_type === 'hire_purchase') {
            return $this->belongsTo(HirePurchaseAgreement::class, 'agreement_id');
        }
        return $this->belongsTo(GentlemanAgreement::class, 'agreement_id');
    }

    public function repossessedBy()
    {
        return $this->belongsTo(User::class, 'repossessed_by');
    }

    public function soldBy()
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    // Calculate loss/profit on sale
    public function calculateSaleResult()
    {
        if (!$this->actual_sale_price) {
            return null;
        }

        return $this->actual_sale_price - $this->car_value;
    }

    // Calculate expected loss/profit
    public function calculateExpectedResult()
    {
        if (!$this->expected_sale_price) {
            return null;
        }

        return $this->expected_sale_price - $this->car_value;
    }
}