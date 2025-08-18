<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agreementfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'agreement_id',
        'agreement_path',
        'agreement_type'
    ];

    protected $casts = [
        'agreement_id' => 'integer',
    ];

     // Relationship with GentlemanAgreement
    public function gentlemanAgreement()
    {
        return $this->belongsTo(GentlemanAgreement::class, 'agreement_id');
    }

    // Relationship with HirePurchaseAgreement
    public function hirePurchaseAgreement()
    {
        return $this->belongsTo(HirePurchaseAgreement::class, 'agreement_id');
    }

    // Relationship with InCash
    public function inCash()
    {
        return $this->belongsTo(InCash::class, 'agreement_id');
    }
}
