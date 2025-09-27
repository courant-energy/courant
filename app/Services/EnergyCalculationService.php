<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

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
     * Uses proper energy calculation: Energy (kWh) = Power (kW) × Time (hours)
     *
     * @param  \App\Models\Profile  $profile
     * @param  string  $startDate  Start date/time string (e.g., 'YYYY-MM-DD HH:MM:SS')
     * @param  string  $endDate    End date/time string (e.g., 'YYYY-MM-DD HH:MM:SS')
     * @return float
     */
    public function calculateEnergyConsumption($profile, $startDate, $endDate)
    {
        // Use the same calculation method as Socket model
        $hourlyData = DB::table('readings')
            ->where('profile_id', $profile->id)
            ->whereBetween('reading_time', [$startDate, $endDate])
            ->select(
                DB::raw('DATE_FORMAT(reading_time, "%Y-%m-%d %H") as hour'),
                DB::raw('AVG(power) as avg_power')
            )
            ->groupBy(DB::raw('DATE_FORMAT(reading_time, "%Y-%m-%d %H")'))
            ->get();

        if ($hourlyData->isEmpty()) {
            return 0.0; // No data points available
        }

        // Calculate energy consumption: (avg_power_watts / 1000) * 1_hour for each hour
        $totalKwh = $hourlyData->sum(function ($hourData) {
            return ($hourData->avg_power / 1000) * 1; // Convert W to kW and multiply by 1 hour
        });

        return round($totalKwh, 2);
    }

    /**
     * Calculate energy consumption by minute intervals (last hour).
     * Uses proper energy calculation: Energy (kWh) = Power (kW) × Time (hours)
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
            // Calculate energy: average power * time (1 minute = 1/60 hour)
            $avgPower = $group->avg('power');
            $data[] = round(($avgPower / 1000) * (1/60), 4); // Convert W to kW, multiply by 1/60 hour
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Calculate energy consumption by hour intervals (today).
     * Uses proper energy calculation: Energy (kWh) = Power (kW) × Time (hours)
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
            // Calculate energy: average power * time (1 hour)
            $avgPower = $group->avg('power');
            $data[] = round(($avgPower / 1000) * 1, 2); // Convert W to kW, multiply by 1 hour
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Calculate energy consumption by day intervals.
     * Uses proper energy calculation: Energy (kWh) = Power (kW) × Time (hours)
     *
     * @param  \App\Models\Profile  $profile
     * @param  int  $days  Number of days to look back
     * @return array ['labels' => [], 'data' => []]
     */
    public function calculateEnergyByDay($profile, int $days = 7)
    {
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();
        
        // Use hourly aggregation for proper energy calculation
        $dailyData = DB::table('readings')
            ->where('profile_id', $profile->id)
            ->whereBetween('reading_time', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(reading_time) as date'),
                DB::raw('DATE_FORMAT(reading_time, "%Y-%m-%d %H") as hour'),
                DB::raw('AVG(power) as avg_power')
            )
            ->groupBy(DB::raw('DATE(reading_time)'), DB::raw('DATE_FORMAT(reading_time, "%Y-%m-%d %H")'))
            ->get()
            ->groupBy('date');

        $labels = [];
        $data = [];

        foreach ($dailyData as $date => $hourlyReadings) {
            $labels[] = $date;
            // Sum energy consumption for all hours in this day
            $dayEnergy = $hourlyReadings->sum(function ($hourData) {
                return ($hourData->avg_power / 1000) * 1; // kW * 1 hour
            });
            $data[] = round($dayEnergy, 2);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Calculate energy consumption by month intervals (all time).
     * Uses proper energy calculation: Energy (kWh) = Power (kW) × Time (hours)
     *
     * @param  \App\Models\Profile  $profile
     * @return array ['labels' => [], 'data' => []]
     */
    public function calculateEnergyByMonth($profile)
    {
        // Use hourly aggregation for proper energy calculation
        $monthlyData = DB::table('readings')
            ->where('profile_id', $profile->id)
            ->select(
                DB::raw('DATE_FORMAT(reading_time, "%Y-%m") as month'),
                DB::raw('DATE_FORMAT(reading_time, "%Y-%m-%d %H") as hour'),
                DB::raw('AVG(power) as avg_power')
            )
            ->groupBy(DB::raw('DATE_FORMAT(reading_time, "%Y-%m")'), DB::raw('DATE_FORMAT(reading_time, "%Y-%m-%d %H")'))
            ->get()
            ->groupBy('month');

        $labels = [];
        $data = [];

        foreach ($monthlyData as $month => $hourlyReadings) {
            $labels[] = $month;
            // Sum energy consumption for all hours in this month
            $monthEnergy = $hourlyReadings->sum(function ($hourData) {
                return ($hourData->avg_power / 1000) * 1; // kW * 1 hour
            });
            $data[] = round($monthEnergy, 2);
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
