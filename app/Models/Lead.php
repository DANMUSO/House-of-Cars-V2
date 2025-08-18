<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_model',
        'client_name',
        'client_phone',
        'client_email',
        'purchase_type',
        'client_budget',
        'follow_up_required',
        'status',
        'salesperson_id',
        'commitment_amount',
        'notes'
    ];

    protected $casts = [
        'client_budget' => 'decimal:2',
        'commitment_amount' => 'decimal:2', // Added this cast
        'follow_up_required' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // FIXED: Changed from users() to user() for belongsTo relationship
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    // Keep both for compatibility if needed
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'Closed');
    }

    public function scopeUnsuccessful($query)
    {
        return $query->where('status', 'Unsuccessful');
    }

    public function scopeRequiresFollowUp($query)
    {
        return $query->where('follow_up_required', true);
    }

    public function scopeBySalesperson($query, $salespersonId)
    {
        return $query->where('salesperson_id', $salespersonId);
    }

    // Accessor for formatted budget
    public function getFormattedBudgetAttribute()
    {
        return 'KES ' . number_format($this->client_budget, 2);
    }

    // Accessor for formatted commitment amount
    public function getFormattedCommitmentAttribute()
    {
        return 'KES ' . number_format($this->commitment_amount, 2);
    }

    // IMPROVED: Static methods for statistics - matches your Blade view expectations
    public static function getStatistics($salespersonId = null)
    {
        $query = self::query();
        
        if ($salespersonId) {
            $query->where('salesperson_id', $salespersonId);
        }

        $totalLeads = $query->count();
        $closedLeads = $query->clone()->where('status', 'Closed')->count();

        return [
            // Match the keys expected by your Blade template
            'active' => $query->clone()->where('status', 'Active')->count(),
            'closed' => $closedLeads,
            'unsuccessful' => $query->clone()->where('status', 'Unsuccessful')->count(),
            'follow_up' => $query->clone()->where('follow_up_required', true)->count(),
            'finance' => $query->clone()->where('purchase_type', 'Finance')->count(),
            'cash' => $query->clone()->where('purchase_type', 'Cash')->count(),
            'total_budget' => $query->clone()->sum('client_budget') ?? 0,
            'avg_budget' => $query->clone()->avg('client_budget') ?? 0,
            'total_leads' => $totalLeads,
            'conversion_rate' => $totalLeads > 0 ? 
                round(($closedLeads / $totalLeads) * 100, 1) : 0
        ];
    }
}