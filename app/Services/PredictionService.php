<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Reading;
use App\Models\Socket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PredictionService
{
    /**
     * Analyze readings for a socket to predict potential failures
     *
     * @param  Socket  $socket  The socket to analyze
     * @param  int  $timeWindowHours  How many hours of data to analyze
     * @return array|null Predictions and their confidence levels or null if no predictions
     */
    public function predictFailuresForSocket(Socket $socket, $timeWindowHours = 24)
    {
        $now = Carbon::now();
        $startTime = $now->copy()->subHours($timeWindowHours);

        // Get readings for analysis
        $readings = $socket->readings()
            ->where('reading_time', '>=', $startTime)
            ->orderBy('reading_time')
            ->get();

        if ($readings->count() < 10) {
            Log::info("Not enough readings for socket ID {$socket->id} to make reliable predictions");

            return null;
        }

        $predictions = [];

        // Run various prediction analyses
        $predictions = array_merge(
            $predictions,
            $this->analyzeVoltageTrend($socket, $readings),
            $this->analyzePowerFactorDegradation($socket, $readings),
            $this->analyzeEnergyConsumptionPattern($socket, $readings),
            $this->analyzeCurrentSpikes($socket, $readings)
        );

        if (empty($predictions)) {
            return null;
        }

        // Save predictions for the socket
        $this->recordPredictions($socket, $predictions);

        return $predictions;
    }

    /**
     * Analyze voltage stability over time
     */
    protected function analyzeVoltageTrend(Socket $socket, $readings)
    {
        $predictions = [];

        // Skip if no voltage readings exist
        if (! isset($readings->first()->voltage)) {
            return $predictions;
        }

        // Calculate voltage stability (standard deviation)
        $voltages = $readings->pluck('voltage')->toArray();

        // Safety check for empty array
        if (empty($voltages)) {
            return $predictions;
        }

        $stdDev = $this->standardDeviation($voltages);

        // Check if voltage is getting increasingly unstable
        // by comparing first half and second half of the time period
        $midpoint = floor($readings->count() / 2);
        $firstHalf = $readings->take($midpoint)->pluck('voltage')->toArray();
        $secondHalf = $readings->skip($midpoint)->pluck('voltage')->toArray();

        // Safety check for empty first or second half
        if (empty($firstHalf) || empty($secondHalf)) {
            return $predictions;
        }

        $stdDevFirst = $this->standardDeviation($firstHalf);
        $stdDevSecond = $this->standardDeviation($secondHalf);

        // Avoid division by zero
        if ($stdDevFirst == 0) {
            return $predictions;
        }

        // If second half is significantly more unstable
        if ($stdDevSecond > $stdDevFirst * 1.5 && $stdDev > 5) {
            $lastVoltage = end($voltages);
            $message = 'Increasing voltage instability detected. This may indicate power supply issues or connection problems.';
            $confidence = min(90, 60 + 30 * ($stdDevSecond / $stdDevFirst - 1));

            $predictions[] = [
                'type' => 'voltage_instability',
                'confidence' => $confidence,
                'message' => $message,
                'details' => [
                    'current_volatility' => round($stdDevSecond, 2),
                    'previous_volatility' => round($stdDevFirst, 2),
                    'increase_percentage' => round(($stdDevSecond / $stdDevFirst - 1) * 100).'%',
                    'current_voltage' => round($lastVoltage, 2),
                ],
                'recommended_action' => 'Check power supply connections and quality. Consider voltage stabilization or UPS.',
            ];
        }

        return $predictions;
    }

    /**
     * Analyze power factor degradation over time
     */
    protected function analyzePowerFactorDegradation(Socket $socket, $readings)
    {
        $predictions = [];

        // Skip if no power factor readings
        if (! isset($readings->first()->power_factor)) {
            return $predictions;
        }

        $powerFactors = $readings->pluck('power_factor')->filter()->toArray();

        // Not enough power factor readings
        if (count($powerFactors) < 5) {
            return $predictions;
        }

        // Safety check for empty array after filtering
        if (empty($powerFactors)) {
            return $predictions;
        }

        // Linear regression to find trend
        $x = range(1, count($powerFactors));
        [$slope, $intercept] = $this->linearRegression($x, $powerFactors);

        // Get the last power factor safely
        $lastPowerFactor = end($powerFactors);

        // Negative slope indicates degrading power factor
        if ($slope < -0.005 && $lastPowerFactor < 0.9) {
            $message = 'Power factor is gradually decreasing. This may indicate component degradation or wiring issues.';
            $confidence = min(85, 50 + abs($slope * 10000));

            $predictions[] = [
                'type' => 'power_factor_degradation',
                'confidence' => $confidence,
                'message' => $message,
                'details' => [
                    'current_power_factor' => round($lastPowerFactor, 2),
                    'trend' => round($slope * 1000, 3).' per thousand readings',
                    'projected_future_value' => round($lastPowerFactor + $slope * 100, 2),
                ],
                'recommended_action' => 'Schedule inspection of motor/device components. Check for loose connections or inspect for excess inductance.',
            ];
        }

        return $predictions;
    }

    /**
     * Analyze energy consumption patterns for anomalies
     */
    protected function analyzeEnergyConsumptionPattern(Socket $socket, $readings)
    {
        $predictions = [];

        // Skip if no power readings
        if (! isset($readings->first()->power)) {
            return $predictions;
        }

        $powers = $readings->pluck('power')->filter()->toArray();

        // Not enough power readings
        if (count($powers) < 5) {
            return $predictions;
        }

        // Safety check for empty array after filtering
        if (empty($powers)) {
            return $predictions;
        }

        $mean = array_sum($powers) / count($powers);

        // Avoid division by zero
        if ($mean <= 0) {
            return $predictions;
        }

        $stdDev = $this->standardDeviation($powers);

        // Check for gradually increasing power consumption (could indicate friction/resistance)
        $x = range(1, count($powers));
        [$slope, $intercept] = $this->linearRegression($x, $powers);

        // Get last power value safely
        $lastPower = end($powers);

        if ($slope > 0.01 * $mean) {
            // Power is increasing at more than 1% of mean per reading
            $message = 'Power consumption is gradually increasing. This may indicate increasing friction, resistance, or component wear.';
            $confidence = min(80, 40 + ($slope * 100 / $mean) * 400);

            $predictions[] = [
                'type' => 'increasing_power_consumption',
                'confidence' => $confidence,
                'message' => $message,
                'details' => [
                    'current_power' => round($lastPower, 2).'W',
                    'mean_power' => round($mean, 2).'W',
                    'trend' => '+'.round($slope * 100 / $mean, 2).'% per reading',
                ],
                'recommended_action' => 'Check for mechanical wear, dust buildup, or blockages. Consider maintenance soon.',
            ];
        }

        return $predictions;
    }

    /**
     * Analyze current patterns for indications of short circuits
     */
    protected function analyzeCurrentSpikes(Socket $socket, $readings)
    {
        $predictions = [];

        // Skip if no current readings
        if (! isset($readings->first()->current)) {
            return $predictions;
        }

        $currents = $readings->pluck('current')->filter()->toArray();

        // Not enough current readings
        if (count($currents) < 5) {
            return $predictions;
        }

        // Safety check for empty array after filtering
        if (empty($currents)) {
            return $predictions;
        }

        $mean = array_sum($currents) / count($currents);

        // Avoid division by zero
        if ($mean <= 0) {
            return $predictions;
        }

        $max = max($currents);

        // Check for intermittent current spikes
        if ($max > $mean * 1.5) {
            // Count spikes
            $spikeCount = 0;
            foreach ($currents as $current) {
                if ($current > $mean * 1.3) {
                    $spikeCount++;
                }
            }

            // If more than 5% of readings show spikes
            if ($spikeCount > count($currents) * 0.05) {
                $message = 'Intermittent current spikes detected. This may indicate partial short circuits or connection issues.';
                $confidence = min(90, 50 + $spikeCount * 2);

                $predictions[] = [
                    'type' => 'current_spikes',
                    'confidence' => $confidence,
                    'message' => $message,
                    'details' => [
                        'spike_percentage' => round($spikeCount / count($currents) * 100).'%',
                        'max_spike' => round($max, 2).'A',
                        'average_current' => round($mean, 2).'A',
                    ],
                    'recommended_action' => 'Inspect wiring and connections. Check for insulation damage or water exposure.',
                ];
            }
        }

        return $predictions;
    }

    /**
     * Record predictions as alerts
     */
    protected function recordPredictions(Socket $socket, array $predictions)
    {
        $profile = $socket->profile;
        $iotDevice = $socket->iotDevice;

        foreach ($predictions as $prediction) {
            // Determine severity based on confidence
            $severity = $this->getSeverityFromConfidence($prediction['confidence']);

            // Create an alert for this prediction
            Alert::create([
                'iot_device_id' => $iotDevice->id,
                'socket_id' => $socket->id,
                'profile_id' => $profile->id,
                'type' => 'prediction_'.$prediction['type'],
                'message' => $prediction['message'],
                'data' => json_encode([
                    'prediction_type' => $prediction['type'],
                    'confidence' => $prediction['confidence'],
                    'details' => $prediction['details'],
                    'recommended_action' => $prediction['recommended_action'],
                    'prediction_time' => Carbon::now()->toDateTimeString(),
                ]),
                'severity' => $severity,
                'resolved' => false,
            ]);

            // Log the prediction
            Log::channel('predictions')->info('Failure prediction generated', [
                'device_id' => $iotDevice->id,
                'socket_id' => $socket->id,
                'type' => $prediction['type'],
                'confidence' => $prediction['confidence'],
                'severity' => $severity,
            ]);
        }
    }

    /**
     * Map prediction confidence to severity
     */
    protected function getSeverityFromConfidence($confidence)
    {
        if ($confidence >= 80) {
            return 'critical';
        } elseif ($confidence >= 60) {
            return 'warning';
        } else {
            return 'info';
        }
    }

    /**
     * Calculate standard deviation
     */
    protected function standardDeviation(array $values)
    {
        $count = count($values);
        if ($count === 0) {
            return 0;
        }

        $mean = array_sum($values) / $count;
        $squares = array_map(function ($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values);

        return sqrt(array_sum($squares) / $count);
    }

    /**
     * Calculate linear regression (slope and intercept)
     */
    protected function linearRegression(array $x, array $y)
    {
        Log::debug('linearRegression: Starting calculation', [
            'x_count' => count($x),
            'y_count' => count($y),
            'x_array' => $x,
            'y_array' => $y,
        ]);

        // Return default values if arrays are empty
        if (empty($x) || empty($y)) {
            Log::warning('linearRegression: Empty arrays detected', [
                'x_empty' => empty($x),
                'y_empty' => empty($y),
            ]);

            return [0, 0];
        }

        // Convert $y to sequential array if it's associative
        if (array_keys($y) !== range(0, count($y) - 1)) {
            Log::info('linearRegression: Converting associative y array to sequential');
            $y = array_values($y);
        }

        // Generate new x array if needed, based on y values
        if (count($x) != count($y)) {
            Log::info('linearRegression: Arrays have different lengths', [
                'x_length' => count($x),
                'y_length' => count($y),
            ]);

            // Use the smaller length
            $n = min(count($x), count($y));
            $x = array_slice($x, 0, $n);
            $y = array_slice($y, 0, $n);

            Log::info('linearRegression: Adjusted arrays', [
                'new_x_length' => count($x),
                'new_y_length' => count($y),
            ]);
        }

        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);

        Log::debug('linearRegression: Initial sums calculated', [
            'sum_x' => $sumX,
            'sum_y' => $sumY,
        ]);

        $sumXX = 0;
        $sumXY = 0;

        try {
            for ($i = 0; $i < $n; $i++) {
                // Ensure both values exist
                if (! isset($x[$i]) || ! isset($y[$i])) {
                    Log::error('linearRegression: Index access error', [
                        'index' => $i,
                        'x_isset' => isset($x[$i]),
                        'y_isset' => isset($y[$i]),
                        'x_count' => count($x),
                        'y_count' => count($y),
                    ]);

                    continue;
                }

                $sumXX += $x[$i] * $x[$i];
                $sumXY += $x[$i] * $y[$i];
            }

            Log::debug('linearRegression: Loop completed', [
                'sum_xx' => $sumXX,
                'sum_xy' => $sumXY,
            ]);

            // Avoid division by zero
            $denominator = ($n * $sumXX - $sumX * $sumX);

            if ($denominator == 0) {
                Log::info('linearRegression: Division by zero prevented', [
                    'denominator' => $denominator,
                    'n' => $n,
                    'sum_xx' => $sumXX,
                    'sum_x' => $sumX,
                ]);

                return [0, $n > 0 ? $sumY / $n : 0];
            }

            $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;
            $intercept = ($sumY - $slope * $sumX) / $n;

            Log::debug('linearRegression: Calculation successful', [
                'slope' => $slope,
                'intercept' => $intercept,
            ]);

            return [$slope, $intercept];
        } catch (\Exception $e) {
            Log::error('linearRegression: Exception caught', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'x_array' => $x,
                'y_array' => $y,
            ]);

            return [0, 0];
        }
    }

    /**
     * Run the prediction service for all active sockets
     *
     * @param  int  $timeWindowHours  How many hours of data to analyze
     * @return int Number of predictions generated
     */
    public function runPredictionForAllActiveSockets($timeWindowHours = 24)
    {
        $sockets = Socket::with(['iotDevice', 'profile'])
            ->whereHas('iotDevice', function ($query) {
                $query->where('is_active', true);
            })
            ->whereHas('profile')
            ->get();

        $predictionsCount = 0;

        foreach ($sockets as $socket) {
            $predictions = $this->predictFailuresForSocket($socket, $timeWindowHours);
            if ($predictions) {
                $predictionsCount += count($predictions);
            }
        }

        Log::info("Prediction service completed: {$predictionsCount} potential issues found in {$sockets->count()} sockets");

        return $predictionsCount;
    }
}
