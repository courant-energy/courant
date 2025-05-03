<?php

namespace App\Services;

use App\Models\Reading;
use App\Models\Alert;
use App\Models\IotDevice;
use App\Models\Profile;
use App\Models\Socket;
use Illuminate\Support\Facades\Log;

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
     *
     * @param string $format Format to export data (csv or json)
     * @param array $filters Optional filters to apply to queries
     * @param App\Models\User $user Whether to restrict data to current authenticated user
     * @return array|string All data in requested format
     */
    public function exportAllData(string $format = 'json', array $filters = [],$user = null)
    {
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
}
