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
                        @if (!$socket->profile)
                            <span class="block mt-1 text-xs text-amber-500">
                                No profile assigned. Power thresholds and expected values unavailable.
                            </span>
                        @endif
                    </x-slot>
                    @livewire('readings.socket-widget', ['socket' => $socket], key('stats-' . $socket->id))
                </x-filament::section>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div></div>
                    <div class="flex flex-col col-span-2 gap-4" x-data="{ activeTab: 'power-consumption' }">
                        <x-filament::tabs>
                            <x-filament::tabs.item alpine-active="activeTab === 'power-consumption'"
                                x-on:click="activeTab = 'power-consumption'">
                                Power Consumption (kWh)
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

                        {{-- Power Consumption Graph --}}
                        <div x-show="activeTab === 'power-consumption'">
                            @livewire(App\Filament\Widgets\PowerConsumption::class, ['socket' => $socket], key('power-consumption-' . $socket->id))
                        </div>

                        {{-- Power Graph --}}
                        <div x-show="activeTab === 'power'">
                        </div>

                        {{-- Power Factor --}}
                        <div x-show="activeTab === 'power-factor'">
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif




</x-filament-panels::page>
