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

        foreach ($profiles as $profile) {
            try {
                $updated = $profile->applySchedule();
                
                if ($updated > 0) {
                    $shouldBeOn = $profile->shouldBeOn();
                    Log::info("Profile '{$profile->name}': Updated {$updated} sockets to " . ($shouldBeOn ? 'ON' : 'OFF'));
                    $totalUpdated += $updated;
                }
            } catch (\Exception $e) {
                Log::error("Error processing schedule for profile '{$profile->name}': " . $e->getMessage());
            }
        }

        Log::info("Socket schedules processing completed. Updated {$totalUpdated} sockets across {$profiles->count()} profiles.");
    }
}
