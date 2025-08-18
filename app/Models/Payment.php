<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    use HasFactory;
    protected $table="payments";
    protected $fillable = [
        'hire_purchase_id',
        'amount',
        'status',
    ];

    // Each installment belongs to a hire purchase
    public function hirePurchase()
    {
        return $this->belongsTo(HirePurchase::class);
    }
}
