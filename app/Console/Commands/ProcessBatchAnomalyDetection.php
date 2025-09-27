<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reading;
use App\Models\Profile;
use App\Models\Alert;
use App\Services\AnomalyService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessBatchAnomalyDetection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'anomaly:batch-detect 
                           {--hours=24 : Number of hours to look back for analysis}
                           {--profile= : Specific profile ID to process (optional)}
                           {--dry-run : Run without creating alerts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run batch Isolation Forest anomaly detection and LSTM error validation';

    /**
     * ML Server URL
     */
    protected $mlServerUrl;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mlServerUrl = config('services.python_ml.server_url', 'http://localhost:5000');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        $profileId = $this->option('profile');
        $dryRun = $this->option('dry-run');
        
        $this->info("Starting batch anomaly detection for the last {$hours} hours...");
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE: No alerts will be created.");
        }

        // Get the time range for analysis
        $endTime = Carbon::now();
        $startTime = $endTime->copy()->subHours($hours);
        
        // Get profiles to process
        $profiles = $profileId 
            ? Profile::where('id', $profileId)->where('anomaly_detection_enabled', true)->get()
            : Profile::where('anomaly_detection_enabled', true)->get();
            
        if ($profiles->isEmpty()) {
            $this->error('No profiles found for batch processing.');
            return;
        }
        
        $this->info("Processing " . $profiles->count() . " profiles...");
        
        $totalAnomalies = 0;
        $totalReadings = 0;
        
        foreach ($profiles as $profile) {
            $this->line("Processing profile: {$profile->name} (ID: {$profile->id})");
            
            $result = $this->processProfileBatch($profile, $startTime, $endTime, $dryRun);
            $totalAnomalies += $result['anomalies'];
            $totalReadings += $result['readings'];
            
            $this->info("  - Processed {$result['readings']} readings, found {$result['anomalies']} anomalies");
        }
        
        $this->info("Batch processing completed!");
        $this->info("Total readings processed: {$totalReadings}");
        $this->info("Total anomalies detected: {$totalAnomalies}");
        
        if ($totalAnomalies > 0 && !$dryRun) {
            $this->info("Created {$totalAnomalies} new alerts in the database.");
        }
    }
    
    /**
     * Process batch anomaly detection for a specific profile
     */
    protected function processProfileBatch(Profile $profile, Carbon $startTime, Carbon $endTime, bool $dryRun)
    {
        // Get readings for this profile in the time range
        $readings = Reading::where('profile_id', $profile->id)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->orderBy('created_at')
            ->get();
            
        if ($readings->isEmpty()) {
            $this->warn("  No readings found for profile {$profile->name}");
            return ['anomalies' => 0, 'readings' => 0];
        }
        
        $anomalyCount = 0;
        $readingCount = $readings->count();
        
        // Process in batches to avoid memory issues for large datasets
        $batchSize = 100;
        $batches = $readings->chunk($batchSize);
        
        foreach ($batches as $batch) {
            // Run Isolation Forest batch analysis
            $isoForestResults = $this->runIsolationForestBatch($profile, $batch);
            
            // Run LSTM error validation
            $lstmValidationResults = $this->runLstmErrorValidation($profile, $batch);
            
            // Process results and create alerts
            foreach ($batch as $index => $reading) {
                $isoResult = $isoForestResults[$index] ?? null;
                $lstmResult = $lstmValidationResults[$index] ?? null;
                
                if ($this->shouldCreateBatchAlert($isoResult, $lstmResult, $reading)) {
                    if (!$dryRun) {
                        $this->createBatchAlert($isoResult, $lstmResult, $reading, $profile);
                    }
                    $anomalyCount++;
                }
            }
        }
        
        return ['anomalies' => $anomalyCount, 'readings' => $readingCount];
    }
    
    /**
     * Run Isolation Forest analysis on a batch of readings
     */
    protected function runIsolationForestBatch(Profile $profile, $readings)
    {
        $results = [];
        
        foreach ($readings as $reading) {
            try {
                $data = [
                    'profile_id' => $profile->id,
                    'voltage' => $reading->voltage,
                    'current' => $reading->current,
                    'power' => $reading->power,
                    'frequency' => $reading->frequency,
                    'power_factor' => $reading->power_factor,
                    'timestamp' => $reading->created_at->toIso8601String(),
                ];
                
                $response = Http::timeout(30)->post($this->mlServerUrl . '/predict', $data);
                
                if ($response->successful()) {
                    $results[] = $response->json();
                } else {
                    Log::warning("Failed Isolation Forest prediction in batch", [
                        'profile_id' => $profile->id,
                        'reading_id' => $reading->id,
                        'status' => $response->status()
                    ]);
                    $results[] = null;
                }
            } catch (\Exception $e) {
                Log::error("Error in Isolation Forest batch prediction", [
                    'profile_id' => $profile->id,
                    'reading_id' => $reading->id,
                    'error' => $e->getMessage()
                ]);
                $results[] = null;
            }
        }
        
        return $results;
    }
    
    /**
     * Run LSTM error validation on a batch of readings
     */
    protected function runLstmErrorValidation(Profile $profile, $readings)
    {
        $results = [];
        $errors = [];
        
        foreach ($readings as $reading) {
            try {
                $data = [
                    'profile_id' => $profile->id,
                    'voltage' => $reading->voltage,
                    'current' => $reading->current,
                    'power' => $reading->power,
                    'frequency' => $reading->frequency,
                    'power_factor' => $reading->power_factor,
                    'timestamp' => $reading->created_at->toIso8601String(),
                ];
                
                $response = Http::timeout(30)->post($this->mlServerUrl . '/predict_lstm', $data);
                
                if ($response->successful()) {
                    $result = $response->json();
                    
                    // Calculate prediction errors for each feature
                    $predictionErrors = $this->calculatePredictionErrors($result);
                    $result['prediction_errors'] = $predictionErrors;
                    
                    $errors[] = $predictionErrors;
                    $results[] = $result;
                } else {
                    $results[] = null;
                    $errors[] = null;
                }
            } catch (\Exception $e) {
                Log::error("Error in LSTM error validation", [
                    'profile_id' => $profile->id,
                    'reading_id' => $reading->id,
                    'error' => $e->getMessage()
                ]);
                $results[] = null;
                $errors[] = null;
            }
        }
        
        // Analyze error distribution to detect model drift
        $this->analyzeErrorDistribution($profile, $errors);
        
        return $results;
    }
    
    /**
     * Calculate prediction errors from LSTM response
     */
    protected function calculatePredictionErrors($lstmResponse)
    {
        $errors = [];
        
        // Check if the enhanced prediction_errors field is available (Phase 2 enhancement)
        if (isset($lstmResponse['prediction_errors'])) {
            // Use the new prediction_errors field directly
            foreach ($lstmResponse['prediction_errors'] as $field => $errorData) {
                $errors[$field] = [
                    'absolute_error' => $errorData['absolute_error'],
                    'relative_error' => $errorData['relative_error'],
                    'actual' => $lstmResponse['details'][$field]['actual'] ?? 0,
                    'predicted' => $lstmResponse['details'][$field]['predicted'] ?? 0
                ];
            }
        } elseif (isset($lstmResponse['details'])) {
            // Fallback to calculating from details (backward compatibility)
            foreach ($lstmResponse['details'] as $field => $details) {
                $actual = $details['actual'] ?? 0;
                $predicted = $details['predicted'] ?? 0;
                
                if ($predicted != 0) {
                    $absoluteError = abs($actual - $predicted);
                    $relativeError = $absoluteError / abs($predicted);
                    
                    $errors[$field] = [
                        'absolute_error' => $absoluteError,
                        'relative_error' => $relativeError,
                        'actual' => $actual,
                        'predicted' => $predicted
                    ];
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Analyze error distribution to detect model drift
     */
    protected function analyzeErrorDistribution(Profile $profile, array $errors)
    {
        $validErrors = array_filter($errors, function($error) {
            return $error !== null;
        });
        
        if (empty($validErrors)) {
            return;
        }
        
        // Calculate average relative errors for each feature
        $featureErrors = [];
        $errorCount = 0;
        
        foreach ($validErrors as $errorSet) {
            if (is_array($errorSet)) {
                foreach ($errorSet as $field => $error) {
                    if (!isset($featureErrors[$field])) {
                        $featureErrors[$field] = [];
                    }
                    $featureErrors[$field][] = $error['relative_error'];
                }
                $errorCount++;
            }
        }
        
        // Check for potential model drift (high average errors)
        foreach ($featureErrors as $field => $fieldErrors) {
            $avgError = array_sum($fieldErrors) / count($fieldErrors);
            
            // If average relative error is > 20%, flag as potential drift
            if ($avgError > 0.2) {
                Log::warning("Potential model drift detected", [
                    'profile_id' => $profile->id,
                    'field' => $field,
                    'average_relative_error' => $avgError,
                    'sample_size' => count($fieldErrors)
                ]);
            }
        }
    }
    
    /**
     * Determine if a batch alert should be created
     */
    protected function shouldCreateBatchAlert($isoResult, $lstmResult, Reading $reading)
    {
        // Check if Isolation Forest detected an anomaly
        $isoAnomaly = $isoResult && ($isoResult['result'] === 'anomaly') && ($isoResult['score'] < -0.6);
        
        // Check if LSTM validation shows significant errors (indicating missed anomaly)
        $lstmHighError = false;
        if ($lstmResult && isset($lstmResult['prediction_errors'])) {
            foreach ($lstmResult['prediction_errors'] as $error) {
                if ($error['relative_error'] > 0.3) { // 30% error threshold
                    $lstmHighError = true;
                    break;
                }
            }
        }
        
        // Check if this reading already has a recent alert to avoid duplicates
        $recentAlert = Alert::where('reading_id', $reading->id)
            ->orWhere(function($query) use ($reading) {
                $query->where('socket_id', $reading->socket_id)
                      ->where('created_at', '>=', $reading->created_at->subMinutes(5))
                      ->where('created_at', '<=', $reading->created_at->addMinutes(5));
            })
            ->exists();
        
        return ($isoAnomaly || $lstmHighError) && !$recentAlert;
    }
    
    /**
     * Create a batch-detected alert
     */
    protected function createBatchAlert($isoResult, $lstmResult, Reading $reading, Profile $profile)
    {
        $socket = $reading->socket;
        $iotDevice = $socket ? $socket->iotDevice : null;
        
        // Determine alert type and message based on results
        if ($isoResult && $isoResult['result'] === 'anomaly') {
            $alertType = 'anomaly_batch_isolation_forest';
            $severity = $this->determineSeverityFromScore($isoResult['score']);
            $message = $this->createBatchIsolationForestMessage($isoResult, $reading, $profile);
        } else {
            $alertType = 'anomaly_batch_lstm_validation';
            $severity = 'medium';
            $message = $this->createBatchLstmValidationMessage($lstmResult, $reading, $profile);
        }
        
        $alertData = [
            'type' => $alertType,
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
                'detected_at' => now()->toIso8601String(),
                'detection_method' => 'batch_processing',
                'isolation_forest_result' => $isoResult,
                'lstm_validation_result' => $lstmResult
            ],
            'resolved' => false,
        ];
        
        if ($iotDevice) {
            $alertData['iot_device_id'] = $iotDevice->id;
        }
        
        Alert::create($alertData);
        
        Log::info("Created batch alert", [
            'alert_type' => $alertType,
            'reading_id' => $reading->id,
            'profile_id' => $profile->id
        ]);
    }
    
    /**
     * Create message for Isolation Forest batch alert
     */
    protected function createBatchIsolationForestMessage($isoResult, Reading $reading, Profile $profile)
    {
        $score = round($isoResult['score'], 3);
        $equipmentName = $profile->name ?? 'Equipment';
        $socket = $reading->socket;
        $socketName = $socket ? "Socket #{$socket->assigned_id}" : 'Unknown Socket';
        
        return "Batch Analysis Alert: Global outlier detected for {$equipmentName} on {$socketName}. " .
               "This reading was flagged by historical pattern analysis as significantly different from normal operation (score: {$score}). " .
               "This anomaly may have been missed by real-time detection due to gradual changes or context-dependent patterns. " .
               "Recommendation: Review recent equipment performance trends and consider maintenance inspection.";
    }
    
    /**
     * Create message for LSTM validation batch alert
     */
    protected function createBatchLstmValidationMessage($lstmResult, Reading $reading, Profile $profile)
    {
        $equipmentName = $profile->name ?? 'Equipment';
        $socket = $reading->socket;
        $socketName = $socket ? "Socket #{$socket->assigned_id}" : 'Unknown Socket';
        
        $message = "Model Validation Alert: High prediction errors detected for {$equipmentName} on {$socketName}. ";
        
        if (isset($lstmResult['prediction_errors'])) {
            $maxError = 0;
            $maxErrorField = '';
            
            foreach ($lstmResult['prediction_errors'] as $field => $error) {
                if ($error['relative_error'] > $maxError) {
                    $maxError = $error['relative_error'];
                    $maxErrorField = $field;
                }
            }
            
            $errorPercent = round($maxError * 100, 1);
            $message .= "The {$maxErrorField} prediction showed {$errorPercent}% error, indicating potential model drift or a new operating condition. ";
        }
        
        $message .= "This suggests the equipment may be entering a new operational state that requires model retraining or investigation.";
        
        return $message;
    }
    
    /**
     * Determine severity from anomaly score
     */
    protected function determineSeverityFromScore($score)
    {
        if ($score < -0.8) {
            return 'high';
        } elseif ($score < -0.6) {
            return 'medium';
        } else {
            return 'low';
        }
    }
}