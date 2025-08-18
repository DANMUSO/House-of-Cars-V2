<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'national_id',
        'address',
        'profile_picture',
        'role',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function leads(): HasMany
{
    return $this->hasMany(Lead::class, 'salesperson_id');
}
    public function facilitationRequests()
    {
        return $this->hasMany(Facilitation::class, 'request_id');
    }

    public function hasRole($role)
    {
        return strtolower($this->role) === strtolower($role);
    }
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    /**
     * Get the leave days for the user
     */
    public function leaveDays(): HasMany
    {
        return $this->hasMany(LeaveDay::class);
    }

    /**
     * Get active leave days for current year
     */
    public function currentYearLeaveDays(): HasMany
    {
        return $this->hasMany(LeaveDay::class)
                    ->where('year', date('Y'))
                    ->where('status', 'active');
    }

    /**
     * Check if user is a client
     */
    public function isClient(): bool
    {
        return in_array($this->role, ['Client', 'client']);
    }

    /**
     * Get total remaining leave days for current year
     */
    public function getTotalRemainingLeaveDays(): int
    {
        return $this->currentYearLeaveDays()->sum('remaining_days');
    }

    /**
     * Get leave days by type for current year
     */
    public function getLeaveByType(string $leaveType): ?LeaveDay
    {
        return $this->currentYearLeaveDays()
                    ->where('leave_type', $leaveType)
                    ->first();
    }
    
}
