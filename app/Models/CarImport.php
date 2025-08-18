<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'bidder_name',
        'make',
        'model',
        'year',
        'vin',
        'engine_type',
        'body_type',
        'mileage',
        'bid_amount',
        'bid_start_date',
        'bid_end_date',
        'deposit',
        'photos',
        'fullamount',
        'status',
    ];

    protected $casts = [
        'photos' => 'array', // Automatically casts the JSON field to array
        'bid_start_date' => 'date',
        'bid_end_date' => 'date',
    ];
    public function vehicleInspection()
    {
        return $this->hasOne(VehicleInspection::class, 'imported_id', 'id');
    }
    public function incash()
    {
        return $this->belongsTo(InCash::class, 'car_id', 'id');
    }
}
