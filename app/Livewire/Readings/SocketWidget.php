<?php

namespace App\Livewire\Readings;

use Livewire\Component;


class SocketWidget extends Component
{
    public $socket;

    public function mount($socket = null)
    {
        $this->socket = $socket;
    }

    public function render()
    {
        // Ensure socket exists before rendering
        if (!$this->socket) {
            return view('livewire.readings.socket-widget-empty');
        }

        return view('livewire.readings.socket-widget');
    }
}
