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
            <div wire:key="socket-{{ $socket->id }}" class="space-y-4">

                <x-filament::section aside>
                    <x-slot name="heading">
                        {{ $socket->profile?->name ?? 'No Profile Assigned' }}
                    </x-slot>
                    <x-slot name="description">
                        {{ $socket->name }}
                        @if(!$socket->profile)
                            <span class="block text-xs text-amber-500 mt-1">
                                No profile assigned. Power thresholds and expected values unavailable.
                            </span>
                        @endif
                    </x-slot>
                    @livewire('readings.socket-widget', ['socket' => $socket], key('stats-'.$socket->id))
                </x-filament::section>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div></div>
                    @livewire(App\Filament\Widgets\Readings\EnergyUsageGraph::class, ['socket' => $socket], key($socket->id))
                </div>
            </div>
        @endforeach
    @endif




</x-filament-panels::page>
