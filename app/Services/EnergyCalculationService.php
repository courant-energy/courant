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
        // Get all readings for the profile within the specified date range, ordered by time
        $readings = $profile->readings()
            ->whereBetween('reading_time', [$startDate, $endDate])
            ->where('profile_id', $profile->id)
            ->orderBy('reading_time', 'asc')
            ->get();

        // Calculate total energy consumption
        $totalEnergy = 0;
        if ($readings->count() < 2) {
            return 0.0; // Not enough data points to calculate intervals
        }

        $totalKwh = 0.0;
        // Define the maximum allowed gap threshold in seconds (2 minutes)
        $maxGapSecondsThreshold = 2 * 60; // 120 seconds

        // Iterate through readings pairwise
        for ($i = 0; $i < $readings->count() - 1; $i++) {
            $r1 = $readings[$i];
            $r2 = $readings[$i + 1];

            // Ensure reading_time is a Carbon instance (usually handled by Eloquent casts)
            if (!($r1->reading_time instanceof \Carbon\Carbon) || !($r2->reading_time instanceof \Carbon\Carbon)) {
                // Log error or handle appropriately if timestamps are not Carbon objects
                // Consider logging: \Log::warning("Invalid reading_time format...");
                continue;
            }

            // Calculate time difference in seconds
            $deltaTimeSeconds = $r2->reading_time->diffInSeconds($r1->reading_time);

            // If the gap is non-positive or exceeds the threshold, skip this interval
            if ($deltaTimeSeconds <= 0 || $deltaTimeSeconds > $maxGapSecondsThreshold) {
                 // Optionally log large gaps: \Log::info("Skipping interval due to gap: {$deltaTimeSeconds}s");
                continue;
            }

            // Ensure power_watts exists and is numeric
            if (!isset($r1->power_watts) || !is_numeric($r1->power_watts) || !isset($r2->power_watts) || !is_numeric($r2->power_watts)) {
                // Log error or handle appropriately if power data is missing/invalid
                // Consider logging: \Log::warning("Invalid power_watts data...");
                continue;
            }

            // Calculate average power in Watts for the interval
            $avgPowerWatts = ($r1->power_watts + $r2->power_watts) / 2;

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
