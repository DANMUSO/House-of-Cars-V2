<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FleetAcquisition;
use App\Models\User;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendFleetPaymentReminders extends Command
{
    protected $signature = 'fleet:send-reminders';
    protected $description = 'Send payment and insurance reminders for fleet acquisitions';

    public function handle()
    {
        $this->info('Starting fleet reminders...');
        
        $this->sendPaymentReminders();
        $this->sendInsuranceReminders();
        
        $this->info('Fleet reminders completed!');
    }

    private function sendPaymentReminders()
    {
        $activeFleets = FleetAcquisition::whereIn('status', ['active', 'approved'])
            ->where('outstanding_balance', '>', 0)
            ->get();

        foreach ($activeFleets as $fleet) {
            // Calculate next payment date based on first payment date
            $nextPaymentDate = $this->calculateNextPaymentDate($fleet);
            
            if (!$nextPaymentDate) continue;

            $today = Carbon::today();
            $threeDaysBefore = Carbon::parse($nextPaymentDate)->subDays(3);
            $paymentDate = Carbon::parse($nextPaymentDate);

            // Send 3 days before reminder
            if ($today->isSameDay($threeDaysBefore)) {
                $this->sendPaymentReminderNotification($fleet, $nextPaymentDate, '3 days');
            }

            // Send on payment day reminder
            if ($today->isSameDay($paymentDate)) {
                $this->sendPaymentReminderNotification($fleet, $nextPaymentDate, 'today');
            }
        }
    }

    private function sendInsuranceReminders()
    {
        $fleets = FleetAcquisition::whereIn('status', ['active', 'approved', 'completed'])
            ->whereNotNull('insurance_expiry_date')
            ->get();

        foreach ($fleets as $fleet) {
            $today = Carbon::today();
            $expiryDate = Carbon::parse($fleet->insurance_expiry_date);
            $threeDaysBefore = $expiryDate->copy()->subDays(3);

            // Send 3 days before expiry
            if ($today->isSameDay($threeDaysBefore)) {
                $this->sendInsuranceReminderNotification($fleet, $fleet->insurance_expiry_date, '3 days');
            }

            // Send on expiry day
            if ($today->isSameDay($expiryDate)) {
                $this->sendInsuranceReminderNotification($fleet, $fleet->insurance_expiry_date, 'today');
            }
        }
    }

    private function calculateNextPaymentDate($fleet)
    {
        $firstPaymentDate = Carbon::parse($fleet->first_payment_date);
        $today = Carbon::today();
        
        // Calculate how many months have passed since first payment
        $monthsPassed = $firstPaymentDate->diffInMonths($today);
        
        // Calculate next payment date
        $nextPaymentDate = $firstPaymentDate->copy()->addMonths($monthsPassed + 1);
        
        // If we've reached the end of the loan term, return null
        if ($monthsPassed >= $fleet->loan_duration_months) {
            return null;
        }
        
        return $nextPaymentDate;
    }

    private function sendPaymentReminderNotification($fleet, $paymentDate, $timing)
    {
        try {
            // Get Accountant and Managing-Director users
            $recipients = User::whereIn('role', ['Accountant', 'Managing-Director'])
                ->whereNotNull('phone')
                ->get();

            $vehicleInfo = "{$fleet->vehicle_make} {$fleet->vehicle_model} ({$fleet->registration_number})";
            $amount = number_format($fleet->monthly_installment, 2);
            $formattedDate = Carbon::parse($paymentDate)->format('d M Y');
            
            if ($timing === 'today') {
                $message = "REMINDER: Fleet payment DUE TODAY for {$vehicleInfo}. Amount: KES {$amount}. Outstanding: KES " . number_format($fleet->outstanding_balance, 2);
            } else {
                $message = "REMINDER: Fleet payment due in {$timing} ({$formattedDate}) for {$vehicleInfo}. Amount: KES {$amount}. Outstanding: KES " . number_format($fleet->outstanding_balance, 2);
            }

            foreach ($recipients as $recipient) {
                $smsSent = SmsService::send($recipient->phone, $message);
                
                if ($smsSent) {
                    Log::info('Fleet payment reminder sent', [
                        'fleet_id' => $fleet->id,
                        'vehicle' => $vehicleInfo,
                        'recipient' => $recipient->first_name . ' ' . $recipient->last_name,
                        'timing' => $timing
                    ]);
                } else {
                    Log::warning('Fleet payment reminder failed', [
                        'fleet_id' => $fleet->id,
                        'recipient_phone' => $recipient->phone
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error sending fleet payment reminder: ' . $e->getMessage());
        }
    }

    private function sendInsuranceReminderNotification($fleet, $expiryDate, $timing)
    {
        try {
            // Get Accountant and Managing-Director users
            $recipients = User::whereIn('role', ['Accountant', 'Managing-Director'])
                ->whereNotNull('phone')
                ->get();

            $vehicleInfo = "{$fleet->vehicle_make} {$fleet->vehicle_model} ({$fleet->registration_number})";
            $formattedDate = Carbon::parse($expiryDate)->format('d M Y');
            $policyNumber = $fleet->insurance_policy_number ?? 'N/A';
            
            if ($timing === 'today') {
                $message = "URGENT: Insurance EXPIRES TODAY for {$vehicleInfo}. Policy: {$policyNumber}. Renew immediately!";
            } else {
                $message = "ALERT: Insurance expires in {$timing} ({$formattedDate}) for {$vehicleInfo}. Policy: {$policyNumber}. Take action!";
            }

            foreach ($recipients as $recipient) {
                $smsSent = SmsService::send($recipient->phone, $message);
                
                if ($smsSent) {
                    Log::info('Fleet insurance reminder sent', [
                        'fleet_id' => $fleet->id,
                        'vehicle' => $vehicleInfo,
                        'recipient' => $recipient->first_name . ' ' . $recipient->last_name,
                        'timing' => $timing
                    ]);
                } else {
                    Log::warning('Fleet insurance reminder failed', [
                        'fleet_id' => $fleet->id,
                        'recipient_phone' => $recipient->phone
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error sending fleet insurance reminder: ' . $e->getMessage());
        }
    }
}