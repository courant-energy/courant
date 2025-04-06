<x-filament-panels::page>

    {{-- CONFIG CARD --}}
    {{-- Contents:
        - Select IoT Device
        - Date Range Selector
    --}}
    <x-filament::section aside>
        <x-slot name="heading">
            Filter
        </x-slot>
        <x-slot name="description">
            Select the IoT device and date range for the data you want to view.
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


    {{-- CARD 1: REALTIME CONSUMPTION --}}
    {{-- Contents:
        - Consumption Graph
        - Statistics
    --}}

    @if ($hasSelectedDevice)
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $device->name }}
        </h1>

        @foreach ($sockets as $socket)
            <x-filament::section aside>
                <x-slot name="heading">
                    {{ $socket->profile->name }}

                </x-slot>

                <x-slot name="description">
                    {{ $socket->name }}
                </x-slot>

                <div class="grid grid-cols-1 gap-4 mt-4 lg:grid-cols-2">
                    {{-- graph of consumption --}}
                    

                    {{-- stats --}}

                </div>
            </x-filament::section>
        @endforeach
    @endif


</x-filament-panels::page>
