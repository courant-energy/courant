<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class PowerConsumption extends ChartWidget
{
    protected static ?string $heading = 'Energy Consumption (kWh)';
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

    protected function getData(): array
    {
        $filter = $this->filter;
        $profile = $this->socket->profile;

        if ($filter === 'hour') {
            [$labels, $data] = $this->getDataByMinute();
        } elseif ($filter === 'today') {
            // Implement getDataByHour() method to fetch and format data by hour
            [$labels, $data] = $this->getDataByHour();
        } elseif ($filter === 'week') {
            // Implement getDataByDay() method to fetch and format data by day
            [$labels, $data] = $this->getDataByDay(7);
        } elseif ($filter === 'month') {
            // Implement getDataByDay() method to fetch and format data by day
            [$labels, $data] = $this->getDataByDay(30);
        } else { // 'all' or default
            // Implement getDataByMonth() method to fetch and format data by month
            [$labels, $data] = $this->getDataByMonth();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Power Consumption (kWh)',
                    'data' =>  $data,

                ],
            ],
            'labels' => $labels
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }


    protected function getDataByMinute()
    {
        $readings = $this->socket->readings()
            ->forProfile($this->socket->profile_id)
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

        return [$labels, $data];

    }

    protected function getDataByHour()
    {
        $readings = $this->socket->readings()
            ->forProfile($this->socket->profile_id)
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

        return [$labels, $data];
    }

    protected function getDataByDay(int $days)
    {
        
        $readings = $this->socket->readings()
            ->forProfile($this->socket->profile_id)
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

        return [$labels, $data];
    }

    protected function getDataByMonth()
    {
        $readings = $this->socket->readings()
            ->forProfile($this->socket->profile_id)
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

        return [$labels, $data];
    }




    protected function getOptions(): array
    {
        return [
            'aspectRatio' => 1.2,
            'maintainAspectRatio' => true,
            'responsive' => true,

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
                
            ],
        ];
    }
}
