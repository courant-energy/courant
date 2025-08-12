<?php

namespace App\Console\Commands;

use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestScheduleAtTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:schedule-at-time {time : Time in H:i format} {--date= : Date in Y-m-d format (default: today)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test schedule processing at a specific time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $time = $this->argument('time');
        $date = $this->option('date') ?: now()->format('Y-m-d');
        
        try {
            $testDateTime = Carbon::parse("$date $time");
        } catch (\Exception $e) {
            $this->error("Invalid date/time format: $date $time");
            return;
        }

        $this->info("Testing schedule processing at: {$testDateTime->format('Y-m-d H:i:s')} ({$testDateTime->format('l')})");
        
        // Set test time
        Carbon::setTestNow($testDateTime);
        
        $profiles = Profile::where('scheduling_enabled', true)
            ->whereNotNull('turn_on_time')
            ->whereNotNull('turn_off_time')
            ->with('sockets')
            ->get();

        if ($profiles->isEmpty()) {
            $this->info('No profiles with scheduling enabled found.');
            return;
        }

        $this->newLine();
        $this->info('Schedule Analysis:');
        $this->line(str_repeat('-', 50));

        foreach ($profiles as $profile) {
            $this->line("Profile: {$profile->name}");
            $this->line("  Schedule: {$profile->turn_on_time->format('H:i')} to {$profile->turn_off_time->format('H:i')}");
            $this->line("  Days: " . implode(', ', $profile->schedule_days ?: ['All days']));
            
            $shouldBeOn = $profile->shouldBeOn();
            $shouldApplyTurnOn = $profile->shouldApplyTurnOn();
            $shouldApplyTurnOff = $profile->shouldApplyTurnOff();
            
            $this->line("  Should be ON: " . ($shouldBeOn ? 'YES' : 'NO'));
            $this->line("  Should apply turn ON: " . ($shouldApplyTurnOn ? 'YES' : 'NO'));
            $this->line("  Should apply turn OFF: " . ($shouldApplyTurnOff ? 'YES' : 'NO'));
            
            $lastTurnOn = $profile->last_turn_on_applied;
            $lastTurnOff = $profile->last_turn_off_applied;
            
            $this->line("  Last turn ON applied: " . ($lastTurnOn ? $lastTurnOn->format('Y-m-d H:i:s') : 'Never'));
            $this->line("  Last turn OFF applied: " . ($lastTurnOff ? $lastTurnOff->format('Y-m-d H:i:s') : 'Never'));
            
            $socketsCount = $profile->sockets->count();
            $this->line("  Linked sockets: {$socketsCount}");
            
            if ($socketsCount > 0) {
                foreach ($profile->sockets as $socket) {
                    $this->line("    - {$socket->name}: " . ($socket->state ? 'ON' : 'OFF'));
                }
            }
            
            $this->newLine();
        }

        $this->info('Simulating schedule application...');
        $this->line(str_repeat('-', 50));

        foreach ($profiles as $profile) {
            $result = $profile->applySchedule();
            
            $this->line("Profile '{$profile->name}':");
            $this->line("  Action: {$result['action']}");
            $this->line("  Reason: {$result['reason']}");
            
            if ($result['action'] !== 'none') {
                $this->info("  ✓ Applied {$result['action']} - Updated {$result['updated']} sockets");
                $this->line("  Scheduled time: {$result['scheduled_time']}");
                $this->line("  Applied at: {$result['applied_at']}");
            } else {
                $this->line("  ○ No action taken");
            }
            
            $this->newLine();
        }

        // Reset test time
        Carbon::setTestNow();
        $this->info('Test completed. Time reset to current time.');
    }
}
