<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Reading;
use Filament\Widgets\ChartWidget;

class Power extends ChartWidget
{
    protected static ?string $heading = 'Average Power (W)';

    protected static ?string $pollingInterval = '10s';

    public ?string $filter = 'hour'; // default filter

    public $socket;

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

    protected function getPowerByMinute()
    {
        $readings = Reading::select(
            \DB::raw('DATE_FORMAT(reading_time, "%H:%i") as minute'),
            \DB::raw('AVG(power) as avg_power')
        )
            ->where('socket_id', $this->socket->id)
            ->where('profile_id', $this->socket->profile_id)
            ->where('reading_time', '>=', now()->subHour())
            ->groupBy('minute')
            ->orderBy('minute')
            ->get();

        $labels = [];
        $data = [];

        foreach ($readings as $reading) {
            $labels[] = $reading->minute;
            $data[] = round($reading->avg_power, 2);
        }

        return [$labels, $data];
    }

    protected function getPowerByHour()
    {
        $readings = Reading::select(
            \DB::raw('DATE_FORMAT(reading_time, "%Y-%m-%d %H:00") as hour'),
            \DB::raw('AVG(power) as avg_power')
        )
            ->where('socket_id', $this->socket->id)
            ->where('profile_id', $this->socket->profile_id)
            ->where('reading_time', '>=', now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $labels = [];
        $data = [];

        foreach ($readings as $reading) {
            $labels[] = $reading->hour;
            $data[] = round($reading->avg_power, 2);
        }

        return [$labels, $data];
    }

    protected function getPowerByDay(int $days)
    {
        $readings = Reading::select(
            \DB::raw('DATE_FORMAT(reading_time, "%Y-%m-%d") as day'),
            \DB::raw('AVG(power) as avg_power')
        )
            ->where('socket_id', $this->socket->id)
            ->where('profile_id', $this->socket->profile_id)
            ->where('reading_time', '>=', now()->subDays($days))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $labels = [];
        $data = [];

        foreach ($readings as $reading) {
            $labels[] = $reading->day;
            $data[] = round($reading->avg_power, 2);
        }

        return [$labels, $data];
    }

    protected function getPowerByMonth()
    {
        $readings = Reading::select(
            \DB::raw('DATE_FORMAT(reading_time, "%Y-%m") as month'),
            \DB::raw('AVG(power) as avg_power')
        )
            ->where('socket_id', $this->socket->id)
            ->where('profile_id', $this->socket->profile_id)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $data = [];

        foreach ($readings as $reading) {
            $labels[] = $reading->month;
            $data[] = round($reading->avg_power, 2);
        }

        return [$labels, $data];
    }

    protected function getData(): array
    {
        $filter = $this->filter;

        if ($filter === 'hour') {
            [$labels, $data] = $this->getPowerByMinute();
        } elseif ($filter === 'today') {
            [$labels, $data] = $this->getPowerByHour();
        } elseif ($filter === 'week') {
            // Implement getDataByDay() method to fetch and format data by day
            [$labels, $data] = $this->getPowerByDay(7);
        } elseif ($filter === 'month') {
            // Implement getDataByDay() method to fetch and format data by day
            [$labels, $data] = $this->getPowerByDay(30);
        } else { // 'all' or default
            // Implement getDataByMonth() method to fetch and format data by month
            [$labels, $data] = $this->getPowerByMonth();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Average Power (W)',
                    'data' => $data,
                    'fill' => true,
                    'lineTension' => 0.4,
                    'borderWidth' => 2,
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
                    'title' => [
                        'display' => true,
                        'text' => 'Power (W)',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Time',
                    ],
                ],
            ],

        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
