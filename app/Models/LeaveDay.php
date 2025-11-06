<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'total_days',
        'used_days',
        'remaining_days',
        'year',
        'status',
        'total_hours',
    'used_hours',
    'remaining_hours',
    ];

    protected $casts = [
        'total_days' => 'integer',
        'used_days' => 'integer',
        'remaining_days' => 'integer',
        'year' => 'integer',
    ];

    /**
     * Get the user that owns the leave days
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update remaining days when used days change
     */
    public function updateRemainingDays(): void
    {
        $this->remaining_days = $this->total_days - $this->used_days;
        $this->save();
    }

    /**
     * Check if user has enough leave days
     */
    public function hasEnoughDays(int $requestedDays): bool
    {
        return $this->remaining_days >= $requestedDays;
    }

    /**
     * Use leave days
     */
    public function useDays(int $days): bool
    {
        if ($this->hasEnoughDays($days)) {
            $this->used_days += $days;
            $this->updateRemainingDays();
            return true;
        }
        return false;
    }

    /**
     * Scope for active leave days
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for current year
     */
    public function scopeCurrentYear($query)
    {
        return $query->where('year', date('Y'));
    }
}
