<?php

namespace App\Filament\Widgets;

use App\Models\Reading;
use Filament\Widgets\ChartWidget;

class PowerFactor extends ChartWidget
{
    protected static ?string $heading = 'Power Factor & Current (A)';
    protected static ?string $pollingInterval = '10s';

    public $socket;

    public ?string $filter = 'hour'; // default filter

    protected function getFilters(): ?array
    {
        return [
            'hour' => 'Last hour',
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'all' => 'All time',
        ];
    }

    protected function getDataByMinute()
    {
        $readings = Reading::select(
            \DB::raw('DATE_FORMAT(reading_time, "%H:%i") as minute'),
            \DB::raw('AVG(power_factor * 100) as avg_power_factor'),
            \DB::raw('AVG(current) as avg_current'),
            \DB::raw('MAX(current) as max_current')
        )
            ->where('socket_id', $this->socket->id)
            ->where('profile_id', $this->socket->profile_id)
            ->where('reading_time', '>=', now()->subHour())
            ->groupBy('minute')
            ->orderBy('minute')
            ->get();

        $labels = [];
        $powerFactorData = [];
        $currentAvgData = [];
        $currentMaxData = [];

        foreach ($readings as $reading) {
            $labels[] = $reading->minute;
            $powerFactorData[] = round($reading->avg_power_factor, 2);
            $currentAvgData[] = round($reading->avg_current, 2);
            $currentMaxData[] = round($reading->max_current, 2);
        }

        return [$labels, $powerFactorData, $currentAvgData, $currentMaxData];
    }

    protected function getDataByHour(): array
    {
        $readings = Reading::select(
            \DB::raw('DATE_FORMAT(reading_time, "%Y-%m-%d %H:00") as hour'),
            \DB::raw('AVG(power_factor * 100) as avg_power_factor'),
            \DB::raw('AVG(current) as avg_current'),
            \DB::raw('MAX(current) as max_current')
        )
            ->where('socket_id', $this->socket->id)
            ->where('profile_id', $this->socket->profile_id)
            ->where('reading_time', '>=', now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $labels = [];
        $powerFactorData = [];
        $currentAvgData = [];
        $currentMaxData = [];

        foreach ($readings as $reading) {
            $labels[] = $reading->hour;
            $powerFactorData[] = round($reading->avg_power_factor, 2);
            $currentAvgData[] = round($reading->avg_current, 2);
            $currentMaxData[] = round($reading->max_current, 2);
        }

        return [$labels, $powerFactorData, $currentAvgData, $currentMaxData];
    }

    protected function getDataByDay(int $days): array
    {
        $readings = Reading::select(
            \DB::raw('DATE_FORMAT(reading_time, "%Y-%m-%d") as day'),
            \DB::raw('AVG(power_factor * 100) as avg_power_factor'),
            \DB::raw('AVG(current) as avg_current'),
            \DB::raw('MAX(current) as max_current')
        )
            ->where('socket_id', $this->socket->id)
            ->where('profile_id', $this->socket->profile_id)
            ->where('reading_time', '>=', now()->subDays($days))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $labels = [];
        $powerFactorData = [];
        $currentAvgData = [];
        $currentMaxData = [];

        foreach ($readings as $reading) {
            $labels[] = $reading->day;
            $powerFactorData[] = round($reading->avg_power_factor, 2);
            $currentAvgData[] = round($reading->avg_current, 2);
            $currentMaxData[] = round($reading->max_current, 2);
        }

        return [$labels, $powerFactorData, $currentAvgData, $currentMaxData];
    }

    protected function getDataByMonth(): array
    {
        $readings = Reading::select(
            \DB::raw('DATE_FORMAT(reading_time, "%Y-%m") as month'),
            \DB::raw('AVG(power_factor * 100) as avg_power_factor'),
            \DB::raw('AVG(current) as avg_current'),
            \DB::raw('MAX(current) as max_current')
        )
            ->where('socket_id', $this->socket->id)
            ->where('profile_id', $this->socket->profile_id)
            ->where('reading_time', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $powerFactorData = [];
        $currentAvgData = [];
        $currentMaxData = [];

        foreach ($readings as $reading) {
            $labels[] = $reading->month;
            $powerFactorData[] = round($reading->avg_power_factor, 2);
            $currentAvgData[] = round($reading->avg_current, 2);
            $currentMaxData[] = round($reading->max_current, 2);
        }

        return [$labels, $powerFactorData, $currentAvgData, $currentMaxData];
    }

    protected function getData(): array
    {

        $currentFilter = $this->filter;

        switch ($currentFilter) {
            case 'hour':
                [$labels, $powerFactorData, $currentAvgData, $currentMaxData] = $this->getDataByMinute();
                break;
            case 'today':
                [$labels, $powerFactorData, $currentAvgData, $currentMaxData] = $this->getDataByHour();
                break;
            case 'week':
                [$labels, $powerFactorData, $currentAvgData, $currentMaxData] = $this->getDataByDay(7);
                break;
            case 'month':
                [$labels, $powerFactorData, $currentAvgData, $currentMaxData] = $this->getDataByDay(30);
                break;
            case 'all':
                [$labels, $powerFactorData, $currentAvgData, $currentMaxData] = $this->getDataByMonth();
                break;
            default:
                [$labels, $powerFactorData, $currentAvgData, $currentMaxData] = $this->getDataByMinute();
                break;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Power Factor (Line)',
                    'data' => $powerFactorData,
                    'type' => 'line',
                    'backgroundColor' => 'rgba(76, 175, 80, 0.1)',
                    'borderColor' => '#4CAF50',
                    'borderWidth' => 2,
                    'fill' => false,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Current Avg (Bar)',
                    'data' => $currentAvgData,
                    'type' => 'bar',
                    'backgroundColor' => 'rgba(33, 150, 243, 0.7)',
                    'borderColor' => '#2196F3',
                    'borderWidth' => 1,
                    'yAxisID' => 'A',
                ],
                [
                    'label' => 'Current Max (Bar)',
                    'data' => $currentMaxData,
                    'type' => 'bar',
                    'backgroundColor' => 'rgba(255, 152, 0, 0.7)',
                    'borderColor' => '#FF9800',
                    'borderWidth' => 1,
                    'yAxisID' => 'A',
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'aspectRatio' => 1.2,
            'maintainAspectRatio' => true,
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Power Factor (%)',
                    ],
                ],
                'A' => [
                    'position' => 'right',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Current (A)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false, // only want the grid lines for one axis to show up
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],

        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Mixed chart with both bar and line
    }


}
