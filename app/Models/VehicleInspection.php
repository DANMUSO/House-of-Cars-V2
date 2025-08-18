<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; 
class VehicleInspection extends Model
{
    use HasFactory;
    protected $fillable = [
        'rh_front_wing', 'rh_right_wing', 'lh_front_wing', 'lh_right_wing', 'bonnet',
        'rh_front_door', 'rh_rear_door', 'lh_front_door', 'lh_rear_door',
        'front_bumper', 'rear_bumper', 'head_lights', 'bumper_lights', 'corner_lights', 'rear_lights',
        'radio_speakers', 'seat_belt', 'door_handles',
        'head_rest', 'floor_carpets', 'rubber_mats', 'cigar_lighter', 'boot_mats',
        'jack', 'spare_wheel', 'compressor', 'wheel_spanner',
        'overall_percent',
        'exterior_percent',
        'interior_func_percent',
        'interior_acc_percent',
        'tools_percent',
        'customer_id',
        'imported_id',
        'current_mileage',
        'inspection_notes',
         'photos',
        'status'
    ];
    // Cast photos as array
    protected $casts = [
        'photos' => 'array'
    ];
    // Helper method to get photos with full URLs and indexes
    public function getPhotosWithUrls()
    {
        if (!$this->photos) {
            return [];
        }

        return collect($this->photos)->map(function ($photoPath, $index) {
            return [
                'index' => $index,
                'url' => Storage::url($photoPath),
                'path' => $photoPath,
                'name' => basename($photoPath)
            ];
        })->values()->toArray();
    }

    // Helper method to count photos
    public function getPhotosCountAttribute()
    {
        return $this->photos ? count($this->photos) : 0;
    }
    public function customerVehicle()
        {
            return $this->belongsTo(CustomerVehicle::class, 'customer_id', 'id');
        }
    public function carsImport()
        {
            return $this->belongsTo(CarImport::class, 'imported_id', 'id');
        }
}
