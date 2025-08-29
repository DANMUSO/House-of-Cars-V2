<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenaltyPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'penalty_id',
        'amount',
        'payment_date',
        'payment_method',
        'payment_reference',
        'notes',
        'recorded_by',
        'is_verified',
        'verified_by',
        'verified_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function penalty()
    {
        return $this->belongsTo(Penalty::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeForPenalty($query, $penaltyId)
    {
        return $query->where('penalty_id', $penaltyId);
    }
}