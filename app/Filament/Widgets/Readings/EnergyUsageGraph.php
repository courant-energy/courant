<?php

namespace App\Filament\Widgets\Readings;

use Filament\Widgets\ChartWidget;

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


    protected function getData(): array
    {
        $startDate = match ($this->filter) {
            'past_hour' => now()->subHour(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'today' => now()->subDay(),
            default => now()->subDay(),
        };

        $readings = $this->socket->readings()
            ->forProfile($this->socket->profile_id)
            ->where('reading_time', '>=', $startDate)
            ->where('reading_time', '<=', now())
            ->orderBy('reading_time', 'asc')
            ->get();
  
        if ($this->filter === 'past_hour') {
            // Raw data for past hour (usually already granular enough)
            // No grouping needed
        } elseif ($this->filter === 'today') {
            $readings = $readings->groupBy(function ($reading) {
                // Group by 10-minute intervals
                return $reading->reading_time
                    ->setMinute(floor($reading->reading_time->minute / 10) * 10)
                    ->format('Y-m-d H:i');
            })->map(function ($group) {
                return [
                    'energy' => $group->avg('energy'),
                    'power_factor' => $group->avg('power_factor'),
                    'reading_time' => $group->first()->reading_time,
                ];
            });
        } elseif ($this->filter === 'week') {
            $readings = $readings->groupBy(function ($reading) {
                return $reading->reading_time->format('Y-m-d H'); // Group by hour
            })->map(function ($group) {
                return [
                    'energy' => $group->avg('energy'),
                    'power_factor' => $group->avg('power_factor'),
                    'reading_time' => $group->first()->reading_time,
                ];
            });
        } elseif ($this->filter === 'month') {
            $readings = $readings->groupBy(function ($reading) {
                return $reading->reading_time->format('Y-m-d'); // Group by day
            })->map(function ($group) {
                return [
                    'energy' => $group->avg('energy'),
                    'power_factor' => $group->avg('power_factor'),
                    'reading_time' => $group->first()->reading_time,
                ];
            });
        }

        $this->data = $readings->pluck('energy');
        $powerFactorData = $readings->pluck('power_factor');
        $this->labels = $readings->pluck('reading_time')->map(function ($dateTime) {
            return $dateTime->format($this->filter === 'month' ? 'M-d' : 'M-d H:i');
        });


        return [
            'datasets' => [
                [
                    'label' => 'Energy Usage',
                    'data' => $this->data,
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
            'labels' => $this->labels,
        ];
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
