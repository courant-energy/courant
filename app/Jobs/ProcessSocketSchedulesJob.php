<?php

namespace App\Jobs;

use App\Models\Profile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessSocketSchedulesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing socket schedules job started');

        $profiles = Profile::where('scheduling_enabled', true)
            ->whereNotNull('turn_on_time')
            ->whereNotNull('turn_off_time')
            ->with('sockets')
            ->get();

        if ($profiles->isEmpty()) {
            Log::info('No profiles with scheduling enabled found');

            return;
        }

        $totalUpdated = 0;
        $schedulesApplied = 0;

        foreach ($profiles as $profile) {
            try {
                $result = $profile->applySchedule();

                if ($result['action'] !== 'none') {
                    $schedulesApplied++;
                    $totalUpdated += $result['updated'];

                    Log::info("Profile '{$profile->name}': {$result['action']} schedule applied", [
                        'sockets_updated' => $result['updated'],
                        'scheduled_time' => $result['scheduled_time'] ?? 'N/A',
                        'applied_at' => $result['applied_at'] ?? 'N/A',
                        'reason' => $result['reason'],
                    ]);
                } else {
                    Log::debug("Profile '{$profile->name}': No schedule action needed", [
                        'reason' => $result['reason'],
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Error processing schedule for profile '{$profile->name}': ".$e->getMessage());
            }
        }

        if ($schedulesApplied > 0) {
            Log::info("Socket schedules processing completed. Applied {$schedulesApplied} schedules, updated {$totalUpdated} sockets across {$profiles->count()} profiles.");
        } else {
            Log::debug("Socket schedules processing completed. No schedules were due for application across {$profiles->count()} profiles.");
        }
    }
}
