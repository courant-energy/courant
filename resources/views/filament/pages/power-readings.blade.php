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
        <div class="grid grid-cols-1 gap-8 mt-4 md:grid-cols-2">


            @foreach ($sockets as $socket)
                <div wire:key="socket-{{ $socket->id }}" class="space-y-4">

                    <x-filament::section>
                        <x-slot name="heading">
                            {{ $socket->profile?->name ?? 'No Profile Assigned' }}
                        </x-slot>
                        <x-slot name="description">
                            {{ $socket->name }}
                            @if (!$socket->profile)
                                <span class="block mt-1 text-xs text-amber-500">
                                    No profile assigned. Power thresholds and expected values unavailable.
                                </span>
                            @endif
                        </x-slot>
                        @livewire('readings.socket-widget', ['socket' => $socket], key('stats-' . $socket->id))
                    </x-filament::section>
                    <hr>
                    <div class="grid grid-cols-1 gap-4" style="margin-top: 2rem;">

                        <div class="flex flex-col gap-4" x-data="{ activeTab: 'energy-consumption' }">

                            {{-- Chart Type Tabs --}}
                            <x-filament::tabs>
                                <x-filament::tabs.item alpine-active="activeTab === 'energy-consumption'"
                                    x-on:click="activeTab = 'energy-consumption'">
                                    Energy Consumption (kWh)
                                </x-filament::tabs.item>
                                <x-filament::tabs.item alpine-active="activeTab === 'power'"
                                    x-on:click="activeTab = 'power'">
                                    Power (W)
                                </x-filament::tabs.item>
                                <x-filament::tabs.item alpine-active="activeTab === 'power-factor'"
                                    x-on:click="activeTab = 'power-factor'">
                                    Power Factor
                                </x-filament::tabs.item>
                            </x-filament::tabs>


                            {{-- Per-Socket Time Range Filter --}}
                            <x-filament::section>
                                <x-slot name="heading">
                                    Time Range
                                </x-slot>

                                <div class="flex flex-wrap gap-2">
                                    @foreach ($this->getFilterOptions() as $value => $label)
                                        <x-filament::button
                                            wire:click="setSocketFilter({{ $socket->id }}, '{{ $value }}')"
                                            :color="$this->getSocketFilter($socket->id) === $value
                                                ? 'primary'
                                                : 'gray'" :outlined="$this->getSocketFilter($socket->id) !== $value" size="sm">
                                            {{ $label }}
                                        </x-filament::button>
                                    @endforeach
                                </div>
                            </x-filament::section>



                            {{-- Power Consumption Graph --}}
                            <div x-show="activeTab === 'energy-consumption'"
                                :class="!{{ $this->hasSocketData($socket) ? 'true' : 'false' }} ? 'hidden' : ''">
                                @livewire(App\Filament\Widgets\PowerConsumption::class, ['socket' => $socket, 'filter' => $this->getSocketFilter($socket->id)], key('power-consumption-' . $socket->id . '-' . $this->getSocketFilter($socket->id)))
                            </div>

                            {{-- Power Graph --}}
                            <div x-show="activeTab === 'power'"
                                :class="!{{ $this->hasSocketData($socket) ? 'true' : 'false' }} ? 'hidden' : ''">
                                @livewire(App\Filament\Admin\Widgets\Power::class, ['socket' => $socket, 'filter' => $this->getSocketFilter($socket->id)], key('power-' . $socket->id . '-' . $this->getSocketFilter($socket->id)))
                            </div>

                            {{-- Power Factor --}}
                            <div x-show="activeTab === 'power-factor'"
                                :class="!{{ $this->hasSocketData($socket) ? 'true' : 'false' }} ? 'hidden' : ''">
                                @livewire(App\Filament\Widgets\PowerFactor::class, ['socket' => $socket, 'filter' => $this->getSocketFilter($socket->id)], key('power-factor-' . $socket->id . '-' . $this->getSocketFilter($socket->id)))
                            </div>

                            {{-- Empty State for All Charts --}}
                            <div
                                :class="{{ $this->hasSocketData($socket) ? 'true' : 'false' }} ? 'hidden' :
                                    'flex flex-col items-center justify-center py-16 text-center'">
                                <x-filament::icon icon="heroicon-o-chart-bar"
                                    class="w-16 h-16 mb-6 text-gray-400 dark:text-gray-500" />
                                <h3 class="mb-3 text-xl font-medium text-gray-900 dark:text-gray-100">
                                    No data available for
                                    {{ strtolower($this->getFilterOptions()[$this->getSocketFilter($socket->id)]) }}.
                                </h3>
                                <p class="max-w-md mb-4 text-sm text-gray-500 dark:text-gray-400">
                                    Try selecting a different time range above or check if the device is collecting data
                                    properly.
                                </p>
                                <x-filament::button wire:click="loadData" size="sm" color="gray" outlined>
                                    Refresh Data
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif




</x-filament-panels::page>
