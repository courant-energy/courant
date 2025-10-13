<?php

namespace App\Livewire\Readings;

use Livewire\Component;

class EnergyUsageGraph extends Component
{
    public $socket;

    public $data;

    public $labels;

    public function mount($socket)
    {
        $this->socket = $socket;

        $readings = $socket->readings()
            ->where('reading_time', '>=', now()->subDay(2))
            ->where('reading_time', '<=', now())
            ->orderBy('reading_time', 'asc')
            ->get();

        $this->data = $readings->pluck('energy');

        $this->labels = $readings->pluck('reading_time')->map(function ($dateTime) {
            return $dateTime->format('H:i');
        });

    }

    public function render()
    {
        return view('livewire.readings.energy-usage-graph');
    }
}
