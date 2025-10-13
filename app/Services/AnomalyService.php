<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Profile;
use App\Models\Reading;
use App\Models\Socket;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

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
     * NOTE: Refactored to use single LSTM real-time processing only
     *
     * @param  Reading  $reading  The reading to process
     * @return void
     */
    public function processReadingForAnomalyDetection(Reading $reading)
    {
        // \Log::info('Processing reading for anomaly detection', [
        //     'reading_id' => $reading->id,
        //     'profile_id' => $reading->profile_id
        // ]);
        // Get the profile associated with the reading
        $profile = $reading->profile_id ? Profile::find($reading->profile_id) : null;

        if (! $profile) {
            Log::warning('No profile found for reading', ['reading_id' => $reading->id]);

            return;
        }

        if ($profile->anomaly_detection_enabled == false) {
            // Log::info('Anomaly detection is disabled for this profile', ['profile_id' => $profile->id]);
            return;
        }

        // Dispatch single job to queue for LSTM real-time anomaly detection only
        // Isolation Forest processing is now handled by scheduled batch command
        dispatch(function () use ($reading, $profile) {
            $this->processRealTimeAnomaly($reading, $profile);
        })->onQueue('anomaly_detection');
    }

    /**
     * Process a reading with LSTM model for real-time anomaly detection
     * This is the primary real-time detector replacing dual dispatch
     *
     * @param  Reading  $reading  The reading to process
     * @param  Profile  $profile  The profile associated with the reading
     * @return void
     */
    protected function processRealTimeAnomaly(Reading $reading, Profile $profile)
    {
        try {
            $data = [
                'profile_id' => $profile->id,
                'voltage' => $reading->voltage,
                'current' => $reading->current,
                'power' => $reading->power,
                'frequency' => $reading->frequency,
                'power_factor' => $reading->power_factor,
                'timestamp' => $reading->created_at ? $reading->created_at->toIso8601String() : now()->toIso8601String(),
            ];

            Log::info('Sending LSTM real-time prediction request', [
                'data' => $data,
                'reading_id' => $reading->id,
            ]);

            // Make request to ML server for LSTM prediction only
            $response = Http::timeout(30)->post($this->mlServerUrl.'/predict_lstm', $data);

            if (! $response->successful()) {
                Log::error('Failed to get LSTM prediction', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'reading_id' => $reading->id,
                ]);

                return;
            }

            $result = $response->json();

            // Process the LSTM response and create alerts if needed
            $this->processLstmResponse($result, $reading, $profile);

        } catch (\Exception $e) {
            Log::error('Error processing real-time LSTM prediction', [
                'error' => $e->getMessage(),
                'reading_id' => $reading->id,
            ]);
        }
    }

    /**
     * Process the LSTM prediction response and create alerts if needed
     *
     * @param  array  $response  The response from the LSTM prediction endpoint
     * @param  Reading  $reading  The reading being analyzed
     * @param  Profile  $profile  The profile associated with the reading
     * @return void
     */
    protected function processLstmResponse($response, Reading $reading, Profile $profile)
    {
        Log::info('Processing LSTM response for real-time anomaly detection');

        // Check if an anomaly is detected (anything other than "within_prediction")
        if ($response['overall_status'] !== 'within_prediction') {

            // Log::info('LSTM anomaly detected', [
            //     'interpretation' => $response['interpretation'],
            //     'overall_status' => $response['overall_status'],
            //     'reading_id' => $reading->id,
            //     'profile_id' => $profile->id
            // ]);

            // Extract significant deviations from both old and new format for compatibility
            $significantDeviations = $response['interpretation']['significant_deviations_summary'] ?? [];

            // Also extract from details section with relative error information
            $detailsWithErrors = $this->extractDeviationsWithRelativeErrors($response['details'] ?? []);

            Log::info('Significant deviations detected', [
                'significant_deviations' => $significantDeviations,
                'details_with_errors' => $detailsWithErrors,
                'reading_id' => $reading->id,
            ]);

            // Create enhanced, actionable alert message with business context
            $alertDetails = $this->createEnhancedLstmAlertMessage($response, $significantDeviations, $reading, $profile, $detailsWithErrors);

            // Determine severity based on the overall_status
            $severity = $this->determineSeverityFromLstmStatus($response['overall_status']);

            // Create the alert in the database with enhanced details
            $alert = $this->createAlert(
                'anomaly_lstm_'.$this->getAnomalyTypeFromLstm($response),
                $alertDetails['message'],
                $severity,
                $reading,
                $profile,
                $alertDetails['metadata'] // Additional context for the alert
            );

            // Send enhanced notification if profile has notifications enabled
            if ($profile->notify_on_anomaly) {
                $this->sendFilamentNotification(
                    $alertDetails['title'],
                    $alertDetails['message'],
                    $alert,
                    $reading,
                    $profile,
                    $response
                );
            }
        }
    }

    /**
     * Extract deviations with relative error information from response details
     *
     * @param  array  $details  The details section from ML response
     * @return array Deviations with relative error data
     */
    protected function extractDeviationsWithRelativeErrors($details)
    {
        $deviationsWithErrors = [];

        foreach ($details as $field => $data) {
            if (isset($data['status']) && $data['status'] !== 'within_prediction') {
                $deviationsWithErrors[$field] = [
                    'actual' => $data['actual'] ?? 0,
                    'predicted' => $data['predicted'] ?? 0,
                    'status' => $data['status'] ?? 'unknown',
                    'relative_error_percent' => $data['relative_error_percent'] ?? 0,
                ];
            }
        }

        return $deviationsWithErrors;
    }

    /**
     * Process the Isolation Forest prediction response and create alerts if needed
     *
     * @param  array  $response  The response from the Isolation Forest prediction endpoint
     * @param  Reading  $reading  The reading being analyzed
     * @param  Profile  $profile  The profile associated with the reading
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
                    'Anomaly Detected!',
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
     * Create an enhanced, actionable alert message for LSTM-based anomalies
     *
     * @param  array  $response  The LSTM response
     * @param  array  $significantDeviations  The significant deviations from the response
     * @param  Reading  $reading  The reading being analyzed
     * @param  Profile  $profile  The profile associated with the reading
     * @param  array  $detailsWithErrors  Deviations with relative error information
     * @return array Enhanced alert details with title, message, and metadata
     */
    protected function createEnhancedLstmAlertMessage($response, $significantDeviations, Reading $reading, Profile $profile, $detailsWithErrors = [])
    {
        Log::info('Creating enhanced LSTM alert message');

        // Determine the primary anomaly type and equipment context
        $anomalyType = $this->getAnomalyTypeFromLstm($response);
        $equipmentName = $profile->name ?? 'Equipment';
        $socket = $reading->socket;
        $socketName = $socket ? "Socket #{$socket->assigned_id}" : 'Unknown Socket';

        // Create actionable title
        $title = $this->createActionableTitle($anomalyType, $equipmentName);

        // Build business-context message using relative error data when available
        $message = $this->buildBusinessContextMessage($anomalyType, $significantDeviations, $reading, $profile, $detailsWithErrors);

        // Add suggested next steps
        $nextSteps = $this->getSuggestedNextSteps($anomalyType, $response['overall_status']);
        $message .= "\n\nRecommendation: ".$nextSteps;

        // Extract threshold information from response if available
        $thresholds = $response['interpretation']['thresholds'] ?? [];

        // Prepare comprehensive metadata for machine analysis
        $metadata = [
            'anomaly_type' => $anomalyType,
            'overall_status' => $response['overall_status'],
            'equipment_profile' => $profile->name,
            'socket_location' => $socketName,
            'significant_deviations' => $significantDeviations,
            'relative_error_deviations' => $detailsWithErrors, // New: relative error data
            'prediction_accuracy' => $this->calculatePredictionAccuracy($response),
            'business_impact_level' => $this->assessBusinessImpact($anomalyType, $response['overall_status']),
            'detection_timestamp' => now()->toIso8601String(),
            'sensitivity_thresholds' => $thresholds, // New: threshold information
            'ml_response_data' => $response,
        ];

        return [
            'title' => $title,
            'message' => $message,
            'metadata' => $metadata,
        ];
    }

    /**
     * Create an actionable title for the alert
     */
    protected function createActionableTitle($anomalyType, $equipmentName)
    {
        $titles = [
            'power_high' => "High Power Draw Alert: {$equipmentName}",
            'power_low' => "Low Power Alert: {$equipmentName}",
            'voltage_high' => "High Voltage Alert: {$equipmentName}",
            'voltage_low' => "Low Voltage Alert: {$equipmentName}",
            'current_high' => "High Current Draw Alert: {$equipmentName}",
            'power_factor_low' => "Poor Power Factor Alert: {$equipmentName}",
            'general' => "Equipment Anomaly Alert: {$equipmentName}",
        ];

        return $titles[$anomalyType] ?? $titles['general'];
    }

    /**
     * Build business context message explaining the anomaly in plain language
     */
    protected function buildBusinessContextMessage($anomalyType, $significantDeviations, Reading $reading, Profile $profile, $detailsWithErrors = [])
    {
        $message = '';

        // Use relative error data if available, otherwise fall back to legacy format
        $deviationsToProcess = ! empty($detailsWithErrors) ? $detailsWithErrors : $significantDeviations;

        if (! empty($deviationsToProcess)) {
            foreach ($deviationsToProcess as $field => $deviation) {
                $fieldName = $this->getReadableName($field);
                $actual = round($deviation['actual'], 2);
                $predicted = round($deviation['predicted'], 2);

                // Use relative error percentage if available, otherwise calculate it
                if (isset($deviation['relative_error_percent'])) {
                    $relativeErrorPercent = round($deviation['relative_error_percent'], 1);
                    $direction = $actual > $predicted ? 'higher' : 'lower';

                    $message .= "The {$fieldName} reading of {$actual} is {$relativeErrorPercent}% {$direction} than the predicted baseline of {$predicted}. ";
                } else {
                    // Legacy format calculation
                    $percentChange = $predicted != 0 ? round((($actual - $predicted) / $predicted) * 100, 1) : 0;
                    $direction = $actual > $predicted ? 'higher' : 'lower';

                    $message .= "The {$fieldName} reading of {$actual} is {$percentChange}% {$direction} than the expected baseline of {$predicted}. ";
                }

                // Add business context based on the field
                $message .= $this->getFieldBusinessContext($field, $actual, $predicted, $direction);
            }
        } else {
            $message = 'Multiple equipment parameters are showing unusual patterns compared to learned baselines. ';
        }

        // Add equipment-specific interpretation
        $message .= $this->getEquipmentSpecificInterpretation($anomalyType, $reading);

        return $message;
    }

    /**
     * Get business context for specific fields
     */
    protected function getFieldBusinessContext($field, $actual, $predicted, $direction)
    {
        $contexts = [
            'power' => $direction === 'higher' ?
                'This increased power consumption may indicate equipment inefficiency or potential mechanical issues. ' :
                'This reduced power consumption may indicate equipment underperformance or operational changes. ',
            'current' => $direction === 'higher' ?
                'Elevated current draw could signal motor strain, bearing issues, or electrical problems. ' :
                'Reduced current may indicate equipment not operating at full capacity. ',
            'voltage' => $direction === 'higher' ?
                'Higher voltage could cause equipment damage and should be investigated immediately. ' :
                'Low voltage can cause equipment malfunction and inefficient operation. ',
            'power_factor' => $direction === 'lower' ?
                'Poor power factor indicates inefficient power usage and may increase energy costs. ' :
                'Improved power factor suggests better electrical efficiency. ',
        ];

        return $contexts[$field] ?? 'This deviation may affect equipment performance and should be monitored. ';
    }

    /**
     * Get equipment-specific interpretation
     */
    protected function getEquipmentSpecificInterpretation($anomalyType, Reading $reading)
    {
        switch ($anomalyType) {
            case 'power_high':
                return 'This may indicate increased mechanical load, wear, or electrical inefficiency.';
            case 'current_high':
                return 'High current draw often signals mechanical binding, bearing wear, or electrical faults.';
            case 'voltage_high':
            case 'voltage_low':
                return 'Voltage irregularities can cause equipment damage and should be addressed promptly.';
            case 'power_factor_low':
                return 'Poor power factor increases energy costs and may indicate reactive load issues.';
            default:
                return 'Equipment performance has deviated from normal operating patterns.';
        }
    }

    /**
     * Get suggested next steps based on anomaly type
     */
    protected function getSuggestedNextSteps($anomalyType, $overallStatus)
    {
        $urgency = in_array($overallStatus, ['significantly_above', 'significantly_below', 'multiple_significant_deviations']) ? 'immediate' : 'scheduled';

        $steps = [
            'power_high' => $urgency === 'immediate' ?
                'Immediately inspect equipment for mechanical binding or electrical issues. Consider reducing load if safe to do so.' :
                'Schedule equipment inspection to check for efficiency degradation or increased load conditions.',
            'current_high' => $urgency === 'immediate' ?
                'Immediately check for mechanical obstruction, bearing issues, or electrical faults. Consider shutting down if unsafe.' :
                'Schedule maintenance inspection focusing on bearings, alignment, and electrical connections.',
            'voltage_high' => 'Immediately check electrical supply and consider protective measures to prevent equipment damage.',
            'voltage_low' => 'Check electrical supply quality and consider voltage regulation measures.',
            'power_factor_low' => 'Schedule electrical system review to identify and correct reactive load issues.',
        ];

        return $steps[$anomalyType] ?? ($urgency === 'immediate' ?
            'Immediately inspect equipment operation and consider safety shutdown if conditions worsen.' :
            'Schedule equipment inspection to identify the cause of the operational deviation.');
    }

    /**
     * Calculate prediction accuracy for metadata
     */
    protected function calculatePredictionAccuracy($response)
    {
        // Try to get relative error data from prediction_errors first (new format)
        if (isset($response['prediction_errors'])) {
            $totalError = 0;
            $count = 0;

            foreach ($response['prediction_errors'] as $field => $errorData) {
                if (isset($errorData['relative_error'])) {
                    $totalError += $errorData['relative_error'];
                    $count++;
                }
            }

            if ($count > 0) {
                return max(0, 100 - ($totalError / $count * 100));
            }
        }

        // Fall back to legacy calculation
        $deviations = $response['interpretation']['significant_deviations_summary'] ?? [];
        if (empty($deviations)) {
            return 100;
        }

        $totalError = 0;
        $count = 0;

        foreach ($deviations as $deviation) {
            $predicted = $deviation['predicted'];
            $actual = $deviation['actual'];
            if ($predicted != 0) {
                $error = abs(($actual - $predicted) / $predicted);
                $totalError += $error;
                $count++;
            }
        }

        return $count > 0 ? max(0, 100 - ($totalError / $count * 100)) : 100;
    }

    /**
     * Assess business impact level
     */
    protected function assessBusinessImpact($anomalyType, $overallStatus)
    {
        $criticalTypes = ['voltage_high', 'current_high'];
        $significantStatuses = ['significantly_above', 'significantly_below', 'multiple_significant_deviations'];

        if (in_array($anomalyType, $criticalTypes) && in_array($overallStatus, $significantStatuses)) {
            return 'critical';
        } elseif (in_array($anomalyType, $criticalTypes) || in_array($overallStatus, $significantStatuses)) {
            return 'high';
        } else {
            return 'medium';
        }
    }

    /**
     * Create an alert message for LSTM-based anomalies (legacy method, kept for compatibility)
     *
     * @param  array  $response  The LSTM response
     * @param  array  $significantDeviations  The significant deviations from the response
     * @return string The formatted alert message
     */
    protected function createLstmAlertMessage($response, $significantDeviations)
    {
        Log::info('Creating LSTM alert message');
        $message = 'Potential anomaly detected in sensor readings. ';

        if (! empty($significantDeviations)) {
            foreach ($significantDeviations as $field => $deviation) {
                $fieldName = $this->getReadableName($field);
                $status = str_replace('_', ' ', $deviation['status']);

                $message .= "{$fieldName} reading of {$deviation['actual']} is {$status} ";
                $message .= 'the predicted value of '.round($deviation['predicted'], 2).'. ';
            }
        } else {
            // If no significant deviations but still an anomaly, create a generic message
            $message .= 'Multiple parameters are outside their expected ranges. ';
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
     * @param  array  $response  The Isolation Forest response
     * @return string The formatted alert message
     */
    protected function createIsolationForestAlertMessage($response)
    {
        $message = 'Anomaly detected: '.($response['explanation']['message'] ?? 'Unusual patterns in sensor readings. ');

        // Add feature values that might have contributed to the anomaly
        if (isset($response['explanation']['feature_values'])) {
            $message .= 'Unusual readings include: ';

            foreach ($response['explanation']['feature_values'] as $field => $value) {
                $fieldName = $this->getReadableName($field);
                $message .= "{$fieldName}: {$value}, ";
            }

            // Remove the trailing comma and space
            $message = rtrim($message, ', ');
            $message .= '.';
        }

        // Add anomaly score information
        if (isset($response['explanation']['anomaly_score'])) {
            $score = round($response['explanation']['anomaly_score'], 2);
            $message .= " (Anomaly score: {$score})";
        }

        return $message;
    }

    /**
     * Create an alert record in the database with enhanced metadata support
     *
     * @param  string  $type  The type of alert
     * @param  string  $message  The alert message
     * @param  string  $severity  The alert severity
     * @param  Reading  $reading  The reading associated with the alert
     * @param  Profile  $profile  The profile associated with the reading
     * @param  array  $metadata  Additional metadata for the alert (optional)
     * @return Alert The created alert
     */
    protected function createAlert($type, $message, $severity, Reading $reading, Profile $profile, array $metadata = [])
    {
        // Get the socket and IoT device
        $socket = $reading->socket;
        $iotDevice = $socket ? $socket->iotDevice : null;

        // Prepare enhanced alert data with metadata
        $alertData = [
            'type' => $type,
            'message' => $message,
            'severity' => $severity,
            'reading_id' => $reading->id,
            'socket_id' => $reading->socket_id,
            'profile_id' => $profile->id,
            'data' => array_merge([
                'voltage' => $reading->voltage,
                'current' => $reading->current,
                'power' => $reading->power,
                'frequency' => $reading->frequency,
                'power_factor' => $reading->power_factor,
                'detected_at' => now()->toIso8601String(),
            ], $metadata), // Merge additional metadata
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
     * @param  string  $title  The notification title
     * @param  string  $body  The notification body
     * @param  Alert  $alert  The alert record
     * @param  Reading  $reading  The reading associated with the alert
     * @param  Profile  $profile  The profile associated with the reading
     * @param  array  $response  The original ML response data
     * @return void
     */
    protected function sendFilamentNotification($title, $body, Alert $alert, Reading $reading, Profile $profile, array $response)
    {
        Log::info('Trying to send Filament notification', [
            'alert_id' => $alert->id,
            'profile_id' => $profile->id,
            'reading_id' => $reading->id,
        ]);
        try {
            // Get users to notify (profile owner)
            $user = $profile->user;

            if (! $user) {
                return;
            }

            // Create a Filament notification
            $notification = FilamentNotification::make()
                ->title($title)
                ->body($body)
                ->danger() // Use the danger (red) style for anomaly alerts
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('View Sockets')
                        ->url(route('filament.app.resources.sockets.index', $alert->id)),
                ])
                ->icon('heroicon-o-exclamation-triangle')
                ->sendToDatabase($user);
        } catch (\Exception $e) {
            Log::error('Failed to send Filament notification', [
                'error' => $e->getMessage(),
                'alert_id' => $alert->id,
            ]);
        }
    }

    /**
     * Get a human-readable name for a reading field
     *
     * @param  string  $field  The field name
     * @return string The human-readable name
     */
    protected function getReadableName($field)
    {
        $readableNames = [
            'voltage' => 'Voltage',
            'current' => 'Current',
            'power' => 'Power Consumption',
            'frequency' => 'Frequency',
            'power_factor' => 'Power Factor',
        ];

        return $readableNames[$field] ?? ucfirst($field);
    }

    /**
     * Determine the severity of an LSTM-based anomaly from its status
     *
     * @param  string  $status  The overall status from the LSTM response
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
     * @param  float  $score  The anomaly score from Isolation Forest
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
     * @param  array  $response  The LSTM response
     * @return string The anomaly type
     */
    protected function getAnomalyTypeFromLstm($response)
    {
        Log::info('Getting anomaly type from LSTM response');
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
