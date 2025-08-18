<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'leave_day_id',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'handover_person',
        'reason',
        'status',
        'applied_date',
        'approved_by',
        'approved_date',
        'cancelled_date',
        'comments',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'applied_date' => 'datetime',
        'approved_date' => 'datetime',
        'cancelled_date' => 'datetime',
        'total_days' => 'integer',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'applied_date',
        'approved_date',
        'cancelled_date',
        'deleted_at',
    ];

    /**
     * Get the user who applied for leave
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the leave day record this application is associated with
     */
    public function leaveDay()
    {
        return $this->belongsTo(LeaveDay::class);
    }

    /**
     * Get the user who approved/rejected the application
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for pending applications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope for approved applications
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    /**
     * Scope for rejected applications
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'Rejected');
    }

    /**
     * Scope for cancelled applications
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'Cancelled');
    }

    /**
     * Scope for current year applications
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereYear('applied_date', date('Y'));
    }

    /**
     * Get formatted status with color coding
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'Pending' => 'warning',
            'Approved' => 'success',
            'Rejected' => 'danger',
            'Cancelled' => 'secondary',
        ];

        return $colors[$this->status] ?? 'primary';
    }

    /**
     * Get formatted date range
     */
    public function getDateRangeAttribute()
    {
        return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
    }

    /**
     * Check if application can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['Pending', 'Approved']) && 
               $this->start_date->isFuture();
    }

    /**
     * Check if application can be approved/rejected
     */
    public function canBeProcessed()
    {
        return $this->status === 'Pending';
    }

    /**
     * Get duration in human readable format
     */
    public function getDurationAttribute()
    {
        if ($this->total_days == 1) {
            return '1 day';
        }
        return $this->total_days . ' days';
    }
}