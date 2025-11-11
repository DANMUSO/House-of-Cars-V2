<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

class BulkSms extends Model
{
    use HasFactory;

    protected $table = 'bulk_sms';

    protected $fillable = [
        'message',
        'recipients',
        'target_group',
        'sent_by',
        'total_sent',
        'total_failed',
        'status'
    ];

    protected $casts = [
        'recipients' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    // Send SMS to all recipients
    public function send()
    {
        $this->update(['status' => 'processing']);
        
        $sent = 0;
        $failed = 0;
        
        foreach ($this->recipients as $phone) {
            try {
                $formattedPhone = SmsService::formatPhone($phone);
                $result = SmsService::send($formattedPhone, $this->message);
                
                if ($result) {
                    $sent++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                Log::error("Bulk SMS failed for {$phone}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->update([
            'total_sent' => $sent,
            'total_failed' => $failed,
            'status' => $failed === 0 ? 'completed' : 'failed'
        ]);

        return $sent > 0;
    }

    // Get recipients based on target group
    public static function getRecipientsByGroup(string $group): array
    {
        return match($group) {
            'all' => collect([
                ...Lead::whereNotNull('client_phone')->pluck('client_phone')->toArray(),
                ...HirePurchaseAgreement::whereNotNull('phone_number')->pluck('phone_number')->toArray(),
                ...GentlemanAgreement::whereNotNull('phone_number')->pluck('phone_number')->toArray()
            ])
            ->filter()
            ->unique()
            ->values()
            ->toArray(),
            
            'leads' => Lead::whereNotNull('client_phone')
                ->pluck('client_phone')
                ->filter()
                ->unique()
                ->values()
                ->toArray(),
            
            'hire_purchase' => HirePurchaseAgreement::whereNotNull('phone_number')
                ->pluck('phone_number')
                ->filter()
                ->unique()
                ->values()
                ->toArray(),
            
            'gentleman' => GentlemanAgreement::whereNotNull('phone_number')
                ->pluck('phone_number')
                ->filter()
                ->unique()
                ->values()
                ->toArray(),
            
            default => []
        };
    }
}