<?php

namespace App\Filament\Widgets\Readings;

use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use App\Services\EnergyCalculationService;
use Illuminate\Support\Facades\Log;

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

    public function __construct() {
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
            // If no profile is associated with the socket, return empty data
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

        // Generate time intervals based on filter
        $timeIntervals = $this->generateTimeIntervals($startDate, $endDate, $this->filter);
        $energyData = [];
        $powerFactorData = [];
        $labels = [];
        $cumulativeEnergy = 0; // Initialize cumulative energy

        // Calculate energy consumption for each interval
        foreach ($timeIntervals as $index => $timePoint) {
            if ($index < count($timeIntervals) - 1) {
            try {
                $intervalStart = Carbon::createFromFormat($this->getTimeFormat(), $timePoint);
                $intervalEnd = Carbon::createFromFormat($this->getTimeFormat(), $timeIntervals[$index + 1]);

                // Use EnergyCalculationService to calculate energy consumption for the interval
                $intervalEnergy = $this->energyCalculationService->calculateEnergyConsumption(
                    $profile,
                    $intervalStart,
                    $intervalEnd
                );

                // Add interval energy to cumulative energy
                $cumulativeEnergy += $intervalEnergy;
                $energyData[] = $cumulativeEnergy; // Store the cumulative energy

                // Get average power factor for the interval
                $avgPowerFactor = $this->getAveragePowerFactorForInterval($profile, $intervalStart, $intervalEnd);

                $powerFactorData[] = $avgPowerFactor;

                // Format label based on the time filter
                $labelFormat = match ($this->filter) {
                    'month' => 'M-d',
                    'week' => 'M-d H:00', // Adjust label format for clarity if needed
                    default => 'M-d H:i',
                };
                $label = $intervalStart->format($labelFormat);
                $labels[] = $label;

            } catch (\Exception $e) {
                // Log the error with context
                Log::error('EnergyUsageGraph: Error processing interval.', [
                'index' => $index,
                'timePoint' => $timePoint,
                'nextTimePoint' => $timeIntervals[$index + 1] ?? 'N/A',
                'format' => $this->getTimeFormat(),
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(), // Consider logging trace only in debug/local env
                ]);
                // Optionally add placeholder data to avoid breaking the chart
                // $energyData[] = 0;
                // $powerFactorData[] = 0;
                // $labels[] = 'Error';
                // Depending on requirements, you might want to re-throw or handle differently
            }
            } else {
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Energy Usage',
                    'data' => $energyData,
                    'backgroundColor' => '#4CAF50',
                    'borderColor' => '#4CAF50',
                    'fill' => false,
                ],
                [
                    'label' => 'Power Factor',
                    'data' => $powerFactorData,
                    'backgroundColor' => '#2196F3',
                    'borderColor' => '#2196F3',
                    'fill' => false,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
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
     * Calculate average power factor for a time interval
     */
    protected function getAveragePowerFactorForInterval($profile, Carbon $startDate, Carbon $endDate): float
    {
        $readings = $profile->readings()
            ->whereBetween('reading_time', [$startDate, $endDate])
            ->get();

        if ($readings->isEmpty()) {
            return 0;
        }

        return $readings->avg('power_factor') ?? 0;
    }

    /**
     * Generate time intervals for the selected time period
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

        $period = CarbonPeriod::create($startDate, $interval, $endDate);
        $timePoints = [];

        foreach ($period as $date) {
            $format = match ($filter) {
                'past_hour' => 'Y-m-d H:i',
                'today' => 'Y-m-d H:i',
                'week' => 'Y-m-d H',
                'month' => 'Y-m-d',
                default => 'Y-m-d H:i',
            };

            if ($filter === 'today') {
                $date = $date->setMinute(floor($date->minute / 10) * 10);
            }

            $timePoints[] = $date->format($format);
        }

        return array_unique($timePoints);
    }

    /**
     * Fill in missing time points with zeros
     */
    protected function fillMissingTimePoints(Collection $readings, array $timeIntervals, string $interval): Collection
    {
        $filledReadings = collect();

        $format = match ($interval) {
            'minute' => 'Y-m-d H:i',
            '10minutes' => 'Y-m-d H:i',
            'hour' => 'Y-m-d H',
            'day' => 'Y-m-d',
            default => 'Y-m-d H:i',
        };

        foreach ($timeIntervals as $timePoint) {
            // Find if we have a reading for this time point
            $found = false;

            foreach ($readings as $reading) {
                $readingTime = is_array($reading)
                    ? $reading['reading_time']->format($format)
                    : $reading->reading_time->format($format);

                if ($readingTime === $timePoint) {
                    $filledReadings->push($reading);
                    $found = true;
                    break;
                }
            }

            // If no reading found for this time point, add a zero placeholder
            if (!$found) {
                $carbonTime = Carbon::createFromFormat($format, $timePoint);

                $filledReadings->push([
                    'energy' => 0,
                    'power_factor' => 0,
                    'reading_time' => $carbonTime,
                ]);
            }
        }

        return $filledReadings;
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
        return 'This graph helps MSMEs monitor energy usage and power factor. Efficient energy usage reduces costs, while maintaining a good power factor improves electrical efficiency and avoids penalties. (Update every 5 seconds)';
    }

    public function getHeading(): ?string
    {
        return 'Energy Usage and Power Factor';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Energy (kWh)'
                    ]
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Power Factor'
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
