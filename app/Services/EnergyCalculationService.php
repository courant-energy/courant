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
     * Uses the same calculation method as PowerConsumption widget.
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

        if ($readings->isEmpty()) {
            return 0.0; // No data points available
        }

        // Calculate total energy consumption using the same method as PowerConsumption widget
        // Sum all power values and convert from watts to kWh
        $totalPowerWatts = $readings->sum('power');
        $totalKwh = round($totalPowerWatts / 1000, 2);

        return $totalKwh;
    }

    /**
     * Calculate energy consumption by minute intervals (last hour).
     * Matches PowerConsumption widget's getDataByMinute method.
     *
     * @param  \App\Models\Profile  $profile
     * @return array ['labels' => [], 'data' => []]
     */
    public function calculateEnergyByMinute($profile)
    {
        $readings = $profile->readings()
            ->where('reading_time', '>=', now()->subHour())
            ->orderBy('reading_time')
            ->get()
            ->groupBy(function ($reading) {
                return $reading->reading_time->format('H:i');
            });

        $labels = [];
        $data = [];

        foreach ($readings as $time => $group) {
            $labels[] = $time;
            $data[] = round($group->sum('power') / 1000, 2); // Convert to kWh
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Calculate energy consumption by hour intervals (today).
     * Matches PowerConsumption widget's getDataByHour method.
     *
     * @param  \App\Models\Profile  $profile
     * @return array ['labels' => [], 'data' => []]
     */
    public function calculateEnergyByHour($profile)
    {
        $readings = $profile->readings()
            ->where('reading_time', '>=', now()->subDay())
            ->orderBy('reading_time')
            ->get()
            ->groupBy(function ($reading) {
                return $reading->reading_time->format('H:00');
            });

        $labels = [];
        $data = [];

        foreach ($readings as $time => $group) {
            $labels[] = $time;
            $data[] = round($group->sum('power') / 1000, 2); // Convert to kWh
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Calculate energy consumption by day intervals.
     * Matches PowerConsumption widget's getDataByDay method.
     *
     * @param  \App\Models\Profile  $profile
     * @param  int  $days  Number of days to look back
     * @return array ['labels' => [], 'data' => []]
     */
    public function calculateEnergyByDay($profile, int $days = 7)
    {
        $readings = $profile->readings()
            ->where('reading_time', '>=', now()->subDays($days))
            ->orderBy('reading_time')
            ->get()
            ->groupBy(function ($reading) {
                return $reading->reading_time->format('Y-m-d');
            });

        $labels = [];
        $data = [];

        foreach ($readings as $date => $group) {
            $labels[] = $date;
            $data[] = round($group->sum('power') / 1000, 2); // Convert to kWh
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Calculate energy consumption by month intervals (all time).
     * Matches PowerConsumption widget's getDataByMonth method.
     *
     * @param  \App\Models\Profile  $profile
     * @return array ['labels' => [], 'data' => []]
     */
    public function calculateEnergyByMonth($profile)
    {
        $readings = $profile->readings()
            ->orderBy('reading_time')
            ->get()
            ->groupBy(function ($reading) {
                return $reading->reading_time->format('Y-m');
            });

        $labels = [];
        $data = [];

        foreach ($readings as $month => $group) {
            $labels[] = $month;
            $data[] = round($group->sum('power') / 1000, 2); // Convert to kWh
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
