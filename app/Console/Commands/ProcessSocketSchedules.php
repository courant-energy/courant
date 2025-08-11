<?php

namespace App\Console\Commands;

use App\Models\Profile;
use Illuminate\Console\Command;

class ProcessSocketSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:process {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process socket schedules and update socket states based on profile scheduling settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in dry-run mode - no changes will be made');
        }

        $this->info('Processing socket schedules...');

        $profiles = Profile::where('scheduling_enabled', true)
            ->whereNotNull('turn_on_time')
            ->whereNotNull('turn_off_time')
            ->with('sockets')
            ->get();

        if ($profiles->isEmpty()) {
            $this->info('No profiles with scheduling enabled found.');
            return;
        }

        $totalUpdated = 0;
        $totalProfiles = $profiles->count();

        foreach ($profiles as $profile) {
            $shouldBeOn = $profile->shouldBeOn();
            $socketsCount = $profile->sockets->count();
            
            if ($socketsCount === 0) {
                $this->line("Profile '{$profile->name}': No sockets linked");
                continue;
            }

            $socketsToUpdate = $profile->sockets->filter(function ($socket) use ($shouldBeOn) {
                return $socket->state != $shouldBeOn;
            });

            if ($socketsToUpdate->isEmpty()) {
                $this->line("Profile '{$profile->name}': All {$socketsCount} sockets already in correct state (" . ($shouldBeOn ? 'ON' : 'OFF') . ")");
                continue;
            }

            if (!$dryRun) {
                $updated = $profile->applySchedule();
                $totalUpdated += $updated;
                $this->info("Profile '{$profile->name}': Updated {$updated}/{$socketsCount} sockets to " . ($shouldBeOn ? 'ON' : 'OFF'));
            } else {
                $this->info("Profile '{$profile->name}': Would update {$socketsToUpdate->count()}/{$socketsCount} sockets to " . ($shouldBeOn ? 'ON' : 'OFF'));
            }

            // Show next schedule change
            $nextChange = $profile->getNextScheduleChange();
            if ($nextChange) {
                $this->line("  Next change: {$nextChange['action']} at {$nextChange['time']->format('Y-m-d H:i')} ({$nextChange['human_readable']})");
            }
        }

        if (!$dryRun) {
            $this->info("Completed! Updated {$totalUpdated} sockets across {$totalProfiles} profiles.");
        } else {
            $this->info("Dry run completed. Use without --dry-run to apply changes.");
        }
    }
}
