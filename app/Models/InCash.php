<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InCash extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'Client_Name',
        'Phone_No',
        'email',
        'KRA',
        'National_ID',
        'Amount',
        'car_type',
        'car_id',
        'imported_id', 
        'customer_id',
        'paid_amount',
        'phone_numberalt',
        'emailalt',
        'tradeinnamount',
        'totalpaidamount'
    ];
    public function carImport()
    {
        return $this->belongsTo(\App\Models\CarImport::class, 'imported_id');
    }
    
    public function customerVehicle()
    {
        return $this->belongsTo(\App\Models\CustomerVehicle::class, 'customer_id');
    }
    // Relationship with AgreementFile
    public function agreementFiles()
    {
        return $this->hasMany(AgreementFile::class, 'agreement_id');
    }

}
