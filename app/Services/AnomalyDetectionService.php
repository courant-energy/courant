<?php

namespace App\Services;

use App\Models\Reading;
use App\Models\Alert;
use App\Models\Socket;
use Illuminate\Support\Facades\Log;

class AnomalyDetectionService
{
    /**
     * Detect anomalies for a new reading
     * 
     * @param Reading $reading The new reading to analyze
     * @return array|null Returns anomaly details or null if no anomaly detected
     */
    public function detectAnomaly(Reading $reading)
    {
        $socket = $reading->socket;
        $profile = $socket->profile;
        
        if (!$profile) {
            Log::info('No profile associated with socket ID: ' . $socket->id);
            return null;
        }
        
        // Use the Profile model's method to check if the reading is outside thresholds
        $anomalies = $profile->isOutsideThresholds($reading);
        
        if (!$anomalies) {
            return null;
        }
        
        // Create alerts for detected anomalies
        $this->createAlertsForAnomalies($reading, $anomalies, $socket, $profile);
        
        return $anomalies;
    }
    
    /**
     * Create alerts for detected anomalies
     * 
     * @param Reading $reading The reading with anomalies
     * @param array $anomalies The detected anomalies
     * @param Socket $socket The socket with the anomalies
     * @param Profile $profile The profile with thresholds
     */
    protected function createAlertsForAnomalies(Reading $reading, array $anomalies, Socket $socket, $profile)
    {
        $iotDevice = $socket->iotDevice;
        
        foreach ($anomalies as $type => $anomalyData) {
            // Generate user-friendly message based on anomaly type
            $message = $this->getAnomalyMessage($type, $anomalyData);
            
            // Determine severity level
            $severity = $profile->getSeverityLevel($anomalyData['percentage']);
            
            // Create the alert
            Alert::create([
                'iot_device_id' => $iotDevice->id,
                'socket_id' => $socket->id,
                'profile_id' => $profile->id,
                'type' => 'anomaly_' . $type,
                'message' => $message,
                'data' => json_encode([
                    'reading_id' => $reading->id,
                    'anomaly_type' => $type,
                    'value' => $anomalyData['value'],
                    'threshold' => $anomalyData['threshold'],
                    'percentage_deviation' => $anomalyData['percentage'],
                    'severity' => $severity,
                    'reading_time' => $reading->reading_time
                ]),
                'severity' => $severity,
                'resolved' => false
            ]);
            
            // Log the anomaly for monitoring
            Log::channel('anomalies')->info('Anomaly detected', [
                'device_id' => $iotDevice->id,
                'socket_id' => $socket->id,
                'reading_id' => $reading->id,
                'type' => $type,
                'value' => $anomalyData['value'],
                'threshold' => $anomalyData['threshold'],
                'percentage' => $anomalyData['percentage'],
                'severity' => $severity
            ]);
        }
    }
    
    /**
     * Get a user-friendly message for an anomaly
     * 
     * @param string $type The type of anomaly
     * @param array $anomalyData The anomaly data
     * @return string A user-friendly message
     */
    protected function getAnomalyMessage($type, array $anomalyData)
    {
        switch ($type) {
            case 'power_high':
                return "Power consumption exceeds threshold: {$anomalyData['value']}W (threshold: {$anomalyData['threshold']}W, {$anomalyData['percentage']}% over)";
                
            case 'power_low':
                return "Power consumption below expected minimum: {$anomalyData['value']}W (threshold: {$anomalyData['threshold']}W, {$anomalyData['percentage']}% under)";
                
            case 'voltage_high':
                return "Voltage too high: {$anomalyData['value']}V (threshold: {$anomalyData['threshold']}V, {$anomalyData['percentage']}% over)";
                
            case 'voltage_low':
                return "Voltage too low: {$anomalyData['value']}V (threshold: {$anomalyData['threshold']}V, {$anomalyData['percentage']}% under)";
                
            case 'power_factor_low':
                return "Poor power factor detected: {$anomalyData['value']} (threshold: {$anomalyData['threshold']}, {$anomalyData['percentage']}% under)";
                
            case 'current_high':
                return "Current too high: {$anomalyData['value']}A (threshold: {$anomalyData['threshold']}A, {$anomalyData['percentage']}% over)";
                
            default:
                return "Anomaly detected in {$type}: {$anomalyData['value']} (threshold: {$anomalyData['threshold']})";
        }
    }
    
    /**
     * Process a batch of readings for anomaly detection
     * 
     * @param array $readings Array of Reading objects to process
     * @return int Number of anomalies detected
     */
    public function processBatchReadings(array $readings)
    {
        $anomalyCount = 0;
        
        foreach ($readings as $reading) {
            $anomalies = $this->detectAnomaly($reading);
            if ($anomalies) {
                $anomalyCount += count($anomalies);
            }
        }
        
        return $anomalyCount;
    }
}