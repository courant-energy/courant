<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IotDevice;
use App\Models\Socket;
use App\Models\Profile;

/**
 * Handles IoT configuration requests.
 */
class ConfigController extends Controller
{
    /**
     * Get the configuration for an IoT device.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfig(Request $request)
    {
        // Validate the request should have device_uuid
        $request->validate([
            'device_uuid' => 'required|string',
        ]);

        // Get the device_uuid from the request
        $deviceUuid = $request->input('device_uuid');

        // Find the device by UUID
        $device = IotDevice::where('device_uuid', $deviceUuid)->first();

        // If device doesn't exist, return 404
        if (!$device) {
            return response()->json([
                'error' => 'Device not found',
                'device_uuid' => $deviceUuid,
            ], 404);
        }

        // Get all sockets for the device with their associated profiles
        $sockets = $device->sockets()->with('profile')->get();

        // Format the response
        $socketConfigs = $sockets->map(function ($socket) {
            // Get maximum power from the associated profile or default to 0
            $maxPower = $socket->profile ? 
                $socket->profile->max_power_threshold ?? 0 : 
                0;

            return [
                'assigned_id' => $socket->assigned_id,
                'maximum_power' => $maxPower,
                'is_on' => (bool) $socket->state,
            ];
        });

        // Create a response array
        $response = [
            'device_uuid' => $deviceUuid,
            'sockets' => $socketConfigs->toArray(),
        ];

        // Update last_seen_at timestamp for the device
        $device->update([
            'last_seen_at' => now(),
        ]);

        // Return the response as JSON
        return response()->json($response);
    }
}
