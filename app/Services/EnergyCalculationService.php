<?php

namespace App\Services;

class EnergyCalculationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Calculate the energy consumption for a given profile.
     *
     * @param  \App\Models\Profile  $profile
     * @param  string|\Carbon\Carbon  $startDate  Start date/time string (e.g., 'YYYY-MM-DD HH:MM:SS')
     * @param  string|\Carbon\Carbon  $endDate    End date/time string (e.g., 'YYYY-MM-DD HH:MM:SS')
     * @return float
     */
    public function calculateEnergyConsumption($profile, $startDate, $endDate)
    {
        // Use more efficient query with selected columns only
        $readings = $profile->readings()
            ->select(['id', 'power', 'reading_time'])
            ->whereBetween('reading_time', [$startDate, $endDate])
            ->orderBy('reading_time', 'asc')
            ->get();

        $readingCount = $readings->count();

        if ($readingCount < 2) {
            return 0.0; // Not enough data points to calculate intervals
        }

        $totalKwh = 0.0;
        // Define the maximum allowed gap threshold in seconds (2 minutes)
        $maxGapSecondsThreshold = 2 * 60; // 120 seconds

        // Iterate through readings pairwise
        for ($i = 0; $i < $readingCount - 1; $i++) {
            $r1 = $readings[$i];
            $r2 = $readings[$i + 1];

            // Ensure reading_time is a Carbon instance (usually handled by Eloquent casts)
            if (!($r1->reading_time instanceof \Carbon\Carbon) || !($r2->reading_time instanceof \Carbon\Carbon)) {
                continue;
            }

            // Calculate time difference in seconds
            $deltaTimeSeconds = $r1->reading_time->diffInSeconds($r2->reading_time);

            // If the gap is non-positive or exceeds the threshold, skip this interval
            if ($deltaTimeSeconds <= 0 || $deltaTimeSeconds > $maxGapSecondsThreshold) {
                continue;
            }

            // Ensure power exists and is numeric
            if (!isset($r1->power) || !is_numeric($r1->power) || !isset($r2->power) || !is_numeric($r2->power)) {
                continue;
            }

            // Calculate average power in Watts for the interval
            $avgPowerWatts = ($r1->power + $r2->power) / 2;

            // Calculate energy for the interval in kWh
            // Energy (kWh) = (Power (W) * Time (s)) / (1000 * 3600)
            $intervalKwh = ($avgPowerWatts * $deltaTimeSeconds) / 3600000; // 1000 * 3600

            // Add interval energy to total
            $totalKwh += $intervalKwh;
        }

        return $totalKwh;
    }

    /**
     * Calculate energy consumption for multiple time intervals efficiently
     *
     * @param  \App\Models\Profile  $profile
     * @param  array  $timeIntervals  Array of ['start' => Carbon, 'end' => Carbon] intervals
     * @return array
     */
    public function calculateEnergyConsumptionForIntervals($profile, array $timeIntervals)
    {
        if (empty($timeIntervals)) {
            return [];
        }

        // Get the overall start and end dates
        $overallStart = min(array_column($timeIntervals, 'start'));
        $overallEnd = max(array_column($timeIntervals, 'end'));

        // Fetch all readings in one query
        $allReadings = $profile->readings()
            ->select(['id', 'power', 'reading_time'])
            ->whereBetween('reading_time', [$overallStart, $overallEnd])
            ->orderBy('reading_time', 'asc')
            ->get();

        $results = [];
        
        foreach ($timeIntervals as $interval) {
            $intervalReadings = $allReadings->whereBetween('reading_time', [$interval['start'], $interval['end']]);
            $results[] = $this->calculateEnergyFromReadings($intervalReadings);
        }

        return $results;
    }

    /**
     * Calculate energy from a collection of readings
     *
     * @param  \Illuminate\Support\Collection  $readings
     * @return float
     */
    private function calculateEnergyFromReadings($readings)
    {
        $readingCount = $readings->count();

        if ($readingCount < 2) {
            return 0.0;
        }

        $totalKwh = 0.0;
        $maxGapSecondsThreshold = 2 * 60; // 120 seconds

        $readingsArray = $readings->values()->all();

        for ($i = 0; $i < $readingCount - 1; $i++) {
            $r1 = $readingsArray[$i];
            $r2 = $readingsArray[$i + 1];

            $deltaTimeSeconds = $r1->reading_time->diffInSeconds($r2->reading_time);

            if ($deltaTimeSeconds <= 0 || $deltaTimeSeconds > $maxGapSecondsThreshold) {
                continue;
            }

            if (!is_numeric($r1->power) || !is_numeric($r2->power)) {
                continue;
            }

            $avgPowerWatts = ($r1->power + $r2->power) / 2;
            $intervalKwh = ($avgPowerWatts * $deltaTimeSeconds) / 3600000;
            $totalKwh += $intervalKwh;
        }

        return $totalKwh;
    }
}
