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
    'receipt_documents', 'receipt_count', 'receipt_file_size'
    ];
    // In app/Models/Facilitation.php
protected $casts = [
    'receipt_documents' => 'array',
];
    public function requester()
        {
            return $this->belongsTo(User::class, 'request_id');
        }
        public function getReceiptUrlAttribute()
{
    if (empty($this->receipt_documents)) {
        return null;
    }
    
    $receiptPath = $this->receipt_documents[0]; // Get first receipt
    
    if (str_starts_with($receiptPath, 'http')) {
        return $receiptPath;
    }
    
    $bucket = config('filesystems.disks.s3.bucket');
    $region = config('filesystems.disks.s3.region');
    return "https://{$bucket}.s3.{$region}.amazonaws.com/{$receiptPath}";
}

public function getReceiptUploadedAtAttribute()
{
    // Since we don't have receipt_uploaded_at in DB, use updated_at when receipt exists
    return !empty($this->receipt_documents) ? $this->updated_at : null;
}

}
