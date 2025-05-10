<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Reading;
use App\Models\Profile;
use App\Models\IotDevice;
use App\Models\Socket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

class AnomalyService
{
    /**
     * URL of the Python ML server for anomaly detection
     */
    protected $mlServerUrl;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mlServerUrl = config('services.python_ml.server_url', 'http://localhost:5000');
    }

    /**
     * Process a reading for anomaly detection
     * This will dispatch the job to the queue for asynchronous processing
     *
     * @param Reading $reading The reading to process
     * @return void
     */
    public function processReadingForAnomalyDetection(Reading $reading)
    {
        \Log::info('Processing reading for anomaly detection', [
            'reading_id' => $reading->id,
            'profile_id' => $reading->profile_id
        ]);
        // Get the profile associated with the reading
        $profile = $reading->profile_id ? Profile::find($reading->profile_id) : null;

        if (!$profile) {
            Log::warning('No profile found for reading', ['reading_id' => $reading->id]);
            return;
        }

        // Dispatch job to queue for LSTM prediction
        dispatch(function () use ($reading, $profile) {
            $this->processWithLstm($reading, $profile);
        })->onQueue('anomaly_detection');

        // Dispatch job to queue for Isolation Forest
        dispatch(function () use ($reading, $profile) {
            \Log::info('Processing reading with Isolation Forest', [
                'reading_id' => $reading->id,
                'profile_id' => $profile->id
            ]);
            $this->processWithIsolationForest($reading, $profile);
        })->onQueue('anomaly_detection');
    }

    /**
     * Process a reading with LSTM model for prediction-based anomaly detection
     *
     * @param Reading $reading The reading to process
     * @param Profile $profile The profile associated with the reading
     * @return void
     */
    protected function processWithLstm(Reading $reading, Profile $profile)
    {
        try {
            $data = [
                'profile_id' => $profile->id,
                'sensitivity_level' => $profile->alert_sensitivity ?? 'medium',
                'reading_data' => [
                    'voltage' => $reading->voltage,
                    'current' => $reading->current,
                    'power' => $reading->power,
                    'frequency' => $reading->frequency,
                    'power_factor' => $reading->power_factor,
                ]
            ];
            \Log::info('Sending LSTM prediction request', [
                'data' => $data,
                'reading_id' => $reading->id
            ]);

            // Make request to ML server
            $response = Http::timeout(30)->post($this->mlServerUrl . '/predict_lstm', $data);

            if (!$response->successful()) {
                Log::error('Failed to get LSTM prediction', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'reading_id' => $reading->id
                ]);
                return;
            }

            $result = $response->json();

            // Process the LSTM response and create alerts if needed
            $this->processLstmResponse($result, $reading, $profile);
        } catch (\Exception $e) {
            Log::error('Error processing LSTM prediction', [
                'error' => $e->getMessage(),
                'reading_id' => $reading->id
            ]);
        }
    }

    /**
     * Process a reading with Isolation Forest model for anomaly detection
     *
     * @param Reading $reading The reading to process
     * @param Profile $profile The profile associated with the reading
     * @return void
     */
    protected function processWithIsolationForest(Reading $reading, Profile $profile)
    {
        \Log::info('Processing reading with Isolation Forest', [
            'reading_id' => $reading,
            'profile_id' => $profile->id
        ]);
        try {
            $data = [
                'profile_id' => $profile->id,

                'voltage' => $reading->voltage,
                'current' => $reading->current,
                'power' => $reading->power,
                'frequency' => $reading->frequency,
                'power_factor' => $reading->power_factor,

            ];

            // Make request to ML server
            $response = Http::timeout(30)->post($this->mlServerUrl . '/predict', $data);

            if (!$response->successful()) {
                Log::error('Failed to get Isolation Forest prediction', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'reading_id' => $reading->id
                ]);
                return;
            }
            \Log::info('Received Isolation Forest prediction response', [
                'response' => $response->json(),
                'reading_id' => $reading->id
            ]);

            $result = $response->json();

            // Process the Isolation Forest response and create alerts if needed
            $this->processIsolationForestResponse($result, $reading, $profile);
        } catch (\Exception $e) {
            Log::error('Error processing Isolation Forest prediction', [
                'error' => $e->getMessage(),
                'reading_id' => $reading->id
            ]);
        }
    }

    /**
     * Process the LSTM prediction response and create alerts if needed
     *
     * @param array $response The response from the LSTM prediction endpoint
     * @param Reading $reading The reading being analyzed
     * @param Profile $profile The profile associated with the reading
     * @return void
     */
    protected function processLstmResponse($response, Reading $reading, Profile $profile)
    {
        // Check if an anomaly is detected (anything other than "within_prediction")
        if ($response['overall_status'] !== 'within_prediction') {

            // Identify the significant deviations for a better alert message
            $significantDeviations = $response['interpretation']['significant_deviations_summary'] ?? [];

            // Create a user-friendly alert message
            $alertMessage = $this->createLstmAlertMessage($response, $significantDeviations);

            // Determine severity based on the overall_status
            $severity = $this->determineSeverityFromLstmStatus($response['overall_status']);

            // Create the alert in the database
            $alert = $this->createAlert(
                'anomaly_lstm_' . $this->getAnomalyTypeFromLstm($response),
                $alertMessage,
                $severity,
                $reading,
                $profile
            );

            // Send Filament notification if profile has notifications enabled
            if ($profile->notify_on_anomaly) {
                $this->sendFilamentNotification(
                    "LSTM Anomaly Detected!",
                    $alertMessage,
                    $alert,
                    $reading,
                    $profile,
                    $response
                );
            }
        }
    }

    /**
     * Process the Isolation Forest prediction response and create alerts if needed
     *
     * @param array $response The response from the Isolation Forest prediction endpoint
     * @param Reading $reading The reading being analyzed
     * @param Profile $profile The profile associated with the reading
     * @return void
     */
    protected function processIsolationForestResponse($response, Reading $reading, Profile $profile)
    {
        // Check if an anomaly is detected
        if ($response['result'] === 'anomaly') {

            // Create a user-friendly alert message
            $alertMessage = $this->createIsolationForestAlertMessage($response);

            // Determine severity based on the anomaly score
            $severity = $this->determineSeverityFromAnomalyScore($response['score']);

            // Create the alert in the database
            $alert = $this->createAlert(
                'anomaly_isolation_forest',
                $alertMessage,
                $severity,
                $reading,
                $profile
            );

            // Send Filament notification if profile has notifications enabled
            if ($profile->notify_on_anomaly) {
                $this->sendFilamentNotification(
                    "Anomaly Detected!",
                    $alertMessage,
                    $alert,
                    $reading,
                    $profile,
                    $response
                );
            }
        }
    }

    /**
     * Create an alert message for LSTM-based anomalies
     *
     * @param array $response The LSTM response
     * @param array $significantDeviations The significant deviations from the response
     * @return string The formatted alert message
     */
    protected function createLstmAlertMessage($response, $significantDeviations)
    {
        $message = "Potential anomaly detected in sensor readings. ";

        if (!empty($significantDeviations)) {
            foreach ($significantDeviations as $field => $deviation) {
                $fieldName = $this->getReadableName($field);
                $status = str_replace('_', ' ', $deviation['status']);

                $message .= "{$fieldName} reading of {$deviation['actual']} is {$status} ";
                $message .= "the predicted value of " . round($deviation['predicted'], 2) . ". ";
            }
        } else {
            // If no significant deviations but still an anomaly, create a generic message
            $message .= "Multiple parameters are outside their expected ranges. ";
        }

        // Add an interpretation based on the status_meaning
        $statusMeaning = $response['interpretation']['status_meaning'][$response['overall_status']] ?? '';
        if ($statusMeaning) {
            $message .= "Analysis: $statusMeaning";
        }

        return $message;
    }

    /**
     * Create an alert message for Isolation Forest-based anomalies
     *
     * @param array $response The Isolation Forest response
     * @return string The formatted alert message
     */
    protected function createIsolationForestAlertMessage($response)
    {
        $message = "Anomaly detected: " . ($response['explanation']['message'] ?? 'Unusual patterns in sensor readings. ');

        // Add feature values that might have contributed to the anomaly
        if (isset($response['explanation']['feature_values'])) {
            $message .= "Unusual readings include: ";

            foreach ($response['explanation']['feature_values'] as $field => $value) {
                $fieldName = $this->getReadableName($field);
                $message .= "{$fieldName}: {$value}, ";
            }

            // Remove the trailing comma and space
            $message = rtrim($message, ", ");
            $message .= ".";
        }

        // Add anomaly score information
        if (isset($response['explanation']['anomaly_score'])) {
            $score = round($response['explanation']['anomaly_score'], 2);
            $message .= " (Anomaly score: {$score})";
        }

        return $message;
    }

    /**
     * Create an alert record in the database
     *
     * @param string $type The type of alert
     * @param string $message The alert message
     * @param string $severity The alert severity
     * @param Reading $reading The reading associated with the alert
     * @param Profile $profile The profile associated with the reading
     * @return Alert The created alert
     */
    protected function createAlert($type, $message, $severity, Reading $reading, Profile $profile)
    {
        // Get the socket and IoT device
        $socket = $reading->socket;
        $iotDevice = $socket ? $socket->iotDevice : null;

        // Prepare alert data
        $alertData = [
            'type' => $type,
            'message' => $message,
            'severity' => $severity,
            'reading_id' => $reading->id,
            'socket_id' => $reading->socket_id,
            'profile_id' => $profile->id,
            'data' => [
                'voltage' => $reading->voltage,
                'current' => $reading->current,
                'power' => $reading->power,
                'frequency' => $reading->frequency,
                'power_factor' => $reading->power_factor,
                'detected_at' => now()->toIso8601String()
            ],
            'resolved' => false,
        ];

        if ($iotDevice) {
            $alertData['iot_device_id'] = $iotDevice->id;
        }

        // Create the alert
        return Alert::create($alertData);
    }

    /**
     * Send a Filament notification for an anomaly
     *
     * @param string $title The notification title
     * @param string $body The notification body
     * @param Alert $alert The alert record
     * @param Reading $reading The reading associated with the alert
     * @param Profile $profile The profile associated with the reading
     * @param array $response The original ML response data
     * @return void
     */
    protected function sendFilamentNotification($title, $body, Alert $alert, Reading $reading, Profile $profile, array $response)
    {
        try {
            // Get users to notify (profile owner)
            $user = $profile->user;

            if (!$user) {
                return;
            }

            // Create a Filament notification
            $notification = FilamentNotification::make()
                ->title($title)
                ->body($body)
                ->danger() // Use the danger (red) style for anomaly alerts
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('View Details')
                        ->url(route('filament.admin.resources.alerts.view', ['record' => $alert->id])),
                    \Filament\Notifications\Actions\Action::make('resolve')
                        ->label('Mark as Resolved')
                        ->url(route('filament.admin.resources.alerts.edit', ['record' => $alert->id])),
                ])
                ->icon('heroicon-o-exclamation-triangle')
                ->persistent();

            // Send the notification to the user
            $notification->sendToDatabase($user);
        } catch (\Exception $e) {
            Log::error('Failed to send Filament notification', [
                'error' => $e->getMessage(),
                'alert_id' => $alert->id
            ]);
        }
    }

    /**
     * Get a human-readable name for a reading field
     *
     * @param string $field The field name
     * @return string The human-readable name
     */
    protected function getReadableName($field)
    {
        $readableNames = [
            'voltage' => 'Voltage',
            'current' => 'Current',
            'power' => 'Power Consumption',
            'frequency' => 'Frequency',
            'power_factor' => 'Power Factor'
        ];

        return $readableNames[$field] ?? ucfirst($field);
    }

    /**
     * Determine the severity of an LSTM-based anomaly from its status
     *
     * @param string $status The overall status from the LSTM response
     * @return string The alert severity (high, medium, or low)
     */
    protected function determineSeverityFromLstmStatus($status)
    {
        switch ($status) {
            case 'significantly_above':
            case 'significantly_below':
            case 'multiple_significant_deviations':
                return 'high';

            case 'above_prediction':
            case 'below_prediction':
                return 'medium';

            default:
                return 'low';
        }
    }

    /**
     * Determine the severity based on an anomaly score
     *
     * @param float $score The anomaly score from Isolation Forest
     * @return string The alert severity (high, medium, or low)
     */
    protected function determineSeverityFromAnomalyScore($score)
    {
        // For Isolation Forest, more negative scores indicate stronger anomalies
        // Adjust these thresholds based on your specific use case
        if ($score < -0.8) {
            return 'high';
        } elseif ($score < -0.5) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get the anomaly type from LSTM response
     *
     * @param array $response The LSTM response
     * @return string The anomaly type
     */
    protected function getAnomalyTypeFromLstm($response)
    {
        // Default type
        $type = 'general';

        // Check if there are significant deviations
        $deviations = $response['interpretation']['significant_deviations_summary'] ?? [];

        if (empty($deviations)) {
            return $type;
        }

        // Check which parameter has the most significant deviation
        foreach ($deviations as $field => $deviation) {
            $status = $deviation['status'] ?? '';

            if ($field === 'power' && strpos($status, 'above') !== false) {
                return 'power_high';
            }

            if ($field === 'power' && strpos($status, 'below') !== false) {
                return 'power_low';
            }

            if ($field === 'voltage' && strpos($status, 'above') !== false) {
                return 'voltage_high';
            }

            if ($field === 'voltage' && strpos($status, 'below') !== false) {
                return 'voltage_low';
            }

            if ($field === 'current' && strpos($status, 'above') !== false) {
                return 'current_high';
            }

            if ($field === 'power_factor' && strpos($status, 'below') !== false) {
                return 'power_factor_low';
            }
        }

        return $type;
    }
}
