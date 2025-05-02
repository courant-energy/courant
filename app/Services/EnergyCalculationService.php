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
     * @param  string  $startDate  Start date/time string (e.g., 'YYYY-MM-DD HH:MM:SS')
     * @param  string  $endDate    End date/time string (e.g., 'YYYY-MM-DD HH:MM:SS')
     * @return float
     */
    public function calculateEnergyConsumption($profile, $startDate, $endDate)
    {
        $readings = $profile->readings()
            ->whereBetween('reading_time', [$startDate, $endDate])
            ->where('profile_id', $profile->id)
            ->orderBy('reading_time', 'asc')
            ->get();

        $readingCount = $readings->count();

        // Calculate total energy consumption
        $totalEnergy = 0;
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

        // Overwrite the initial $totalEnergy with the calculated $totalKwh
        $totalEnergy = $totalKwh;

        return $totalEnergy;
    }
}
