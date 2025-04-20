<?php

namespace App\Services;

use App\Models\IotDevice;
use App\Models\Reading;
use Illuminate\Support\Facades\Validator;
use App\Models\Socket;
use PhpMqtt\Client\Facades\MQTT;
use App\Services\AnomalyDetectionService;

class MqttService
{
    /**
     * The anomaly detection service instance.
     */
    protected $anomalyDetectionService;

    /**
     * Create a new class instance.
     */
    public function __construct(AnomalyDetectionService $anomalyDetectionService)
    {
        $this->anomalyDetectionService = $anomalyDetectionService;
    }

    /**
     * Store Data Readings
     */
    public function storeDataReadings($data)
    {
        // Ensure power_factor exists using powerFactor if needed
        if (!isset($data['power_factor']) && isset($data['powerFactor'])) {
            $data['power_factor'] = $data['powerFactor'];
        }
        
        // Validate data
        $this->validate($data);

        \Log::info('Storing data readings', ['data' => $data]);

        // put to Reading model
        $reading = new Reading();
        $reading->voltage = $data['voltage'];
        $reading->current = $data['current'];
        $reading->power = $data['power'];
        $reading->energy = $data['energy'];
        $reading->frequency = $data['frequency'];
        $reading->power_factor = $data['power_factor'];
        // get profile_id from socket model attrib profile_id
        $device = IotDevice::where('device_uuid', $data['device_id'])->firstOrFail();
        $socket = $device->sockets()
            ->where('assigned_id', $data['socket_id'])
            ->firstOrFail();
        $reading->profile_id = $socket->profile_id;
        $reading->socket_id = $socket->id;
        $reading->reading_time = now();
        $reading->save();
        \Log::info('Data readings stored successfully', ['reading' => $reading]);

        // Check for anomalies in the new reading
        try {
            $anomalies = $this->anomalyDetectionService->detectAnomaly($reading);
            
            if ($anomalies) {
                \Log::info('Anomalies detected in reading', [
                    'reading_id' => $reading->id,
                    'anomalies' => $anomalies,
                    'socket_id' => $socket->id,
                    'device_id' => $device->id
                ]);
                
                // Notify the device owner via MQTT if configured
                $this->notifyAnomalyViaMqtt($device, $socket, $anomalies);
            }
        } catch (\Exception $e) {
            \Log::error('Error detecting anomalies', [
                'error' => $e->getMessage(),
                'reading_id' => $reading->id
            ]);
        }
    }

    /**
     * Notify about anomalies via MQTT
     */
    protected function notifyAnomalyViaMqtt(IotDevice $device, Socket $socket, array $anomalies)
    {
        $profile = $socket->profile;
        
        // Skip if notifications are disabled for this profile
        if ($profile && isset($profile->notify_on_anomaly) && !$profile->notify_on_anomaly) {
            return;
        }
        
        // Create notification payload
        $payload = [
            'type' => 'anomaly_notification',
            'device_id' => $device->device_uuid,
            'socket_id' => $socket->assigned_id,
            'timestamp' => now()->toIso8601String(),
            'anomalies' => array_keys($anomalies),
            'message' => 'Potential device issue detected. Please check your device status.'
        ];
        
        // Publish to device-specific topic
        $topic = "device/{$device->device_uuid}/notifications";
        
        try {
            MQTT::publish($topic, json_encode($payload));
            \Log::info('Anomaly notification sent via MQTT', [
                'topic' => $topic,
                'device_id' => $device->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to publish anomaly notification', [
                'error' => $e->getMessage(),
                'topic' => $topic
            ]);
        }
    }

    /**
     * Validate the data
     */
    public function validate($data)
    {
        \Log::info('Validating data', ['data' => $data]);

        $rules = [
            'socket_id' => 'required|integer',
            'voltage' => 'required|numeric',
            'current' => 'required|numeric',
            'power' => 'required|numeric',
            'energy' => 'required|numeric',
            'frequency' => 'required|numeric',
            'power_factor' => 'required_without:powerFactor|numeric',
            'powerFactor' => 'required_without:power_factor|numeric',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            \Log::error('Validation failed', ['errors' => $validator->errors()]);
            throw new \Exception('Validation failed: ' . $validator->errors()->first());
        }
    }

    /**
     * Handle and publish alerts via MQTT
     *
     * @param string $topic The topic to publish the alert response to
     * @param array|string $data The alert data to process
     * @param int $qos Quality of Service level (0, 1, or 2)
     * @param bool $retain Whether to retain the message
     * @param string|null $connection The named connection to use
     * @return bool Success status
     */
    public function handleAlert($topic, $data, $qos = 0, $retain = false, $connection = null)
    {
        try {
            \Log::info('Handling alert', ['topic' => $topic, 'data' => $data]);
            
            // Prepare response message
            $responseMessage = is_array($data) ? json_encode([
                'status' => 'processed',
                'alert_id' => $data['id'] ?? uniqid(),
                'timestamp' => now()->toIso8601String(),
                'message' => 'Alert has been processed successfully'
            ]) : json_encode([
                'status' => 'processed',
                'timestamp' => now()->toIso8601String(),
                'message' => 'Alert has been processed successfully'
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
            
            // Additional alert processing can be done here
            // Examples: Send email notifications, push notifications, etc.
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to handle alert', [
                'topic' => $topic,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

}
