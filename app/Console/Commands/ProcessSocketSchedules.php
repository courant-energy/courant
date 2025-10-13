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
            $result = $dryRun ? $this->simulateScheduleApplication($profile) : $profile->applySchedule();
            $socketsCount = $profile->sockets->count();

            if ($socketsCount === 0) {
                $this->line("Profile '{$profile->name}': No sockets linked");

                continue;
            }

            if ($result['action'] === 'none') {
                $this->line("Profile '{$profile->name}': No schedule action needed - {$result['reason']}");

                continue;
            }

            if (! $dryRun) {
                $totalUpdated += $result['updated'];
                $this->info("Profile '{$profile->name}': Applied {$result['action']} schedule - updated {$result['updated']}/{$socketsCount} sockets");
                $this->line("  Scheduled time: {$result['scheduled_time']}, Applied at: {$result['applied_at']}");
            } else {
                $this->info("Profile '{$profile->name}': Would apply {$result['action']} schedule - affecting {$result['sockets_to_update']}/{$socketsCount} sockets");
                $this->line("  Scheduled time: {$result['scheduled_time']}");
            }

            // Show next schedule change
            $nextChange = $profile->getNextScheduleChange();
            if ($nextChange) {
                $this->line("  Next change: {$nextChange['action']} at {$nextChange['time']->format('Y-m-d H:i')} ({$nextChange['human_readable']})");
            }
        }

        if (! $dryRun) {
            $this->info("Completed! Updated {$totalUpdated} sockets across {$totalProfiles} profiles.");
        } else {
            $this->info('Dry run completed. Use without --dry-run to apply changes.');
        }
    }

    /**
     * Simulate schedule application for dry-run mode
     */
    private function simulateScheduleApplication(Profile $profile): array
    {
        $result = ['action' => 'none', 'reason' => 'no_schedule_due', 'sockets_to_update' => 0];

        // Check if we should apply turn ON
        if ($profile->shouldApplyTurnOn()) {
            $socketsToUpdate = $profile->sockets->where('state', false)->count();
            $result = [
                'action' => 'turn_on',
                'reason' => 'scheduled_turn_on',
                'sockets_to_update' => $socketsToUpdate,
                'scheduled_time' => $profile->turn_on_time->format('H:i'),
            ];
        }
        // Check if we should apply turn OFF
        elseif ($profile->shouldApplyTurnOff()) {
            $socketsToUpdate = $profile->sockets->where('state', true)->count();
            $result = [
                'action' => 'turn_off',
                'reason' => 'scheduled_turn_off',
                'sockets_to_update' => $socketsToUpdate,
                'scheduled_time' => $profile->turn_off_time->format('H:i'),
            ];
        }

        return $result;
    }
}
