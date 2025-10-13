<?php

namespace App\Console\Commands;

use App\Services\MqttService;
use Illuminate\Console\Command;
use PhpMqtt\Client\Exceptions\MqttClientException;
use PhpMqtt\Client\Facades\MQTT;

class MqttSub extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mqtt-sub {--connection=default : The MQTT connection to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to MQTT topics for device readings and alerts';

    /**
     * Execute the console command.
     */
    public function handle(MqttService $mqttService)
    {
        $connectionName = $this->option('connection');
        $this->info("Subscribing to MQTT topics using connection: {$connectionName}...");

        try {
            // Connect to MQTT broker
            $mqtt = MQTT::connection($connectionName);

            // Set up signal handler for graceful shutdown
            if (extension_loaded('pcntl')) {
                $this->setupSignalHandlers($mqtt);
            }

            // Subscribe to device readings with QoS 1
            $this->subscribeToReadings($mqtt, $mqttService);

            // Subscribe to alerts with QoS 1
            $this->subscribeToAlerts($mqtt, $mqttService);

            $this->info('Starting MQTT event loop. Press Ctrl+C to exit.');

            // Start the event loop with blocking enabled
            $mqtt->loop(true);

        } catch (MqttClientException $e) {
            $this->error("MQTT Error: {$e->getMessage()}");

            return 1;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");

            return 1;
        }

        return 0;
    }

    /**
     * Set up signal handlers for graceful shutdown
     */
    private function setupSignalHandlers($mqtt)
    {
        $this->info('Setting up signal handlers for graceful shutdown...');
        pcntl_signal(SIGINT, function () use ($mqtt) {
            $this->info('Received shutdown signal, closing MQTT connection...');
            $mqtt->interrupt();
        });
        pcntl_async_signals(true);
    }

    /**
     * Subscribe to device readings
     */
    private function subscribeToReadings($mqtt, $mqttService)
    {
        $mqtt->subscribe('courant/device/+/readings', function (string $topic, string $message) use ($mqttService, $mqtt) {
            $this->info("Received message on topic {$topic}");

            try {
                // Extract device ID from topic (courant/device/{device_id}/readings)
                $deviceId = $this->extractDeviceId($topic);
                $data = $this->parseJsonMessage($message);
                if (! $data) {
                    return;
                }

                // Check if the data contains socket1, socket2, etc. structure
                if ($this->hasSocketStructure($data)) {
                    $this->info('Processing multi-socket reading data');

                    // Process each socket in the payload
                    foreach ($data as $key => $socketData) {
                        // Only process socket objects
                        if (is_array($socketData) && isset($socketData['socket_id']) && isset($socketData['valid']) && $socketData['valid'] === true) {
                            $socketData['device_id'] = $deviceId;
                            $socketData['timestamp'] = $data['timestamp'] ?? time();

                            $this->info("Processing data for {$key} (ID: {$socketData['socket_id']})");
                            $mqttService->storeDataReadings($socketData);
                        }
                    }
                }
                // Check if it's an array of readings
                elseif (is_array($data) && isset($data[0])) {
                    $this->info('Processing batch of '.count($data).' readings');
                    foreach ($data as $reading) {
                        $reading['device_id'] = $deviceId;
                        $mqttService->storeDataReadings($reading);
                    }
                }
                // Single reading
                else {
                    $data['device_id'] = $deviceId;
                    $mqttService->storeDataReadings($data);
                }

                // Send acknowledgment
                $this->sendAcknowledgment($mqtt, $deviceId, 'readings');

            } catch (\Exception $e) {
                $this->error("Error processing reading: {$e->getMessage()}");
                $this->error('Message content: '.substr($message, 0, 100).(strlen($message) > 100 ? '...' : ''));
            }
        }, 1); // QoS level 1
    }

    /**
     * Check if the data has socket structure (socket1, socket2, etc.)
     */
    private function hasSocketStructure(array $data): bool
    {
        // Look for keys that match socketX pattern and contain socket data
        foreach ($data as $key => $value) {
            if (is_array($value) &&
                preg_match('/^socket\d+$/', $key) &&
                isset($value['socket_id']) &&
                isset($value['voltage']) &&
                isset($value['power'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Subscribe to device alerts
     */
    private function subscribeToAlerts($mqtt, $mqttService)
    {
        $mqtt->subscribe('courant/device/+/alerts', function (string $topic, string $message) use ($mqttService, $mqtt) {
            $this->info("Received alert on topic {$topic}");

            try {
                // Extract device ID from topic (courant/device/{device_id}/alerts)
                $deviceId = $this->extractDeviceId($topic);
                $data = $this->parseJsonMessage($message);
                if (! $data) {
                    return;
                }

                // Log the alert data
                $this->info('Processing alert data: '.json_encode($data));

                // Ensure device_id is set in the alert data
                if (! isset($data['device_id'])) {
                    $data['device_id'] = $deviceId;
                }

                // Format the alert for handling
                $alertData = $this->formatAlertData($data);

                // Handle the alert
                $alertResponseTopic = "devices/{$deviceId}/alerts/response";
                $mqttService->handleAlert($alertResponseTopic, $alertData, 1);

                // Send acknowledgment
                $this->sendAcknowledgment($mqtt, $deviceId, 'alerts');

            } catch (\Exception $e) {
                $this->error("Error processing alert: {$e->getMessage()}");
                $this->error('Message content: '.substr($message, 0, 100).(strlen($message) > 100 ? '...' : ''));
            }
        }, 1); // QoS level 1
    }

    /**
     * Format alert data for proper processing
     */
    private function formatAlertData(array $data): array
    {
        // For alerts that follow the format from the sample
        if (isset($data['alert_type'])) {
            // Convert socket number to field name for consistency
            if (isset($data['socket']) && is_numeric($data['socket'])) {
                $data['socket_id'] = (int) $data['socket'];
            }

            // Add timestamp if not present
            if (! isset($data['timestamp'])) {
                $data['timestamp'] = time();
            }

            // Set severity based on alert type
            $data['severity'] = $this->determineSeverityFromAlertType($data['alert_type']);

            // Create a descriptive message
            $data['message'] = $this->createAlertMessage($data);
        }

        return $data;
    }

    /**
     * Determine alert severity from alert type
     */
    private function determineSeverityFromAlertType(string $alertType): string
    {
        switch ($alertType) {
            case 'power_exceeded':
            case 'overcurrent':
            case 'overvoltage':
                return 'critical';

            case 'low_power_factor':
            case 'undervoltage':
                return 'warning';

            default:
                return 'info';
        }
    }

    /**
     * Create a human-readable alert message
     */
    private function createAlertMessage(array $alertData): string
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

    /**
     * Extract device ID from topic
     */
    private function extractDeviceId(string $topic): string
    {
        $topicParts = explode('/', $topic);

        return $topicParts[2] ?? 'unknown';
    }

    /**
     * Send acknowledgment message
     */
    private function sendAcknowledgment($mqtt, string $deviceId, string $type)
    {
        $ackTopic = "devices/{$deviceId}/{$type}/ack";
        $mqtt->publish($ackTopic, 'Message received', 1);
        $this->info("Acknowledgment sent to {$ackTopic}");
    }

    /**
     * Parse JSON message with error handling
     */
    private function parseJsonMessage(string $message)
    {
        // Clean the message string before parsing
        $message = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $message));

        // Remove UTF-8 BOM if present
        $bom = pack('H*', 'EFBBBF');
        $message = preg_replace("/^$bom/", '', $message);

        // Try to decode the JSON
        $data = json_decode($message, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }

        // Handle errors
        $errorMsg = json_last_error_msg();
        $this->error("Invalid JSON format: {$errorMsg}");

        // Try to fix common JSON issues
        if ($this->looksLikeJsonArray($message)) {
            $this->info('Attempting to fix JSON array syntax...');
            $fixedJson = $this->attemptJsonFix($message);
            $data = json_decode($fixedJson, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->info('JSON fixed successfully!');

                return $data;
            }

            $this->error('Failed to fix JSON: '.json_last_error_msg());
        }

        return null;
    }

    /**
     * Check if a string looks like it was intended to be a JSON array
     */
    private function looksLikeJsonArray(string $input): bool
    {
        $input = trim($input);

        return
            (strpos($input, '[') === 0 && strpos($input, '{') !== false) ||
            (strpos($input, '{') === 0 && substr_count($input, '{') > 1);
    }

    /**
     * Attempt to fix common JSON syntax errors
     */
    private function attemptJsonFix(string $input): string
    {
        $input = trim($input);

        // Fix trailing commas
        $input = preg_replace('/,\s*(\}|\])/', '$1', $input);

        // Ensure array has opening and closing brackets
        if (strpos($input, '[') !== 0) {
            $input = '['.$input;
        }

        if (substr($input, -1) !== ']') {
            $input .= ']';
        }

        // Fix missing closing braces
        $opens = substr_count($input, '{');
        $closes = substr_count($input, '}');
        if ($opens > $closes) {
            $input = $input.str_repeat('}', $opens - $closes);
        }

        return $input;
    }
}
