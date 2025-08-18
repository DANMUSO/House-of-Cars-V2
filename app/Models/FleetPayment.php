<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetPayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'fleet_acquisition_id',
        'payment_amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'balance_before',
        'balance_after',
        'payment_number',
        'notes',
        'status',
        'processed_by'
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relationships
    public function fleetAcquisition()
    {
        return $this->belongsTo(FleetAcquisition::class);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return 'KSh ' . number_format($this->payment_amount, 2);
    }

    public function getFormattedBalanceAfterAttribute()
    {
        return 'KSh ' . number_format($this->balance_after, 2);
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeForFleet($query, $fleetId)
    {
        return $query->where('fleet_acquisition_id', $fleetId);
    }
}
