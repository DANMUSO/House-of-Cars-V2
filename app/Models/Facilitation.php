<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facilitation extends Model
{
    use HasFactory;

    protected $table = 'facilitations';

    protected $fillable = [
        'id',
        'request',
        'amount',
        'status',
        'request_id',
    ];
    public function requester()
        {
            return $this->belongsTo(User::class, 'request_id');
        }

}
