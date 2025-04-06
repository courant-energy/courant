<x-filament-panels::page>
   


    <x-filament::section aside>
        <x-slot name="heading">
            Filter
        </x-slot>
        <x-slot name="description">
            Select the IoT device you want to monitor.
        </x-slot>
        {{-- Content --}}
        <div class="space-y-4">
            <div class="mt-4">
                {{ $this->form }}
            </div>
            <div class="flex items-center justify-end mt-4">
                <x-filament::button wire:click="loadData" color="primary">
                    Submit
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>




    @if ($hasSelectedDevice)
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $device->name }}
        </h1>
        @foreach ($sockets as $socket)
            <x-filament::section aside wire:key="socket-{{ $socket->id }}">
                <x-slot name="heading">
                    {{ $socket->profile->name }}
                </x-slot>
                <x-slot name="description">
                    {{ $socket->name }}
                </x-slot>
                @livewire('readings.socket-widget', ['socket' => $socket], key($socket->id))




            </x-filament::section>
        @endforeach
    @endif

    


</x-filament-panels::page>
