<?php

namespace App\Livewire\Readings;

use Livewire\Component;


class SocketWidget extends Component
{
    public $socket;

    public function mount($socket)
    {
        $this->socket = $socket;
    }


    public function render()
    {
        return view('livewire.readings.socket-widget');
    }
}
