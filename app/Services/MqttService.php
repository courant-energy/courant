<?php

namespace App\Services;

use App\Models\Reading;
use Illuminate\Support\Facades\Validator;
use App\Models\Socket;
use PhpMqtt\Client\Facades\MQTT;

class MqttService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
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
        $reading->socket_id = $data['socket_id'];
        $reading->voltage = $data['voltage'];
        $reading->current = $data['current'];
        $reading->power = $data['power'];
        $reading->energy = $data['energy'];
        $reading->frequency = $data['frequency'];
        $reading->power_factor = $data['power_factor'];
        // get profile_id from socket model attrib profile_id
        $socket = Socket::findOrFail($data['socket_id']);
        $reading->profile_id = $socket->profile_id;
        $reading->reading_time = now();
        $reading->save();
        \Log::info('Data readings stored successfully', ['reading' => $reading]);


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
