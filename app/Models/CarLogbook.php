<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CarLogbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'imported_id',
        'title',
        'description',
        'document_type',
        'documents',
        'document_date',
        'issued_by',
        'reference_number',
        'status',
        'notes',
        'expiry_date',
        'file_count',
        'file_size'
    ];

    protected $casts = [
        'documents' => 'array',
        'document_date' => 'datetime',
        'expiry_date' => 'datetime',
    ];

    /**
     * Relationship with CarImport (imported cars)
     */
    public function carsImport()
    {
        return $this->belongsTo(CarImport::class, 'imported_id');
    }

    /**
     * Relationship with CustomerVehicle (trade-in cars)
     */
    public function customerVehicle()
    {
        return $this->belongsTo(CustomerVehicle::class, 'customer_id');
    }

    /**
     * Get the car details regardless of type
     */
    public function getCarAttribute()
    {
        if ($this->customer_id > 0) {
            return $this->customerVehicle;
        } elseif ($this->imported_id > 0) {
            return $this->carsImport;
        }
        return null;
    }

    /**
     * Get car type (imported or trade-in)
     */
    public function getCarTypeAttribute()
    {
        if ($this->customer_id > 0) {
            return 'trade-in';
        } elseif ($this->imported_id > 0) {
            return 'imported';
        }
        return 'unknown';
    }

    /**
     * Get formatted car details
     */
    public function getCarDetailsAttribute()
    {
        $car = $this->car;
        if (!$car) return 'No car details';

        if ($this->car_type === 'trade-in') {
            return "{$car->make} {$car->model} ({$car->year}) - {$car->registration_number}";
        } else {
            return "{$car->make} {$car->model} ({$car->year}) - {$car->chassis_number}";
        }
    }

    /**
     * Check if document is expired or expiring soon
     */
    public function getExpiryStatusAttribute()
    {
        if (!$this->expiry_date) {
            return 'no_expiry';
        }

        $now = Carbon::now();
        $expiryDate = Carbon::parse($this->expiry_date);
        
        if ($expiryDate->isPast()) {
            return 'expired';
        } elseif ($expiryDate->diffInDays($now) <= 30) {
            return 'expiring_soon';
        }
        
        return 'valid';
    }

    /**
     * Get expiry status badge class
     */
    public function getExpiryBadgeClassAttribute()
    {
        switch ($this->expiry_status) {
            case 'expired':
                return 'bg-danger';
            case 'expiring_soon':
                return 'bg-warning';
            case 'valid':
                return 'bg-success';
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) return 'Unknown';
        return $this->file_size;
    }

    /**
     * Scope for active logbooks
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for expired documents
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', Carbon::now());
    }

    /**
     * Scope for expiring soon documents
     */
    public function scopeExpiringSoon($query)
    {
        return $query->where('expiry_date', '>', Carbon::now())
                    ->where('expiry_date', '<=', Carbon::now()->addDays(30));
    }

    /**
     * Scope for imported cars
     */
    public function scopeImported($query)
    {
        return $query->where('imported_id', '!=', 0);
    }

    /**
     * Scope for trade-in cars
     */
    public function scopeTradeIn($query)
    {
        return $query->where('customer_id', '!=', 0);
    }

    /**
     * Get document count
     */
    public function getDocumentCountAttribute()
    {
        return is_array($this->documents) ? count($this->documents) : 0;
    }

    /**
     * Check if logbook has documents
     */
    public function hasDocuments()
    {
        return $this->document_count > 0;
    }

    /**
     * Format document type for display
     */
    public function getFormattedDocumentTypeAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->document_type));
    }
}