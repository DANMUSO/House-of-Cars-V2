<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    use HasFactory;
    protected $table="installments";
    protected $fillable = [
        'hire_purchase_id',
        'amount',
        'due_date',
        'status',
    ];

    // Each installment belongs to a hire purchase
    public function hirePurchase()
    {
        return $this->belongsTo(HirePurchase::class);
    }

}
