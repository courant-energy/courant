<?php

namespace App\Services;

use App\Models\IotDevice;
use App\Models\Reading;
use App\Models\Socket;
use Illuminate\Support\Facades\Validator;
use PhpMqtt\Client\Facades\MQTT;

class MqttService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        // Constructor no longer needs AnomalyDetectionService
    }

    /**
     * Store Data Readings
     */
    public function storeDataReadings($data)
    {
        \Log::debug('Storing data readings', ['data' => $data]);
        // Validate data
        $this->validateReadingData($data);

        try {
            // Get device by uuid
            $device = IotDevice::where('device_uuid', $data['device_id'])->first();

            if (! $device) {
                \Log::warning('Device not found', ['device_uuid' => $data['device_id']]);

                return;
            }

            // Find the socket based on socket_id
            $socket = $device->sockets()
                ->where('assigned_id', $data['socket_id'])
                ->first();

            if (! $socket) {
                \Log::warning('Socket not found', ['device_uuid' => $data['device_id'], 'socket_id' => $data['socket_id']]);

                return;
            }

            // Create and save reading
            $reading = new Reading([
                'voltage' => $data['voltage'],
                'current' => $data['current'],
                'power' => $data['power'],
                'energy' => $data['energy'],
                'frequency' => $data['frequency'],
                'power_factor' => $data['power_factor'],
                'profile_id' => $socket->profile_id,
                'socket_id' => $socket->id,
                'reading_time' => now(),
            ]);

            $reading->save();
            // \Log::debug('Reading stored', [
            //     'device' => $data['device_id'],
            //     'socket' => $data['socket_id'],
            //     'power' => $data['power']
            // ]);
        } catch (\Exception $e) {
            \Log::error('Failed to store reading', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    /**
     * Validate reading data
     */
    public function validateReadingData($data)
    {
        \Log::debug('Validating reading data', ['data' => $data]);

        $rules = [
            'device_id' => 'required|string',
            'socket_id' => 'required|integer',
            'voltage' => 'required|numeric',
            'current' => 'required|numeric',
            'power' => 'required|numeric',
            'energy' => 'required|numeric',
            'frequency' => 'required|numeric',
            'power_factor' => 'required|numeric',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            \Log::error('Validation failed for reading data', ['errors' => $validator->errors()]);
            throw new \Exception('Validation failed: '.$validator->errors()->first());
        }
    }

    /**
     * Handle and publish alerts via MQTT
     *
     * @param  string  $topic  The topic to publish the alert response to
     * @param  array|string  $data  The alert data to process
     * @param  int  $qos  Quality of Service level (0, 1, or 2)
     * @param  bool  $retain  Whether to retain the message
     * @param  string|null  $connection  The named connection to use
     * @return bool Success status
     */
    public function handleAlert($topic, $data, $qos = 0, $retain = false, $connection = null)
    {
        try {
            \Log::info('Handling alert', ['topic' => $topic, 'data' => $data]);

            // Process and store the alert if we have necessary data
            if (is_array($data) && isset($data['device_id']) && isset($data['alert_type'])) {
                $this->processAndStoreAlert($data);
            }

            // Prepare response message
            $responseMessage = is_array($data) ? json_encode([
                'status' => 'processed',
                'alert_id' => $data['id'] ?? uniqid(),
                'timestamp' => now()->toIso8601String(),
                'message' => 'Alert has been processed successfully',
            ]) : json_encode([
                'status' => 'processed',
                'timestamp' => now()->toIso8601String(),
                'message' => 'Alert has been processed successfully',
            ]);

            // For QoS 0, we can use the simple publish method
            if ($qos === 0) {
                MQTT::publish($topic, $responseMessage, $retain, $connection);
                \Log::info('Alert response published', ['topic' => $topic, 'qos' => 0]);

                return true;
            }

            // For QoS 1 and 2, we need to use the loop
            $mqtt = MQTT::connection($connection);
            $mqtt->publish($topic, $responseMessage, $qos, $retain);
            $mqtt->loop(true, true); // Run loop until message is acknowledged

            \Log::info('Alert response published', ['topic' => $topic, 'qos' => $qos]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to handle alert', [
                'topic' => $topic,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Process and store an alert in the database
     *
     * @param  array  $alertData  The alert data
     * @return \App\Models\Alert|null The created alert or null if failed
     */
    protected function processAndStoreAlert(array $alertData)
    {
        try {
            // Find the device
            $device = IotDevice::where('device_uuid', $alertData['device_id'])->first();
            if (! $device) {
                \Log::warning('Device not found for alert', ['device_uuid' => $alertData['device_id']]);

                return null;
            }

            // Get socket ID
            $socketId = $alertData['socket_id'] ?? $alertData['socket'] ?? null;
            if (! $socketId) {
                \Log::warning('Socket ID not found in alert data', ['alert' => $alertData]);

                return null;
            }

            // Find the socket
            $socket = $device->sockets()->where('assigned_id', $socketId)->first();
            if (! $socket) {
                \Log::warning('Socket not found', ['device_id' => $device->id, 'socket_id' => $socketId]);

                return null;
            }

            // Get the profile (if available)
            $profileId = $socket->profile_id;

            // Determine severity
            $severity = $alertData['severity'] ?? 'warning';

            // Create the alert message if not provided
            $message = $alertData['message'] ?? $this->createAlertMessage($alertData);

            // get the reading if exists based on the socket_id and if it is equal to the current_power in data
            $reading = null;
            try {
                $reading = Reading::where('socket_id', $socket->id)
                    ->where('power', $alertData['current_power'] ?? null)
                    ->first();
            } catch (\Exception $e) {
                \Log::error('Failed to fetch reading for alert', [
                    'error' => $e->getMessage(),

                ]);
            }

            // Create alert data structure
            $alert = new \App\Models\Alert([
                'iot_device_id' => $device->id,
                'socket_id' => $socket->id,
                'profile_id' => $profileId,
                'type' => 'device_'.$alertData['alert_type'],
                'message' => $message,
                'severity' => $severity,
                'data' => $this->formatAlertData($alertData),
                'resolved' => false,
                'reading_id' => $reading ? $reading->id : null,
            ]);

            $alert->save();

            \Log::info('Alert stored in database', [
                'alert_id' => $alert->id,
                'type' => $alert->type,
                'device' => $device->name,
            ]);

            return $alert;
        } catch (\Exception $e) {
            \Log::error('Failed to process and store alert', [
                'error' => $e->getMessage(),
                'data' => $alertData,
            ]);

            return null;
        }
    }

    /**
     * Format alert data for storage
     *
     * @param  array  $alertData  The raw alert data
     * @return array Formatted data for storage
     */
    protected function formatAlertData(array $alertData): array
    {
        $formattedData = [
            'alert_type' => $alertData['alert_type'],
            'timestamp' => $alertData['timestamp'] ?? time(),
            'detected_at' => now()->toIso8601String(),
        ];

        // Add additional data based on alert type
        switch ($alertData['alert_type']) {
            case 'power_exceeded':
                $formattedData['current_power'] = $alertData['current_power'] ?? null;
                $formattedData['max_power'] = $alertData['max_power'] ?? null;
                break;

            case 'overcurrent':
                $formattedData['current'] = $alertData['current'] ?? null;
                $formattedData['max_current'] = $alertData['max_current'] ?? null;
                break;

            case 'overvoltage':
                $formattedData['voltage'] = $alertData['voltage'] ?? null;
                $formattedData['max_voltage'] = $alertData['max_voltage'] ?? null;
                break;

            case 'undervoltage':
                $formattedData['voltage'] = $alertData['voltage'] ?? null;
                $formattedData['min_voltage'] = $alertData['min_voltage'] ?? null;
                break;

            case 'low_power_factor':
                $formattedData['power_factor'] = $alertData['power_factor'] ?? null;
                $formattedData['min_power_factor'] = $alertData['min_power_factor'] ?? null;
                break;
        }

        return $formattedData;
    }

    /**
     * Create a human-readable alert message
     */
    protected function createAlertMessage(array $alertData): string
    {
        $socketId = $alertData['socket_id'] ?? $alertData['socket'] ?? 'unknown';

        switch ($alertData['alert_type']) {
            case 'power_exceeded':
                $currentPower = $alertData['current_power'] ?? 'unknown';
                $maxPower = $alertData['max_power'] ?? 'unknown';

                return "Power exceeded on socket {$socketId}: {$currentPower}W (max: {$maxPower}W)";

            case 'overcurrent':
                $current = $alertData['current'] ?? 'unknown';

                return "Overcurrent detected on socket {$socketId}: {$current}A";

            case 'overvoltage':
                $voltage = $alertData['voltage'] ?? 'unknown';

                return "Overvoltage detected on socket {$socketId}: {$voltage}V";

            case 'low_power_factor':
                $powerFactor = $alertData['power_factor'] ?? 'unknown';

                return "Low power factor on socket {$socketId}: {$powerFactor}";

            default:
                return "Alert on socket {$socketId}: {$alertData['alert_type']}";
        }
    }
}
