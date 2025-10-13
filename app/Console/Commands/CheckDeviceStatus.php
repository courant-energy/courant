<?php

namespace App\Console\Commands;

use App\Models\DowntimeEvent;
use Illuminate\Console\Command;

class CheckDeviceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-device-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Consider using chunkById for large datasets to avoid memory issues
        // e.g., \App\Models\IotDevice::chunkById(100, function ($devices) { ... });
        $iotDevices = \App\Models\IotDevice::all();
        $thresholdMinutes = 5; // Define threshold for online status
        $now = now();

        foreach ($iotDevices as $device) {
            // Find the currently open downtime event for this device (if any)
            $openDowntimeEvent = DowntimeEvent::where('iot_device_id', $device->id)
                ->whereNull('end_time')
                ->first();

            // Determine the current status based on last_seen_at
            $isOnline = $device->last_seen_at && $device->last_seen_at->diffInMinutes($now) < $thresholdMinutes;
            $newStatus = $isOnline ? 'online' : 'offline';

            // Update device status only if it changed
            if ($device->status !== $newStatus) {
                $device->update(['status' => $newStatus]);
            }

            if ($isOnline) {
                // Device is online. If there was an open downtime event, close it.
                if ($openDowntimeEvent) {
                    $openDowntimeEvent->update(['end_time' => $now]);
                }
            } else {
                // Device is offline. If there isn't an open downtime event, create one.
                if (! $openDowntimeEvent) {
                    DowntimeEvent::create([
                        'iot_device_id' => $device->id,
                        'start_time' => $now, // Record when the offline status was detected
                    ]);
                }
                // If $openDowntimeEvent exists, do nothing (it remains open)
            }
        }

        $this->info('Device status check completed.');
    }
}
