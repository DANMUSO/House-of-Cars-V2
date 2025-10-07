<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    
    protected function schedule(Schedule $schedule)
    {
        // Run fleet reminders daily at 8:00 AM
        $schedule->command('fleet:send-reminders')
                ->dailyAt('08:00')
                ->timezone('Africa/Nairobi');

        // Mazingira Day - October 10th at 8:00 AM
        $schedule->command('holiday:send-messages --holiday=mazingira')
                 ->yearlyOn(10, 10, '08:00')
                 ->timezone('Africa/Nairobi');
        
        // Mashujaa Day - October 20th at 8:00 AM
        $schedule->command('holiday:send-messages --holiday=mashujaa')
                 ->yearlyOn(10, 20, '08:00')
                 ->timezone('Africa/Nairobi');
        
        // Jamhuri Day - December 12th at 8:00 AM
        $schedule->command('holiday:send-messages --holiday=jamhuri')
                 ->yearlyOn(12, 12, '08:00')
                 ->timezone('Africa/Nairobi');
        
        // Christmas - December 25th at 8:00 AM
        $schedule->command('holiday:send-messages --holiday=christmas')
                 ->yearlyOn(12, 25, '08:00')
                 ->timezone('Africa/Nairobi');
        
        // New Year - January 1st at 8:00 AM
        $schedule->command('holiday:send-messages --holiday=newyear')
                 ->yearlyOn(1, 1, '08:00')
                 ->timezone('Africa/Nairobi');
        
        // Labour Day - May 1st at 8:00 AM
        $schedule->command('holiday:send-messages --holiday=labor')
                 ->yearlyOn(5, 1, '08:00')
                 ->timezone('Africa/Nairobi');
        
        // Madaraka Day - June 1st at 8:00 AM
        $schedule->command('holiday:send-messages --holiday=madaraka')
                 ->yearlyOn(6, 1, '08:00')
                 ->timezone('Africa/Nairobi');
        
        // Note: Easter varies each year, so it needs to be sent manually
        // or you can calculate Easter date dynamically
        
        
        // ============================================
        // OTHER EXISTING SCHEDULES (if any)
        // ============================================
        
        // Fleet payment reminders (if you have this)
        // $schedule->command('fleet:send-reminders')->daily();
    }
    

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
