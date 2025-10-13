<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentSchedule;
use App\Models\HirePurchaseAgreement;
use App\Models\GentlemanAgreement;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendInstallmentReminders extends Command
{
    protected $signature = 'installments:notify';
    protected $description = 'Send SMS notifications to Accountant and General Manager for upcoming and due installments';

    public function handle()
    {
        $today = Carbon::today();
        $threeDaysFromNow = Carbon::today()->addDays(3);

        // Get installments due in 3 days or today
        $upcomingPayments = PaymentSchedule::whereIn('status',  ['pending', 'partial', 'overdue'])
            ->where(function($query) use ($today, $threeDaysFromNow) {
                $query->whereDate('due_date', $threeDaysFromNow)
                      ->orWhereDate('due_date', $today);
            })
            ->get();

        if ($upcomingPayments->isEmpty()) {
            $this->info('No upcoming installments found.');
            return 0;
        }

        // Get recipient phone numbers from users table
        $recipients = $this->getRecipientPhones();
        
        if (empty($recipients)) {
            $this->error('No recipients found (Accountant/General-Manager).');
            Log::error('SMS Notification failed: No recipients configured');
            return 1;
        }

        $summary = $this->generateSummary($upcomingPayments, $today, $threeDaysFromNow);

        // Send SMS to all recipients
        foreach ($recipients as $role => $phone) {
            try {
                $message = "Dear {$role},\n\n{$summary}\n\n- House of Cars";
                
                $sent = SmsService::send($phone, $message);
                
                if ($sent) {
                    $this->info("✓ SMS sent to {$role}: {$phone}");
                    Log::info("Installment reminder sent to {$role}", ['phone' => $phone]);
                } else {
                    $this->error("✗ Failed to send SMS to {$role}");
                    Log::error("Failed to send installment reminder to {$role}");
                }
            } catch (\Exception $e) {
                $this->error("Error sending to {$role}: " . $e->getMessage());
                Log::error("SMS error for {$role}: " . $e->getMessage());
            }
        }

        $this->info("Processed {$upcomingPayments->count()} installment notifications.");
        return 0;
    }

    /**
     * Get Accountant and General-Manager phone numbers from users table
     */
    private function getRecipientPhones()
    {
        $recipients = [];

        // Get Accountant phone (exact role name)
        $accountant = DB::table('users')
            ->where('role', 'Accountant')
            ->whereNotNull('phone')
            ->first();
        
        if ($accountant && !empty($accountant->phone)) {
            $recipients['Accountant'] = $accountant->phone;
        }

        // Get General-Manager phone (exact role name with hyphen)
       /*  $generalManager = DB::table('users')
            ->where('role', 'General-Manager')
            ->whereNotNull('phone_no')
            ->first();

        if ($generalManager && !empty($generalManager->phone)) {
            $recipients['General Manager'] = $generalManager->phone;
        } */

        return $recipients;
    }

    /**
     * Generate summary message
     */
    private function generateSummary($payments, $today, $threeDaysFromNow)
    {
        $dueIn3Days = [];
        $dueToday = [];

        foreach ($payments as $payment) {
            $dueDate = Carbon::parse($payment->due_date);
            $agreement = $this->getAgreementDetails($payment->agreement_id);
            
            if (!$agreement) continue;

            $remainingAmount = $payment->total_amount - ($payment->amount_paid ?? 0);
            $item = "{$agreement['client_name']} - {$agreement['vehicle']} - KSh " . 
                    number_format($remainingAmount, 2);

            if ($dueDate->isSameDay($threeDaysFromNow)) {
                $dueIn3Days[] = $item;
            } elseif ($dueDate->isSameDay($today)) {
                $dueToday[] = $item;
            }
        }

        $summary = "INSTALLMENT REMINDERS\n" . date('d M Y') . "\n\n";

        if (!empty($dueIn3Days)) {
            $summary .= "DUE IN 3 DAYS (" . date('d M', strtotime('+3 days')) . "):\n";
            $summary .= implode("\n", $dueIn3Days) . "\n\n";
        }

        if (!empty($dueToday)) {
            $summary .= "DUE TODAY:\n";
            $summary .= implode("\n", $dueToday);
        }

        return trim($summary);
    }

    /**
 * Get agreement details with smart table detection
 */
private function getAgreementDetails($agreementId)
{
    // Smart detection based on ID pattern
    // Hire Purchase: 1000xxx, Gentleman Agreement: 500xxx
    
    if ($agreementId >= 1000000) {
        // Check Hire Purchase first (more likely)
        $hp = HirePurchaseAgreement::find($agreementId);
        if ($hp) {
            return [
                'client_name' => $hp->client_name,
                'vehicle' => trim("{$hp->vehicle_make} {$hp->model}"),
                'type' => 'HP'
            ];
        }
    } else {
        // Check Gentleman Agreement first (ID < 1000000)
        $ga = GentlemanAgreement::find($agreementId);
        if ($ga) {
            return [
                'client_name' => $ga->client_name,
                'vehicle' => trim("{$ga->vehicle_make} {$ga->model}"),
                'type' => 'GA'
            ];
        }
    }
    
    // Fallback: Check the other table
    if ($agreementId >= 1000000) {
        $ga = GentlemanAgreement::find($agreementId);
        if ($ga) {
            return [
                'client_name' => $ga->client_name,
                'vehicle' => trim("{$ga->vehicle_make} {$ga->model}"),
                'type' => 'GA'
            ];
        }
    } else {
        $hp = HirePurchaseAgreement::find($agreementId);
        if ($hp) {
            return [
                'client_name' => $hp->client_name,
                'vehicle' => trim("{$hp->vehicle_make} {$hp->model}"),
                'type' => 'HP'
            ];
        }
    }

    Log::warning('Agreement NOT FOUND:', ['agreement_id' => $agreementId]);
    return null;
}
}