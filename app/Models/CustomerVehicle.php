<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class CustomerVehicle extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'customer_name',
        'phone_no',
        'email',
        'vehicle_make',
        'model',
        'chasis_no',
        'status',
        'number_plate',
        'minimum_price',
        'sell_type',
        'photos',
        'colour',
        'engine_no',
        'engine_capacity',
        'transmission',
        'national_id'
    ];
    public function vehicleInspection()
        {
            return $this->hasOne(VehicleInspection::class, 'customer_id', 'id');
        }
    public function incash()
        {
            return $this->belongsTo(CarImport::class, 'car_id', 'id');
        }
}
