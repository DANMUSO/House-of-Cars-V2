<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HirePurchase extends Model
{
    use HasFactory;
    protected $table="hire_purchases";
    protected $fillable = [
        'Client_Name',
        'Phone_No',
        'email',
        'KRA',
        'National_ID',
        'Amount',
        'deposit',
        'duration',
        'paid_percentage',
        'car_type',
        'car_id',
        'first_due_date',
        'last_due_date',
        'imported_id', 
        'customer_id',
    ];
    public function carImport()
    {
        return $this->belongsTo(\App\Models\CarImport::class, 'imported_id');
    }
    
    public function customerVehicle()
    {
        return $this->belongsTo(\App\Models\CustomerVehicle::class, 'customer_id');
    }
    public function installments()
    {
        return $this->hasMany(Installment::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
