<?php

namespace App\Console\Commands;

use App\Services\MqttService;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use PhpMqtt\Client\Exceptions\MqttClientException;

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
                $this->info('Setting up signal handlers for graceful shutdown...');
                pcntl_signal(SIGINT, function () use ($mqtt) {
                    $this->info('Received shutdown signal, closing MQTT connection...');
                    $mqtt->interrupt();
                });
                pcntl_async_signals(true);
            }

            // Subscribe to device readings with QoS 1
            $mqtt->subscribe('devices/+/readings', function (string $topic, string $message) use ($mqttService, $mqtt) {
                $this->info("Received message on topic {$topic}");
                $this->line($message);
                
                try {
                    // Clean the message string before parsing - remove any unexpected whitespace, NULL bytes, etc.
                    $cleanedMessage = $this->cleanJsonString($message);
                    
                    $data = json_decode($cleanedMessage, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $errorMsg = json_last_error_msg();
                        $this->error("Invalid JSON format: {$errorMsg}");
                        $this->error("Message content: " . substr($message, 0, 200) . (strlen($message) > 200 ? '...' : ''));
                        
                        // Try to fix common JSON array syntax issues
                        if ($this->looksLikeJsonArray($message)) {
                            $this->info("Attempting to fix JSON array syntax...");
                            $fixedJson = $this->attemptJsonFix($message);
                            $data = json_decode($fixedJson, true);
                            
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $this->error("Failed to fix JSON: " . json_last_error_msg());
                                return;
                            }
                            
                            $this->info("JSON fixed successfully!");
                        } else {
                            return;
                        }
                    }
                    
                    // Extract device ID from topic (devices/{device_id}/readings)
                    $topicParts = explode('/', $topic);
                    $deviceId = $topicParts[1] ?? 'unknown';
                    
                    // Check if data is an array of readings
                    if (is_array($data) && isset($data[0])) {
                        $this->info("Processing batch of " . count($data) . " readings");
                        foreach ($data as $reading) {
                            // Ensure powerFactor key is converted to power_factor for consistency
                            if (isset($reading['powerFactor']) && !isset($reading['power_factor'])) {
                                $reading['power_factor'] = $reading['powerFactor'];
                                unset($reading['powerFactor']);
                            }

                            $reading['device_id'] = $deviceId; // Add device ID to each reading
                            
                            // Store each reading
                            $mqttService->storeDataReadings($reading);
                        }
                    } else {
                        // Handle single reading object
                        // Ensure powerFactor key is converted to power_factor for consistency
                        if (isset($data['powerFactor']) && !isset($data['power_factor'])) {
                            $data['power_factor'] = $data['powerFactor'];
                            unset($data['powerFactor']);
                        }
                        
                        // Store the reading
                        $mqttService->storeDataReadings($data);
                    }
                    
                    // Send acknowledgment to specific device (not using wildcards in publishing)
                    $ackTopic = "devices/{$deviceId}/readings/ack";
                    $mqtt->publish($ackTopic, 'Message received', 1);
                    $this->info("Acknowledgment sent to {$ackTopic}");
                } catch (\Exception $e) {
                    $this->error("Error processing reading: {$e->getMessage()}");
                    $this->error("Message content: " . substr($message, 0, 100) . (strlen($message) > 100 ? '...' : ''));
                }
            }, 1); // QoS level 1

            // Subscribe to alerts with QoS 1
            $mqtt->subscribe('devices/+/alerts', function (string $topic, string $message) use ($mqttService, $mqtt) {
                $this->info("Received alert on topic {$topic}");
                $this->line($message);
                
                try {
                    // Extract device ID from topic
                    $topicParts = explode('/', $topic);
                    $deviceId = $topicParts[1] ?? 'unknown';
                    
                    // Process the alert
                    $data = json_decode($message, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $errorMsg = json_last_error_msg();
                        $this->error("Invalid JSON format: {$errorMsg}");
                        $this->error("Message content: " . substr($message, 0, 100) . (strlen($message) > 100 ? '...' : ''));
                        return;
                    }
                    
                    // Handle the alert - using the updated MqttService::handleAlert method
                    $alertResponseTopic = "devices/{$deviceId}/alerts/response";
                    $mqttService->handleAlert($alertResponseTopic, $data, 1);
                    
                    // Send acknowledgment
                    $ackTopic = "devices/{$deviceId}/alerts/ack";
                    $mqtt->publish($ackTopic, 'Alert received', 1);
                    $this->info("Alert acknowledgment sent to {$ackTopic}");
                } catch (\Exception $e) {
                    $this->error("Error processing alert: {$e->getMessage()}");
                    $this->error("Message content: " . substr($message, 0, 100) . (strlen($message) > 100 ? '...' : ''));
                }
            }, 1); // QoS level 1

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
     * Clean a JSON string by removing control characters and normalizing whitespace
     */
    private function cleanJsonString(string $input): string
    {
        // Remove UTF-8 BOM if present
        $bom = pack('H*', 'EFBBBF');
        $input = preg_replace("/^$bom/", '', $input);
        
        // Remove NULL bytes and other control chars except whitespace
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        
        return trim($input);
    }
    
    /**
     * Check if a string looks like it was intended to be a JSON array
     */
    private function looksLikeJsonArray(string $input): bool
    {
        $trimmed = trim($input);
        return (
            // Starts with [ and contains at least one {
            (strpos($trimmed, '[') === 0 && strpos($trimmed, '{') !== false) ||
            // Or starts with { but has multiple of them, suggesting it might be an array
            (strpos($trimmed, '{') === 0 && substr_count($trimmed, '{') > 1)
        );
    }
    
    /**
     * Attempt to fix common JSON syntax errors
     */
    private function attemptJsonFix(string $input): string
    {
        $input = trim($input);
        
        // Fix trailing commas in arrays/objects which JSON doesn't allow
        $input = preg_replace('/,\s*(\}|\])/', '$1', $input);
        
        // Ensure array has opening and closing brackets
        if (strpos($input, '[') !== 0) {
            $input = '[' . $input;
        }
        
        if (substr($input, -1) !== ']') {
            $input .= ']';
        }
        
        // Fix objects that might be missing closing braces
        $opens = substr_count($input, '{');
        $closes = substr_count($input, '}');
        if ($opens > $closes) {
            $input = $input . str_repeat('}', $opens - $closes);
        }
        
        return $input;
    }
}
