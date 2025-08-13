<?php

namespace App\Filament\Widgets\Readings;

use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use App\Services\EnergyCalculationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EnergyUsageGraph extends ChartWidget
{
    protected static ?string $heading = 'Chart';
    public $socket;
    protected $data;
    protected $labels;
    public ?string $filter = 'past_hour';
    protected static ?string $pollingInterval = '5s';
    // colspanfull
    protected int|string|array $columnSpan = 2;
    protected EnergyCalculationService $energyCalculationService;

    public function __construct()
    {
        $this->energyCalculationService = app(EnergyCalculationService::class);
    }

    protected function getData(): array
    {
        $startDate = match ($this->filter) {
            'past_hour' => now()->subHour(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'today' => now()->subDay(),
            default => now()->subDay(),
        };
        $endDate = now();

        // Get the profile from the socket
        $profile = $this->socket->profile;

        if (!$profile) {
            return $this->getEmptyDataset();
        }

        // Use caching for expensive calculations
        $cacheKey = "energy_graph_{$profile->id}_{$this->filter}_{$startDate->timestamp}_{$endDate->timestamp}";
        $cacheTime = match ($this->filter) {
            'past_hour' => 30, // 30 seconds for real-time data
            'today' => 300,    // 5 minutes
            'week' => 900,     // 15 minutes
            'month' => 1800,   // 30 minutes
            default => 300,
        };

        return Cache::remember($cacheKey, $cacheTime, function () use ($profile, $startDate, $endDate) {
            return $this->generateChartData($profile, $startDate, $endDate);
        });
    }

    /**
     * Clear cache for this profile when new data arrives
     */
    public static function clearCacheForProfile($profileId): void
    {
        $patterns = [
            "energy_graph_{$profileId}_past_hour_*",
            "energy_graph_{$profileId}_today_*",
            "energy_graph_{$profileId}_week_*",
            "energy_graph_{$profileId}_month_*",
        ];

        foreach ($patterns as $pattern) {
            Cache::flush(); // For simplicity, you can implement more targeted cache clearing
        }
    }

    protected function getEmptyDataset(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Energy Usage',
                    'data' => [],
                    'backgroundColor' => '#4CAF50',
                    'borderColor' => '#4CAF50',
                    'fill' => false,
                ],
                [
                    'label' => 'Power Factor',
                    'data' => [],
                    'backgroundColor' => '#2196F3',
                    'borderColor' => '#2196F3',
                    'fill' => false,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => [],
        ];
    }

    protected function generateChartData($profile, Carbon $startDate, Carbon $endDate): array
    {
        // Generate time intervals based on filter
        $timeIntervals = $this->generateTimeIntervals($startDate, $endDate, $this->filter);
        
        if (empty($timeIntervals)) {
            return $this->getEmptyDataset();
        }

        // Create interval objects for batch energy calculation
        $intervalObjects = [];
        for ($i = 0; $i < count($timeIntervals) - 1; $i++) {
            try {
                $intervalStart = Carbon::createFromFormat($this->getTimeFormat(), $timeIntervals[$i]);
                $intervalEnd = Carbon::createFromFormat($this->getTimeFormat(), $timeIntervals[$i + 1]);
                $intervalObjects[] = ['start' => $intervalStart, 'end' => $intervalEnd];
            } catch (\Exception $e) {
                Log::error('EnergyUsageGraph: Error parsing time interval', [
                    'start' => $timeIntervals[$i],
                    'end' => $timeIntervals[$i + 1] ?? 'N/A',
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        // Batch calculate energy consumption for all intervals
        $energyResults = $this->energyCalculationService->calculateEnergyConsumptionForIntervals(
            $profile,
            $intervalObjects
        );

        // Get power factor data efficiently
        $powerFactorData = $this->getBatchPowerFactorData($profile, $intervalObjects);

        // Process results
        $energyData = [];
        $powerFactorDataPoints = [];
        $labels = [];
        $cumulativeEnergy = 0;

        foreach ($intervalObjects as $index => $interval) {
            $intervalEnergy = $energyResults[$index] ?? 0;
            $cumulativeEnergy += $intervalEnergy;
            $energyData[] = $cumulativeEnergy;

            $avgPowerFactor = $powerFactorData[$index] ?? 0;
            $powerFactorDataPoints[] = $avgPowerFactor * 100; // Convert to percentage

            // Format label based on the time filter
            $labelFormat = match ($this->filter) {
                'month' => 'M-d',
                'week' => 'M-d H:00',
                default => 'M-d H:i',
            };
            $labels[] = $interval['start']->format($labelFormat);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Energy Usage',
                    'data' => $energyData,
                    'backgroundColor' => '#4CAF50',
                    'borderColor' => '#4CAF50',
                    'fill' => false,
                    'pointRadius' => 0,
                    'lineTension' => 0.4,
                    'borderWidth' => 4,
                    'pointHoverRadius' => 5,
                ],
                [
                    'label' => 'Power Factor',
                    'data' => $powerFactorDataPoints,
                    'backgroundColor' => '#2196F3',
                    'borderColor' => '#2196F3',
                    'fill' => false,
                    'yAxisID' => 'y1',
                    'pointRadius' => 0,
                    'lineTension' => 0.4,
                    'borderWidth' => 4,
                    'pointHoverRadius' => 5,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Get power factor data for multiple intervals efficiently
     */
    protected function getBatchPowerFactorData($profile, array $intervals): array
    {
        if (empty($intervals)) {
            return [];
        }

        // Get the overall start and end dates
        $overallStart = min(array_column($intervals, 'start'));
        $overallEnd = max(array_column($intervals, 'end'));

        // Fetch all power factor readings in one query
        $allReadings = $profile->readings()
            ->select(['power_factor', 'reading_time'])
            ->whereBetween('reading_time', [$overallStart, $overallEnd])
            ->whereNotNull('power_factor')
            ->get();

        $results = [];
        foreach ($intervals as $interval) {
            $intervalReadings = $allReadings->whereBetween('reading_time', [$interval['start'], $interval['end']]);
            $results[] = $intervalReadings->isEmpty() ? 0 : $intervalReadings->avg('power_factor');
        }

        return $results;
    }

    /**
     * Get the appropriate time format based on the current filter
     */
    protected function getTimeFormat(): string
    {
        return match ($this->filter) {
            'past_hour' => 'Y-m-d H:i',
            'today' => 'Y-m-d H:i',
            'week' => 'Y-m-d H',
            'month' => 'Y-m-d',
            default => 'Y-m-d H:i',
        };
    }

    /**
     * Calculate average power factor for a time interval (legacy method, kept for compatibility)
     */
    protected function getAveragePowerFactorForInterval($profile, Carbon $startDate, Carbon $endDate): float
    {
        $readings = $profile->readings()
            ->select(['power_factor'])
            ->whereBetween('reading_time', [$startDate, $endDate])
            ->whereNotNull('power_factor')
            ->get();

        if ($readings->isEmpty()) {
            return 0;
        }

        return $readings->avg('power_factor') ?? 0;
    }

    /**
     * Generate time intervals for the selected time period (optimized)
     */
    protected function generateTimeIntervals(Carbon $startDate, Carbon $endDate, string $filter): array
    {
        $interval = match ($filter) {
            'past_hour' => '1 minute',
            'today' => '10 minutes',
            'week' => '1 hour',
            'month' => '1 day',
            default => '1 hour',
        };

        // Use a more efficient approach for generating intervals
        $timePoints = [];
        $format = $this->getTimeFormat();
        
        $period = CarbonPeriod::create($startDate, $interval, $endDate);
        
        foreach ($period as $date) {
            if ($filter === 'today') {
                $date = $date->setMinute(floor($date->minute / 10) * 10);
            }
            $timePoints[] = $date->format($format);
        }

        return array_unique($timePoints);
    }

    protected function getFilters(): ?array
    {
        return [
            'past_hour' => 'Past Hour',
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getDescription(): ?string
    {
        return 'This graph helps MSMEs monitor energy usage and power factor. Efficient energy usage reduces costs, while maintaining a good power factor improves electrical efficiency and avoids penalties. (Updates every 5 seconds)';
    }

    public function getHeading(): ?string
    {
        return 'Energy Usage and Power Factor';
    }

    protected function getOptions(): array
    {
        return [
            'aspectRatio' => 1.2,
            'maintainAspectRatio' => true,
            'responsive' => true,
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => false,
                    'title' => [
                        'display' => true,
                        'text' => 'Energy (kWh)'
                    ]
                ],
                'x' => [
                    'ticks' => [
                        'maxTicksLimit' => 10,
                    ],
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Power Factor (%)'
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                    'max' => 100,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'callbacks' => [
                        'label' => 'function(context) {
                            if (context.datasetIndex === 0) {
                                return "Energy: " + context.parsed.y.toFixed(3) + " kWh";
                            } else {
                                return "Power Factor: " + context.parsed.y.toFixed(1) + "%";
                            }
                        }'
                    ]
                ]
            ],
        ];
    }
}
