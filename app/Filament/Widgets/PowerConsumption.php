<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Services\EnergyCalculationService;

class PowerConsumption extends ChartWidget
{
    protected static ?string $heading = 'Energy Consumption (kWh)';
    protected static ?string $pollingInterval = '10s';
    public $socket;
    public ?string $filter = 'hour'; // default filter
    protected EnergyCalculationService $energyService;

    public function __construct()
    {
        $this->energyService = new EnergyCalculationService();
    }

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
        $result = $this->energyService->calculateEnergyByMinute($this->socket->profile);
        return [$result['labels'], $result['data']];
    }

    protected function getDataByHour()
    {
        $result = $this->energyService->calculateEnergyByHour($this->socket->profile);
        return [$result['labels'], $result['data']];
    }

    protected function getDataByDay(int $days)
    {
        $result = $this->energyService->calculateEnergyByDay($this->socket->profile, $days);
        return [$result['labels'], $result['data']];
    }

    protected function getDataByMonth()
    {
        $result = $this->energyService->calculateEnergyByMonth($this->socket->profile);
        return [$result['labels'], $result['data']];
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
