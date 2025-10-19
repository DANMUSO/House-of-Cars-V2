<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InCash;
use App\Models\GentlemanAgreement;
use App\Models\HirePurchaseAgreement;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendHolidayMessages extends Command
{
    protected $signature = 'holiday:send-messages 
                            {--holiday= : Specific holiday (mazingira, christmas, newyear, easter, jamhuri, madaraka, mashujaa, labor)}
                            {--message= : Custom message to send}
                            {--dry-run : Preview recipients without sending}';
    
    protected $description = 'Send holiday greetings to all clients and staff members';

    private $recipients = [];
    private $successCount = 0;
    private $failureCount = 0;

    // Pre-defined holiday messages
    private $holidayMessages = [
        'mazingira' => "As we celebrate this special day dedicated to caring for our environment, we at House of Cars extend our warm wishes to you and your loved ones.\nWe'll be open today to serve you, visit us for the best car deals and exceptional service.\nHappy Mazingira Day!",
        
        'christmas' => "Merry Christmas from House of Cars! Wishing you and your loved ones joy, peace, and blessings this festive season. We appreciate your continued trust in us. Enjoy the holidays!",
        
        'newyear' => "Happy New Year from House of Cars! Thank you for being part of our journey. Wishing you success, prosperity, and safe travels in the year ahead. Cheers to new beginnings!",
        
        'easter' => "Happy Easter from House of Cars! May this season bring you renewed hope, joy, and blessings. We appreciate your continued partnership with us. Have a blessed Easter!",
        
        'jamhuri' => "Happy Jamhuri Day! As we celebrate Kenya's Independence, House of Cars wishes you a day filled with pride and joy. Thank you for choosing us for your automotive needs!",
        
        'madaraka' => "Happy Madaraka Day from House of Cars! Celebrating Kenya's journey to self-governance. We're proud to serve you and grateful for your trust. Enjoy the celebrations!",
        
        'mashujaa' => "Happy Mashujaa Day! House of Cars honors all heroes who have shaped our nation. Thank you for your continued support. Have a memorable celebration!",
        
        'labor' => "Happy Labour Day from House of Cars! We celebrate your hard work and dedication. Enjoy this well-deserved rest. Thank you for being part of our family!",
    ];

    // Holiday dates (month-day format)
    private $holidayDates = [
        'mazingira' => '10-10',    // October 10
        'christmas' => '12-25',    // December 25
        'newyear' => '01-01',      // January 1
        'easter' => 'varies',      // Easter varies each year
        'jamhuri' => '12-12',      // December 12
        'madaraka' => '06-01',     // June 1
        'mashujaa' => '10-20',     // October 20
        'labor' => '05-01',        // May 1
    ];

    public function handle()
    {
        $this->info('🎉 Starting Holiday Messages Campaign...');
        $this->newLine();
        
        // Determine which holiday message to send
        $holiday = $this->option('holiday');
        $customMessage = $this->option('message');
        $isDryRun = $this->option('dry-run');
        
        // Get the message to send
        $message = $this->getHolidayMessage($holiday, $customMessage);
        
        if (!$message) {
            $this->error('❌ No message specified. Use --message option or --holiday option.');
            return 1;
        }
        
        $this->info("📝 Message to send:");
        $this->line("─────────────────────────────────────");
        $this->line($message);
        $this->line("─────────────────────────────────────");
        $this->newLine();
        
        // Collect all recipients
        $this->info('📞 Collecting recipients...');
        $this->collectInCashClients();
        $this->collectGentlemanAgreementClients();
        $this->collectHirePurchaseClients();
        $this->collectStaffMembers();
        
        // Remove duplicates and clean phone numbers
        $this->recipients = array_unique($this->recipients);
        $this->recipients = array_filter($this->recipients); // Remove empty values
        
        $totalRecipients = count($this->recipients);
        
        $this->newLine();
        $this->info("✅ Total unique recipients: {$totalRecipients}");
        $this->newLine();
        
        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No messages will be sent');
            $this->displayRecipientsSample();
            return 0;
        }
        
        if (!$this->confirm('Do you want to proceed with sending messages?', true)) {
            $this->warn('Operation cancelled.');
            return 0;
        }
        
        // Send messages
        $this->sendMessages($message);
        
        // Display summary
        $this->displaySummary();
        
        return 0;
    }

    private function getHolidayMessage($holiday, $customMessage)
    {
        if ($customMessage) {
            return $customMessage;
        }
        
        if ($holiday && isset($this->holidayMessages[$holiday])) {
            return $this->holidayMessages[$holiday];
        }
        
        // Auto-detect holiday based on today's date
        $today = Carbon::today()->format('m-d');
        foreach ($this->holidayDates as $holidayName => $date) {
            if ($date === $today) {
                $this->info("🎯 Auto-detected holiday: " . ucfirst($holidayName));
                return $this->holidayMessages[$holidayName];
            }
        }
        
        return null;
    }

    private function collectInCashClients()
    {
        $this->info('Collecting InCash clients...');
        
        $inCashClients = InCash::all();
        
        foreach ($inCashClients as $client) {
            // Add primary phone as-is from database
            if (!empty($client->Phone_No)) {
                $this->recipients[] = $client->Phone_No;
            }
            
            // Add alternate phone as-is from database
            if (!empty($client->phone_numberalt)) {
                $this->recipients[] = $client->phone_numberalt;
            }
        }
        
        $this->line("  → {$inCashClients->count()} InCash clients processed");
    }

    private function collectGentlemanAgreementClients()
    {
        $this->info('Collecting Gentleman Agreement clients...');
        
        $gentlemanClients = GentlemanAgreement::all();
        
        foreach ($gentlemanClients as $client) {
            // Add primary phone as-is from database
            if (!empty($client->phone_number)) {
                $this->recipients[] = $client->phone_number;
            }
            
            // Add alternate phone as-is from database
            if (!empty($client->phone_numberalt)) {
                $this->recipients[] = $client->phone_numberalt;
            }
        }
        
        $this->line("  → {$gentlemanClients->count()} Gentleman Agreement clients processed");
    }

    private function collectHirePurchaseClients()
    {
        $this->info('Collecting Hire Purchase clients...');
        
        $hireClients = HirePurchaseAgreement::all();
        
        foreach ($hireClients as $client) {
            // Add primary phone as-is from database
            if (!empty($client->phone_number)) {
                $this->recipients[] = $client->phone_number;
            }
            
            // Add alternate phone as-is from database
            if (!empty($client->phone_numberalt)) {
                $this->recipients[] = $client->phone_numberalt;
            }
        }
        
        $this->line("  → {$hireClients->count()} Hire Purchase clients processed");
    }

    private function collectStaffMembers()
    {
        $this->info('Collecting staff members...');
        
        $staffMembers = User::whereIn('role', ['Managing-Director', 'Accountant', 'General-Manager'])
            ->whereNotNull('phone')
            ->get();
        
        foreach ($staffMembers as $staff) {
            // Add staff phone as-is from database
            if (!empty($staff->phone)) {
                $this->recipients[] = $staff->phone;
            }
        }
        
        $this->line("  → {$staffMembers->count()} staff members processed");
    }

    private function sendMessages($message)
    {
        $this->info('📤 Sending messages...');
        
        $progressBar = $this->output->createProgressBar(count($this->recipients));
        $progressBar->start();
        
        foreach ($this->recipients as $phone) {
            try {
                $smsSent = SmsService::send($phone, $message);
                
                if ($smsSent) {
                    $this->successCount++;
                    Log::info('Holiday message sent', [
                        'phone' => $phone,
                        'status' => 'success'
                    ]);
                } else {
                    $this->failureCount++;
                    Log::warning('Holiday message failed', [
                        'phone' => $phone,
                        'status' => 'failed'
                    ]);
                }
            } catch (\Exception $e) {
                $this->failureCount++;
                Log::error('Error sending holiday message', [
                    'phone' => $phone,
                    'error' => $e->getMessage()
                ]);
            }
            
            $progressBar->advance();
            
            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second delay
        }
        
        $progressBar->finish();
        $this->newLine(2);
    }

    private function displayRecipientsSample()
    {
        $this->newLine();
        $this->info('Sample recipients (first 10):');
        $sample = array_slice($this->recipients, 0, 10);
        foreach ($sample as $index => $phone) {
            $this->line(($index + 1) . ". " . $phone);
        }
        
        if (count($this->recipients) > 10) {
            $this->line("... and " . (count($this->recipients) - 10) . " more");
        }
    }

    private function displaySummary()
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('           CAMPAIGN SUMMARY            ');
        $this->info('═══════════════════════════════════════');
        $this->line("Total Recipients:  " . count($this->recipients));
        $this->line("✅ Successful:      {$this->successCount}");
        $this->line("❌ Failed:          {$this->failureCount}");
        $this->info('═══════════════════════════════════════');
        
        if ($this->successCount > 0) {
            $this->info('🎉 Holiday messages sent successfully!');
        }
        
        if ($this->failureCount > 0) {
            $this->warn("⚠️  Some messages failed. Check logs for details.");
        }
    }
}