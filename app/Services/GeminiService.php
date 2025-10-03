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
use Illuminate\Support\Facades\Auth;
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
        Log::debug('GeminiService::exportReadingData started', [
            'user_id' => Auth::id(),
            'format' => $format,
            'filters' => $filters
        ]);

        try {
            $query = Reading::query()
                ->whereHas('socket.iotDevice', function ($query) {
                    $query->where('user_id', Auth::id());
                });

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

            Log::debug('GeminiService::exportReadingData completed successfully', [
                'user_id' => Auth::id(),
                'format' => $format,
                'total_readings_processed' => $readings->count(),
                'hourly_readings_generated' => $sortedReadings->count()
            ]);

            // Return formatted data
            return $this->formatData($sortedReadings, $format);
        } catch (\Exception $e) {
            Log::error('GeminiService::exportReadingData failed', [
                'user_id' => Auth::id(),
                'format' => $format,
                'filters' => $filters,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
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
        Log::debug('GeminiService::exportAlertData started', [
            'user_id' => Auth::id(),
            'format' => $format,
            'filters' => $filters
        ]);

        try {
            $query = Alert::query()
                ->whereHas('iotDevice', function ($query) {
                    $query->where('user_id', Auth::id());
                });

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

            Log::debug('GeminiService::exportAlertData completed successfully', [
                'user_id' => Auth::id(),
                'format' => $format,
                'alerts_count' => $alerts->count()
            ]);

            // Return formatted data
            return $this->formatData($alerts, $format);
        } catch (\Exception $e) {
            Log::error('GeminiService::exportAlertData failed', [
                'user_id' => Auth::id(),
                'format' => $format,
                'filters' => $filters,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
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
        Log::debug('GeminiService::exportIotDeviceData started', [
            'user_id' => Auth::id(),
            'format' => $format,
            'filters' => $filters
        ]);

        try {
            $query = IotDevice::query()
                ->where('user_id', Auth::id());

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

            Log::debug('GeminiService::exportIotDeviceData completed successfully', [
                'user_id' => Auth::id(),
                'format' => $format,
                'devices_count' => $devices->count()
            ]);

            // Return formatted data
            return $this->formatData($devices, $format);
        } catch (\Exception $e) {
            Log::error('GeminiService::exportIotDeviceData failed', [
                'user_id' => Auth::id(),
                'format' => $format,
                'filters' => $filters,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return $format === 'json'
                ? json_encode(['error' => 'Failed to export IoT device data: ' . $e->getMessage()])
                : 'Error: Failed to export IoT device data: ' . $e->getMessage();
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
        Log::debug('GeminiService::exportProfileData started', [
            'user_id' => Auth::id(),
            'format' => $format,
            'filters' => $filters
        ]);

        try {
            $query = Profile::query()
                ->where('user_id', Auth::id());

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

            Log::debug('GeminiService::exportProfileData completed successfully', [
                'user_id' => Auth::id(),
                'format' => $format,
                'profiles_count' => $profiles->count()
            ]);

            // Return formatted data
            return $this->formatData($profiles, $format);
        } catch (\Exception $e) {
            Log::error('GeminiService::exportProfileData failed', [
                'user_id' => Auth::id(),
                'format' => $format,
                'filters' => $filters,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return $format === 'json'
                ? json_encode(['error' => 'Failed to export profile data: ' . $e->getMessage()])
                : 'Error: Failed to export profile data: ' . $e->getMessage();
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
        Log::debug('GeminiService::formatData started', [
            'format' => $format,
            'data_count' => $data->count(),
            'data_type' => get_class($data)
        ]);

        try {
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

            Log::error('GeminiService::formatData unsupported format', [
                'format' => $format,
                'supported_formats' => ['csv', 'json']
            ]);
            throw new \InvalidArgumentException("Unsupported format: {$format}. Supported formats are 'csv' and 'json'.");
        } catch (\Exception $e) {
            Log::error('GeminiService::formatData failed', [
                'format' => $format,
                'data_count' => $data->count(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
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
        Log::debug('GeminiService::exportAllData started', [
            'format' => $format,
            'filters' => $filters,
            'user_id' => $user ? $user->id : null
        ]);
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
        Log::info('GeminiService::generateInsights started', [
            'user_id' => $user ? $user->id : null,
            'timestamp' => now()->toISOString()
        ]);

        try {
            $isLoading = true;

            Log::debug('GeminiService::generateInsights exporting data for analysis');

            // Get data from the last 30 days
            $data = $this->exportAllData('json', [], $user);
            
            Log::debug('GeminiService::generateInsights data exported', [
                'data_size_bytes' => strlen($data),
                'user_id' => $user ? $user->id : null
            ]);

            // Store the data in a temporary file
            $tempFilename = 'gemini_data_' . uniqid() . '.json';
            $tempFilePath = Storage::disk('local')->path($tempFilename);
            
            try {
                file_put_contents($tempFilePath, $data);
                Log::debug('GeminiService::generateInsights temp file created', [
                    'temp_filename' => $tempFilename,
                    'temp_file_path' => $tempFilePath
                ]);
            } catch (\Exception $e) {
                Log::error('GeminiService::generateInsights failed to create temp file', [
                    'temp_filename' => $tempFilename,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }

            // Initialize Gemini API data
            $apiKey = config('services.gemini.api_key');
            $modelId = config('services.gemini.model_id', 'gemini-2.0-flash');
            
            Log::debug('GeminiService::generateInsights API configuration', [
                'model_id' => $modelId,
                'has_api_key' => !empty($apiKey),
                'api_key_length' => $apiKey ? strlen($apiKey) : 0
            ]);
            
            if (empty($apiKey)) {
                Log::error('GeminiService::generateInsights missing API key');
                throw new \Exception('Gemini API key is not configured');
            }

            // Since the client library doesn't support responseSchema, we'll make a direct API call
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$modelId}:generateContent?key={$apiKey}";

            // Create prompt text
            $promptText = "Analyze the provided power readings data, integrating device profiles and alert information, to provide actionable insights. The goal is to optimize power consumption, predict equipment failures, and improve energy efficiency for MSMEs.\n\n" .
                "Specifically:\n\n" .
                "Identify instances of energy wastage and operational failures.\n\n" .
                "Compare actual power consumption (from the Reading table) with expected power consumption (from the Profile table).\n\n" .
                "IMPORTANT: Apply conditional logic for wastage analysis:\n\n" .
                "- IF actual power consumption is greater than 0.5 W AND less than expected power consumption, THEN generate an 'Energy Wastage' insight.\n\n" .
                "- IF actual power consumption is 0.5 W or less AND expected power consumption is greater than 0.5 W, THEN:\n\n" .
                "    APPLY DEVICE CONTEXT FILTER USING THE DEVICE NAME FROM THE IOT DEVICES TABLE:\n\n" .
                "    - IF the device name contains keywords like 'Charger', 'Phone Charger', 'Laptop Charger', 'Power Bank' or similar charging devices, AND the power consumption has been ≤0.5 W for less than 4 hours, THEN DO NOT GENERATE an 'Operational Failure' insight (as charging devices are expected to show 0W when not charging).\n\n" .
                "    - IF the device name contains keywords like 'Refrigerator', 'Fridge', 'Air Conditioner', 'AC Unit', 'Cooling System' or similar refrigeration/HVAC devices, AND the 0W reading duration is less than 90 minutes, THEN DO NOT GENERATE an 'Operational Failure' insight (as these devices cycle on and off normally).\n\n" .
                "    - IF the device name contains keywords like 'Server', 'POS Terminal', 'Computer', 'Router', 'Modem', 'Security System' or similar always-on devices, THEN ALWAYS GENERATE an 'Operational Failure' insight regardless of duration (as these should be on 24/7 or during business hours).\n\n" .
                "    - IF the device name contains keywords like 'Microwave', 'Power Tool', 'Printer', 'Coffee Machine' (intermittent use devices), AND there's no pattern of recent high power usage followed by abrupt drop to 0W, THEN DO NOT GENERATE an 'Operational Failure' insight (as 0W is their normal idle state).\n\n" .
                "    - OTHERWISE (for all other devices, or if power loss is prolonged beyond the thresholds above), THEN GENERATE an 'Operational Failure' insight.\n\n" .
                "- Zero or near-zero consumption (≤0.5 W) analysis must consider device type context to avoid false positives.\n\n" .
                "For Energy Wastage insights: Quantify the wastage (in Watts or kWh) and express it as a percentage of expected consumption.\n\n" .
                "For Energy Wastage insights: Suggest potential causes limited to: 'Inefficient equipment operation', 'Aging equipment', 'Incorrect device settings', or 'Other'.\n\n" .
                "For Operational Failure insights: Suggest potential causes limited to: 'Device unplugged or disconnected', 'Power supply failure', 'Device malfunction', 'Incorrect wiring', or 'Other'.\n\n" .
                "GUIDANCE FOR OPERATIONAL FAILURE (DEVICE CONTEXT):\n\n" .
                "- Chargers (Phone Charger, Laptop Charger, Power Bank): It is normal for a charger to show 0W when not charging a device. Only classify as 'Operational Failure' if the 0W reading persists for more than 4 hours during expected use times (business hours for office chargers, evening hours for home chargers) or if the charger typically has a non-zero standby power profile (>0.5W).\n\n" .
                "- Refrigeration/AC Units (Refrigerator, Fridge, Air Conditioner, AC Unit, Cooling System): These devices cycle on and off naturally, resulting in periodic 0W readings. Only classify as 'Operational Failure' if the off-cycle duration exceeds 90 minutes continuously, which may indicate a malfunction or power loss.\n\n" .
                "- Always-On Devices (Server, POS Terminal, Computer, Router, Modem, Security System): These devices are expected to be on 24/7 or during all business hours. Any sustained 0W reading during the expected operation window should ALWAYS be classified as 'Operational Failure', regardless of duration.\n\n" .
                "- Intermittent Use Devices (Microwave, Power Tool, Printer, Coffee Machine): A 0W reading is the default idle state for these devices. Only classify as 'Operational Failure' if a low or zero power reading is recorded immediately after a period of high power usage, suggesting an abrupt disconnection or failure while actively in use.\n\n" .
                "- Unknown Device Types: If the device name doesn't clearly indicate the device type, err on the side of caution and generate 'Operational Failure' insights for sustained 0W readings (>2 hours) to ensure critical issues aren't missed.\n\n" .
                "Detect anomalies in power consumption.\n\n" .
                "Analyze voltage, current, and power data (from the Reading table) for fluctuations, surges, and brownouts.\n\n" .
                "Correlate detected anomalies with alerts (from the Alert table) by matching socket_id and timestamps (within a 5-minute window).\n\n" .
                "For each anomaly, classify the type as: 'Voltage fluctuation', 'Power surge', 'Brownout', or 'Other'.\n\n" .
                "When referencing alerts, translate the technical alert type from the Alert table into user-friendly language. Explain what the alert means in practical terms rather than using the technical alert type name.\n\n" .
                "Predict potential equipment failures.\n\n" .
                "Analyze trends in power, voltage, and current over time (using reading_time from the Reading table).\n\n" .
                "Use max_power_threshold and min_power_threshold (from the Profile table) to identify readings exceeding safe limits.\n\n" .
                "Predict failures only if the trend shows a sustained increase (for max threshold) or decrease (for min threshold) over at least 3 consecutive readings.\n\n" .
                "Provide recommendations.\n\n" .
                "Base recommendations on the analysis in steps 1-3.\n\n" .
                "For Energy Wastage insights: Limit recommendations to: 'Adjust device settings', 'Schedule maintenance', 'Implement energy-saving measures', 'Investigate wiring', 'Replace equipment', or 'Other'.\n\n" .
                "For Operational Failure insights: Prioritize recommendations like: 'Check device connection', 'Verify power supply', 'Inspect wiring and outlets', 'Test device functionality', or 'Contact technical support'.\n\n" .
                "Assess alert system effectiveness.\n\n" .
                "Analyze the Alert table.\n\n" .
                "Identify patterns in type, severity, and resolved status.\n\n" .
                "Calculate the average resolution time (resolved_at - alert time) for each alert type.\n\n" .
                "Reporting Requirements\n\n" .
                "Provide clear explanations for each insight that are easy to understand by non-technical users.\n\n" .
                "Write descriptions in plain language, avoiding technical jargon and complex terminology.\n\n" .
                "Use everyday language that business owners and facility managers can easily understand.\n\n" .
                "NEVER use technical alert type names, system codes, or database field names in user-facing descriptions.\n\n" .
                "Convert all technical terms to business-friendly language that explains the real-world impact.\n\n" .
                "Include specific numbers, percentages, and monetary impact where possible to make insights concrete.\n\n" .
                "Focus on the business impact and what it means for the user's operations and costs.\n\n" .
                "Start descriptions with action-oriented language like 'Your equipment is using...', 'We detected...', 'Your system shows...'.\n\n" .
                "Include key supporting data points (values from the tables) but explain them in simple terms.\n\n" .
                "When mentioning dates and times, use human-readable formats instead of technical timestamps.\n\n" .
                "Convert timestamps to friendly formats like 'yesterday at 2:30 PM', 'last Tuesday', 'this morning', '3 days ago', etc.\n\n" .
                "For date ranges, use phrases like 'over the past week', 'in the last 3 days', 'since Monday', etc.\n\n" .
                "Provide actionable recommendations from the list in step 4.\n\n" .
                "Prioritize insights as 'High', 'Medium', or 'Low' based on potential impact on cost, equipment, and operations.\n\n" .
                "Filtering\n\n" .
                "Use the IotDevice table to filter data by device_uuid. If no device_uuid is provided, analyze all devices.\n\n" .
                "IMPORTANT: When analyzing readings and generating insights, always reference the device name from the IotDevice table to apply appropriate device-specific logic for operational failure detection. Match readings to devices using socket_id relationships.\n\n" .
                "Output Structure\n\n" .
                "The response MUST be valid JSON.\n\n" .
                "The JSON should contain ONLY the labels and data types as specified in the example structure.\n\n" .
                "Do not include any descriptive text or explanations within the JSON data itself.\n\n" .
                "Use the aliases and names instead of the data ids.\n\n" .
                "The insight_type should be one of the following: 'Energy Wastage', 'Operational Failure', 'Anomaly Detection', 'Predictive Maintenance', or 'Alert System Effectiveness'.\n\n" .
                "In the supporting_data array, ALWAYS include both socket_id and device_name for each affected device to provide proper context for the insight.\n\n" .
                "Example of user-friendly descriptions:\n\n" .
                "Instead of: 'Voltage fluctuation detected with 15% variance from nominal'\n\n" .
                "Write: 'Your equipment is experiencing unstable power supply, which could damage your devices and increase electricity bills by up to 15%.'\n\n" .
                "Instead of: 'Power consumption exceeds threshold by 200W'\n\n" .
                "Write: 'One of your devices is using 200 watts more electricity than expected, costing you approximately PHP 30 extra per month.'\n\n" .
                "For Operational Failure (Device Context Aware): Instead of 'Device consuming 0W against expected 66W'\n\n" .
                "Write for Chargers: 'Your phone charger has been drawing no power for several hours during typical usage time - it may be disconnected or malfunctioning.'\n\n" .
                "Write for Servers/POS: 'Your POS terminal is showing no power consumption when it should be running - this could indicate a power failure or device malfunction that needs immediate attention.'\n\n" .
                "Write for Refrigeration: 'Your refrigerator has been off for over 2 hours, which is longer than normal - this could indicate a power issue or equipment failure that may spoil stored items.'\n\n" .
                "Instead of: 'Alert triggered at 2024-09-27 14:32:15'\n\n" .
                "Write: 'We detected an issue yesterday afternoon around 2:30 PM'\n\n" .
                "For Alert Information: Instead of 'We detected an anomaly yesterday afternoon around 3:38 PM on Energy Analytics Device - Garage. The alert type is anomaly_lstm_power_high. We recommend you check the device.'\n\n" .
                "Write: 'We detected abnormally high power usage yesterday afternoon around 3:38 PM on your Energy Analytics Device in the Garage. This could indicate equipment malfunction or inefficiency that may increase your electricity costs.'\n\n" .
                "Instead of: 'Trend observed from 2024-09-20 00:00:00 to 2024-09-27 23:59:59'\n\n" .
                "Write: 'Over the past week, we noticed a pattern in your energy usage'\n\n" .
                "CRITICAL: Convert technical alert types to user-friendly language:\n\n" .
                "- Instead of: 'anomaly_lstm_power_high' → Write: 'abnormally high power usage'\n\n" .
                "- Instead of: 'anomaly_lstm_power_low' → Write: 'unusually low power consumption'\n\n" .
                "- Instead of: 'anomaly_lstm_voltage_high' → Write: 'voltage levels higher than normal'\n\n" .
                "- Instead of: 'anomaly_lstm_voltage_low' → Write: 'voltage drops below safe levels'\n\n" .
                "- Instead of: 'anomaly_lstm_current_high' → Write: 'electrical current spikes'\n\n" .
                "- Instead of: 'anomaly_lstm_current_low' → Write: 'electrical current below expected levels'\n\n" .
                "- Instead of: 'threshold_exceeded' → Write: 'power usage exceeded safe limits'\n\n" .
                "- Instead of: 'voltage_fluctuation' → Write: 'unstable power supply'\n\n" .
                "- Instead of: 'power_surge' → Write: 'sudden power spike'\n\n" .
                "- Instead of: 'brownout' → Write: 'power dip or brownout condition'\n\n" .
                "- Instead of: 'device_offline' → Write: 'device connection lost'\n\n" .
                "NEVER include technical alert type names like 'anomaly_lstm_power_high' in user descriptions. Always translate them to plain English that explains what the issue means for the user's equipment and operations.";

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
                                    'enum' => ['Energy Wastage', 'Operational Failure', 'Anomaly Detection', 'Predictive Maintenance', 'Alert System Effectiveness']
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
                                'priority' => [
                                    'type' => 'string',
                                    'enum' => ['High', 'Medium', 'Low']
                                ]
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
                    'maxOutputTokens' => 4096,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $responseSchema
                ]
            ];

            Log::debug('GeminiService::generateInsights making API request', [
                'api_url' => $apiUrl,
                'request_body_size' => strlen(json_encode($requestBody)),
                'prompt_length' => strlen($promptText)
            ]);

            // Make the API request using Guzzle
            $client = new \GuzzleHttp\Client();
            
            try {
                $response = $client->post($apiUrl, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $requestBody,
                ]);
                
                Log::debug('GeminiService::generateInsights API request successful', [
                    'response_status' => $response->getStatusCode(),
                    'response_size' => strlen($response->getBody())
                ]);
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                Log::error('GeminiService::generateInsights API request failed', [
                    'error' => $e->getMessage(),
                    'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
                    'status_code' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null
                ]);
                throw $e;
            }

            // Clean up the temporary file
            try {
                Storage::disk('local')->delete($tempFilename);
                Log::debug('GeminiService::generateInsights temp file cleaned up', [
                    'temp_filename' => $tempFilename
                ]);
            } catch (\Exception $e) {
                Log::warning('GeminiService::generateInsights failed to clean up temp file', [
                    'temp_filename' => $tempFilename,
                    'error' => $e->getMessage()
                ]);
            }

            $isLoading = false;

            // Parse the response
            $responseBody = $response->getBody();
            Log::debug('GeminiService::generateInsights parsing API response', [
                'response_body_length' => strlen($responseBody)
            ]);
            
            $responseData = json_decode($responseBody, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('GeminiService::generateInsights failed to parse JSON response', [
                    'json_error' => json_last_error_msg(),
                    'response_body_preview' => substr($responseBody, 0, 500)
                ]);
                throw new \Exception('Failed to parse API response: ' . json_last_error_msg());
            }

            // Extract the text content from the response
            $content = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            Log::debug('GeminiService::generateInsights content extracted', [
                'content_length' => strlen($content),
                'has_content' => !empty($content)
            ]);
            
            if (empty($content)) {
                Log::error('GeminiService::generateInsights empty content in API response', [
                    'response_data' => $responseData
                ]);
                return ['error' => 'Empty content received from API'];
            }

            if ($this->isJson($content)) {
                $insights = json_decode($content, true);
                Log::info('GeminiService::generateInsights completed successfully', [
                    'user_id' => $user ? $user->id : null,
                    'insights_count' => isset($insights['insights']) ? count($insights['insights']) : 0
                ]);
                return $insights;
            }

            // Add more detailed error logging
            Log::error('GeminiService::generateInsights API response not valid JSON', [
                'content' => $content,
                'content_length' => strlen($content),
                'json_error' => json_last_error_msg(),
                'content_starts_with' => substr(trim($content), 0, 50),
                'content_ends_with' => substr(trim($content), -50)
            ]);
            return ['error' => 'The API response was not in the expected JSON format.'];
        } catch (\Exception $e) {
            Log::error('GeminiService::generateInsights failed', [
                'user_id' => $user ? $user->id : null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'exception_type' => get_class($e)
            ]);
            
            // Clean up temp file if it exists
            if (isset($tempFilename)) {
                try {
                    Storage::disk('local')->delete($tempFilename);
                } catch (\Exception $cleanupException) {
                    Log::warning('GeminiService::generateInsights failed to cleanup temp file during error handling', [
                        'temp_filename' => $tempFilename,
                        'cleanup_error' => $cleanupException->getMessage()
                    ]);
                }
            }
            
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
        // Trim whitespace that might cause issues
        $string = trim($string);
        
        // Check if string is empty
        if (empty($string)) {
            return false;
        }
        
        // Attempt to decode
        $result = json_decode($string);
        
        // Check for JSON errors and ensure we got a result
        return json_last_error() === JSON_ERROR_NONE && $result !== null;
    }
}
