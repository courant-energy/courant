<?php

namespace App\Services;

use App\Models\Reading;
use App\Models\Alert;
use App\Models\IotDevice;
use App\Models\Profile;
use App\Models\Socket;
use GeminiAPI\Client;
use GeminiAPI\Enums\MimeType;
use GeminiAPI\Enums\ModelName;
use GeminiAPI\Requests\GenerateContentRequest;
use GeminiAPI\Resources\Content;
use GeminiAPI\Resources\Parts\FileDataPart;
use GeminiAPI\Resources\Parts\FilePart;
use GeminiAPI\Resources\Parts\TextPart;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiService
{
    /**
     * Export reading data in the specified format
     * Aggregates readings by hour to reduce data size
     *
     * @param string $format Format to export data (csv or json)
     * @param array $filters Optional filters to apply to the query
     * @return string Formatted data in the requested format
     */
    public function exportReadingData(string $format = 'json', array $filters = [])
    {
        try {
            $query = Reading::query();
            
            // Apply filters if provided
            if (isset($filters['socket_id'])) {
                $query->where('socket_id', $filters['socket_id']);
            }
            
            // Support filtering by multiple socket IDs
            if (isset($filters['socket_ids']) && is_array($filters['socket_ids']) && !empty($filters['socket_ids'])) {
                $query->whereIn('socket_id', $filters['socket_ids']);
            }
            
            if (isset($filters['profile_id'])) {
                $query->where('profile_id', $filters['profile_id']);
            }
            
            // Support filtering by multiple profile IDs
            if (isset($filters['profile_ids']) && is_array($filters['profile_ids']) && !empty($filters['profile_ids'])) {
                $query->whereIn('profile_id', $filters['profile_ids']);
            }
            
            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $query->whereBetween('reading_time', [$filters['start_date'], $filters['end_date']]);
            }

            // Get the base data for aggregation
            $readings = $query->get([
                'socket_id',
                'voltage',
                'current',
                'power',
                'energy',
                'frequency',
                'power_factor',
                'profile_id',
                'reading_time'
            ]);

            // Group readings by hour and calculate averages
            $hourlyReadings = collect();
            $groupedReadings = $readings->groupBy(function ($reading) {
                // Group by socket_id, profile_id and hour of reading_time
                $hourKey = $reading->reading_time->format('Y-m-d H:00:00');
                return $reading->socket_id . '_' . $reading->profile_id . '_' . $hourKey;
            });

            foreach ($groupedReadings as $key => $group) {
                // Split the key to get socket_id, profile_id, and hour
                list($socket_id, $profile_id, $hour) = explode('_', $key, 3);
                
                // Calculate averages for numeric values
                $hourlyReading = [
                    'socket_id' => $socket_id,
                    'profile_id' => $profile_id,
                    'reading_time' => $hour,
                    'voltage' => $group->avg('voltage'),
                    'current' => $group->avg('current'),
                    'power' => $group->avg('power'),
                    // Sum energy values since it's cumulative
                    'energy' => $group->max('energy'),
                    'frequency' => $group->avg('frequency'),
                    'power_factor' => $group->avg('power_factor'),
                    // Also include the count of readings that were averaged
                    'readings_count' => $group->count(),
                ];
                
                $hourlyReadings->push($hourlyReading);
            }

            // Sort by time for better readability
            $sortedReadings = $hourlyReadings->sortBy('reading_time')->values();

            // Return formatted data
            return $this->formatData($sortedReadings, $format);
        } catch (\Exception $e) {
            Log::error('Error exporting reading data: ' . $e->getMessage());
            return $format === 'json' 
                ? json_encode(['error' => 'Failed to export reading data: ' . $e->getMessage()]) 
                : 'Error: Failed to export reading data: ' . $e->getMessage();
        }
    }

    /**
     * Export alert data in the specified format
     *
     * @param string $format Format to export data (csv or json)
     * @param array $filters Optional filters to apply to the query
     * @return string Formatted data in the requested format
     */
    public function exportAlertData(string $format = 'json', array $filters = [])
    {
        try {
            $query = Alert::query();
            
            // Apply filters if provided
            if (isset($filters['iot_device_id'])) {
                $query->where('iot_device_id', $filters['iot_device_id']);
            }
            
            // Support filtering by multiple IoT device IDs
            if (isset($filters['iot_device_ids']) && is_array($filters['iot_device_ids']) && !empty($filters['iot_device_ids'])) {
                $query->whereIn('iot_device_id', $filters['iot_device_ids']);
            }
            
            if (isset($filters['socket_id'])) {
                $query->where('socket_id', $filters['socket_id']);
            }
            
            // Support filtering by multiple socket IDs
            if (isset($filters['socket_ids']) && is_array($filters['socket_ids']) && !empty($filters['socket_ids'])) {
                $query->whereIn('socket_id', $filters['socket_ids']);
            }
            
            if (isset($filters['profile_id'])) {
                $query->where('profile_id', $filters['profile_id']);
            }
            
            // Support filtering by multiple profile IDs
            if (isset($filters['profile_ids']) && is_array($filters['profile_ids']) && !empty($filters['profile_ids'])) {
                $query->whereIn('profile_id', $filters['profile_ids']);
            }

            if (isset($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (isset($filters['severity'])) {
                $query->where('severity', $filters['severity']);
            }

            if (isset($filters['resolved'])) {
                $query->where('resolved', $filters['resolved']);
            }

            // Select only the requested fields
            $query->select([
                'iot_device_id',
                'socket_id',
                'profile_id',
                'type',
                'message',
                'data',
                'severity',
                'resolved',
                'resolved_at',
                'resolution_notes'
            ]);

            $alerts = $query->get();
            
            // Format the data field if it's a JSON field
            $alerts = $alerts->map(function ($alert) {
                if (is_array($alert->data)) {
                    $alert->data = json_encode($alert->data);
                }
                return $alert;
            });

            // Return formatted data
            return $this->formatData($alerts, $format);
        } catch (\Exception $e) {
            Log::error('Error exporting alert data: ' . $e->getMessage());
            return $format === 'json' 
                ? json_encode(['error' => 'Failed to export alert data: ' . $e->getMessage()]) 
                : 'Error: Failed to export alert data: ' . $e->getMessage();
        }
    }

    /**
     * Export IoT device data in the specified format
     *
     * @param string $format Format to export data (csv or json)
     * @param array $filters Optional filters to apply to the query
     * @return string Formatted data in the requested format
     */
    public function exportIotDeviceData(string $format = 'json', array $filters = [])
    {
        try {
            $query = IotDevice::query();
            
            // Apply filters if provided
            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }
            
            if (isset($filters['is_active'])) {
                $query->where('is_active', $filters['is_active']);
            }

            // Select only the requested fields
            $query->select([
                'name',
                'device_uuid',
                'user_id',
                'is_active',
                'socket_count',
                'last_seen_at',
                'status'
            ]);

            $devices = $query->get();

            // Return formatted data
            return $this->formatData($devices, $format);
        } catch (\Exception $e) {
            Log::error('Error exporting IoT device data: ' . $e->getMessage());
            return $format === 'json' 
                ? json_encode(['error' => 'Failed to export IoT device data']) 
                : 'Error: Failed to export IoT device data';
        }
    }

    /**
     * Export profile data in the specified format
     *
     * @param string $format Format to export data (csv or json)
     * @param array $filters Optional filters to apply to the query
     * @return string Formatted data in the requested format
     */
    public function exportProfileData(string $format = 'json', array $filters = [])
    {
        try {
            $query = Profile::query();
            
            // Apply filters if provided
            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }
            
            if (isset($filters['notify_on_anomaly'])) {
                $query->where('notify_on_anomaly', $filters['notify_on_anomaly']);
            }
            
            if (isset($filters['alert_sensitivity'])) {
                $query->where('alert_sensitivity', $filters['alert_sensitivity']);
            }

            // Select only the requested fields
            $query->select([
                'name',
                'expected_power',
                'max_power_threshold',
                'min_power_threshold',
                'user_id',
                'notify_on_anomaly',
                'alert_sensitivity',
                'notification_channels'
            ]);

            $profiles = $query->get();
            
            // Format the notification_channels field if it's a JSON field
            $profiles = $profiles->map(function ($profile) {
                if (is_array($profile->notification_channels)) {
                    $profile->notification_channels = json_encode($profile->notification_channels);
                }
                return $profile;
            });

            // Return formatted data
            return $this->formatData($profiles, $format);
        } catch (\Exception $e) {
            Log::error('Error exporting profile data: ' . $e->getMessage());
            return $format === 'json' 
                ? json_encode(['error' => 'Failed to export profile data']) 
                : 'Error: Failed to export profile data';
        }
    }

    /**
     * Format data collection to the requested format (CSV or JSON)
     *
     * @param \Illuminate\Database\Eloquent\Collection $data Data collection to format
     * @param string $format Format to export (csv or json)
     * @return string Formatted data string
     */
    private function formatData($data, string $format)
    {
        if ($format === 'json') {
            return json_encode($data, JSON_PRETTY_PRINT);
        } elseif ($format === 'csv') {
            if ($data->isEmpty()) {
                return '';
            }
            
            // Create CSV header from the keys of the first item
            $headers = array_keys($data->first()->toArray());
            $csv = implode(',', $headers) . "\n";
            
            // Add data rows
            foreach ($data as $item) {
                $row = [];
                foreach ($headers as $header) {
                    // Format datetime values to ISO 8601
                    if ($item[$header] instanceof \DateTime) {
                        $row[] = $item[$header]->format('c'); // ISO 8601 format
                    } else {
                        // Escape values containing commas with quotes
                        $value = $item[$header];
                        if (is_string($value) && (strpos($value, ',') !== false || strpos($value, '"') !== false)) {
                            $value = '"' . str_replace('"', '""', $value) . '"';
                        }
                        $row[] = $value;
                    }
                }
                $csv .= implode(',', $row) . "\n";
            }
            
            return $csv;
        }
        
        throw new \InvalidArgumentException("Unsupported format: {$format}. Supported formats are 'csv' and 'json'.");
    }

    /**
     * Export all data in a single response
     * Can be filtered to only include data for the current authenticated user
     * Default behavior filters to the past 30 days of data
     *
     * @param string $format Format to export data (csv or json)
     * @param array $filters Optional filters to apply to queries
     * @param App\Models\User $user Whether to restrict data to current authenticated user
     * @return array|string All data in requested format
     */
    public function exportAllData(string $format = 'json', array $filters = [], $user = null)
    {
        // Set default date range to past 30 days if not specified
        if (!isset($filters['start_date'])) {
            $filters['start_date'] = now()->subDays(30)->startOfDay()->toDateTimeString();
        }
        
        if (!isset($filters['end_date'])) {
            $filters['end_date'] = now()->endOfDay()->toDateTimeString();
        }
        
        // If currentUserOnly is true, add user_id filter
        if ($user) {
            $userId = $user->id;
            
            // Add user_id to filters
            $filters['user_id'] = $userId;
            
            // We need to get the user's IoT devices to filter readings and alerts
            $userDevices = IotDevice::where('user_id', $userId)->pluck('id')->toArray();
            
            // Get the user's sockets to filter readings
            $userSockets = Socket::whereIn('iot_device_id', $userDevices)->pluck('id')->toArray();
            
            // Get the user's profiles to filter readings
            $userProfiles = Profile::where('user_id', $userId)->pluck('id')->toArray();
            
            // Create a special filter for device-specific data
            $deviceFilters = array_merge($filters, ['iot_device_ids' => $userDevices]);
            
            // Create a special filter for socket-specific data
            $socketFilters = array_merge($filters, ['socket_ids' => $userSockets]);
            
            // Create a special filter for profile-specific data
            $profileFilters = array_merge($filters, ['profile_ids' => $userProfiles]);
        } else {
            $deviceFilters = $filters;
            $socketFilters = $filters;
            $profileFilters = $filters;
        }

        if ($format === 'json') {
            return json_encode([
                'readings' => json_decode($this->exportReadingData('json', $socketFilters)),
                'alerts' => json_decode($this->exportAlertData('json', $deviceFilters)),
                'iotDevices' => json_decode($this->exportIotDeviceData('json', $filters)),
                'profiles' => json_decode($this->exportProfileData('json', $filters))
            ], JSON_PRETTY_PRINT);
        } else {
            // For CSV, return a multi-section document with headers separating each section
            $csv = "=== READINGS ===\n";
            $csv .= $this->exportReadingData('csv', $socketFilters);
            $csv .= "\n=== ALERTS ===\n";
            $csv .= $this->exportAlertData('csv', $deviceFilters);
            $csv .= "\n=== IOT DEVICES ===\n";
            $csv .= $this->exportIotDeviceData('csv', $filters);
            $csv .= "\n=== PROFILES ===\n";
            $csv .= $this->exportProfileData('csv', $filters);
            
            return $csv;
        }
    }

    /**
     * Generate insights by sending data to Gemini API
     * 
     * @param App\Models\User $user The authenticated user
     * @param bool $isLoading Reference variable to track loading state
     * @return array The insights generated by Gemini API
     */
    public function generateInsights($user = null, &$isLoading = false)
    {
        try {
            $isLoading = true;
            
            // Get data from the last 30 days
            $data = $this->exportAllData('json', [], $user);
            
            // Store the data in a temporary file
            $tempFilename = 'gemini_data_' . uniqid() . '.json';
            $tempFilePath = Storage::disk('local')->path($tempFilename);
            file_put_contents($tempFilePath, $data);
            
            // Initialize Gemini API data
            $apiKey = config('services.gemini.api_key');
            $modelId = config('services.gemini.model_id', 'gemini-2.0-flash');
            
            // Since the client library doesn't support responseSchema, we'll make a direct API call
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$modelId}:generateContent?key={$apiKey}";
            
            // Create prompt text
            $promptText = "Analyze the provided power readings data, integrating device profiles and alert information, to provide actionable insights. The goal is to optimize power consumption, predict equipment failures, and improve energy efficiency for MSMEs.\n\n" .
                "Specifically:\n\n" .
                "Identify instances of energy wastage.\n\n" .
                "Compare actual power consumption (from the Reading table) with expected power consumption (from the Profile table).\n\n" .
                "Quantify the wastage (in Watts or kWh) and express it as a percentage of expected consumption.\n\n" .
                "Suggest potential causes for the wastage, limited to: 'Inefficient equipment operation', 'Aging equipment', 'Incorrect device settings', or 'Other'.\n\n" .
                "Detect anomalies in power consumption.\n\n" .
                "Analyze voltage, current, and power data (from the Reading table) for fluctuations, surges, and brownouts.\n\n" .
                "Correlate detected anomalies with alerts (from the Alert table) by matching socket_id and timestamps (within a 5-minute window).\n\n" .
                "For each anomaly, classify the type as: 'Voltage fluctuation', 'Power surge', 'Brownout', or 'Other'.\n\n" .
                "Predict potential equipment failures.\n\n" .
                "Analyze trends in power, voltage, and current over time (using reading_time from the Reading table).\n\n" .
                "Use max_power_threshold and min_power_threshold (from the Profile table) to identify readings exceeding safe limits.\n\n" .
                "Predict failures only if the trend shows a sustained increase (for max threshold) or decrease (for min threshold) over at least 3 consecutive readings.\n\n" .
                "Provide recommendations.\n\n" .
                "Base recommendations on the analysis in steps 1-3.\n\n" .
                "Limit recommendations to: 'Adjust device settings', 'Schedule maintenance', 'Implement energy-saving measures', 'Investigate wiring', 'Replace equipment', or 'Other'.\n\n" .
                "Assess alert system effectiveness.\n\n" .
                "Analyze the Alert table.\n\n" .
                "Identify patterns in type, severity, and resolved status.\n\n" .
                "Calculate the average resolution time (resolved_at - alert time) for each alert type.\n\n" .
                "Reporting Requirements\n\n" .
                "Provide a clear explanation for each insight.\n\n" .
                "Include key supporting data points (values from the tables).\n\n" .
                "Provide actionable recommendations from the list in step 4.\n\n" .
                "Prioritize insights as 'High', 'Medium', or 'Low' based on potential impact on cost, equipment, and operations.\n\n" .
                "Filtering\n\n" .
                "Use the IotDevice table to filter data by device_uuid. If no device_uuid is provided, analyze all devices.\n\n" .
                "Output Structure\n\n" .
                "The response MUST be valid JSON.\n\n" .
                "The JSON should contain ONLY the labels and data types as specified in the example structure.\n\n" .
                "Do not include any descriptive text or explanations within the JSON data itself.\n\n" .
                "Use the aliases and names instead of the data ids.\n\n" .
                "The insight_type should be one of the following: 'Energy Wastage', 'Anomaly Detection', 'Predictive Maintenance', or 'Alert System Effectiveness'.";

            // Define response schema
            $responseSchema = [
                'type' => 'object',
                'properties' => [
                    'insights' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'insight_id' => ['type' => 'integer'],
                                'insight_type' => [
                                    'type' => 'string',
                                    'enum' => ['Energy Wastage', 'Anomaly Detection', 'Predictive Maintenance']
                                ],
                                'description' => ['type' => 'string'],
                                'supporting_data' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'socket_id' => ['type' => 'string'],
                                            'device_name' => ['type' => 'string']
                                        ]
                                    ]
                                ],
                                'potential_causes' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string']
                                ],
                                'recommendations' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string']
                                ],
                                'priority' => ['type' => 'string']
                            ],
                            'required' => [
                                'insight_id',
                                'insight_type',
                                'description',
                                'potential_causes',
                                'recommendations',
                                'priority'
                            ]
                        ]
                    ]
                ],
                'required' => ['insights']
            ];
            
            // Construct the request body with the responseMimeType and responseSchema
            $requestBody = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => $promptText
                            ],
                            [
                                'text' => "\nAnalyze this data:\n" . $data
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'topP' => 0.8,
                    'topK' => 40,
                    'maxOutputTokens' => 2048,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $responseSchema
                ]
            ];
            
            // Make the API request using Guzzle
            $client = new \GuzzleHttp\Client();
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestBody,
            ]);
            
            // Clean up the temporary file
            Storage::disk('local')->delete($tempFilename);
            
            $isLoading = false;
            
            // Parse the response
            $responseData = json_decode($response->getBody(), true);
            
            // Extract the text content from the response
            $content = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            if ($this->isJson($content)) {
                return json_decode($content, true);
            }
            
            return ['error' => 'The API response was not in the expected JSON format.'];
            
        } catch (\Exception $e) {
            Log::error('Error generating insights: ' . $e->getMessage());
            $isLoading = false;
            return ['error' => 'Failed to generate insights: ' . $e->getMessage()];
        }
    }
    
    /**
     * Check if a string is valid JSON
     * 
     * @param string $string String to check
     * @return bool True if valid JSON, false otherwise
     */
    private function isJson(string $string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
