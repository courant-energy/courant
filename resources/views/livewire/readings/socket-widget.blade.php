<div wire:poll.5s>
    {{-- stats --}}
    <div class="grid grid-cols-1 gap-4 my-4 md:grid-cols-3">
        <h2 class="text-lg font-semibold md:col-span-3">Status</h2>
        <div class="hidden">
            <span class="text-gray-900"></span>
            <span class="text-red-600"></span>
            <span class="text-green-600"></span>
            <span class="text-yellow-500"></span>
        </div>
        <div>
            {{-- TODO: Add the Power Consumption (kWh) --}} 
            <h3 class="text-xl font-semibold">
                {{ number_format($socket->powerConsumption ?? 0, 2) }} kWh
            </h3>
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Energy Consumption</p>
                <button class="ml-2 text-gray-400 hover:text-gray-600" onclick="this.nextElementSibling.classList.toggle('hidden')" type="button">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <div class="hidden absolute z-10 p-2 mt-1 text-xs text-white bg-gray-800 rounded shadow-lg max-w-xs">
                    Total energy consumed over time, measured in kilowatt-hours (kWh).
                </div>
            </div>
        </div>


        {{-- Power --}}
        <div>

            @php
                $textColorClass = 'text-gray-900 dark:text-gray-400'; // Default color

                if ($socket->profile) {
                    $deviation = 0.05; // 5% deviation allowed
                    $lowerGreen = $socket->profile->expected_power * (1 - $deviation);
                    $upperGreen = $socket->profile->expected_power * (1 + $deviation);

                    if (
                        $socket->currentPower > $socket->profile->max_power_threshold ||
                        $socket->currentPower < $socket->profile->min_power_threshold
                    ) {
                        $textColorClass = 'text-red-600';
                    } elseif ($socket->currentPower >= $lowerGreen && $socket->currentPower <= $upperGreen) {
                        $textColorClass = 'text-green-600';
                    } else {
                        $textColorClass = 'text-yellow-500';
                    }
                }
            @endphp

            <h3 class="text-xl font-semibold {{ $textColorClass }}">
                {{ number_format($socket->currentPower ?? 0, 2) }} W
            </h3>
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Current Power</p>
                <button class="ml-2 text-gray-400 hover:text-gray-600" onclick="this.nextElementSibling.classList.toggle('hidden')" type="button">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <div class="hidden absolute z-10 p-2 mt-1 text-xs text-white bg-gray-800 rounded shadow-lg max-w-xs">
                    Electrical power consumption measured in watts. Represents the rate at which electricity is being used.
                </div>
            </div>
        </div>
        {{-- Power Factor --}}
        <div>
            @php
                // Power Factor color coding
                $pfTextColorClass = 'text-gray-900'; // Default color

                if ($socket->currentPowerFactor < 0.8) {
                    $pfTextColorClass = 'text-red-600'; // Bad power factor (<80%)
                } elseif ($socket->currentPowerFactor >= 0.9) {
                    $pfTextColorClass = 'text-green-600'; // Good power factor (≥90%)
                } else {
                    $pfTextColorClass = 'text-yellow-500'; // Acceptable power factor (80%-89%)
                }
            @endphp

            <h3 class="text-xl font-semibold {{ $pfTextColorClass }}">
                {{ $socket->currentPowerFactor * 100 }} %
            </h3>
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Power Factor</p>
                <button class="ml-2 text-gray-400 hover:text-gray-600" onclick="this.nextElementSibling.classList.toggle('hidden')" type="button">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <div class="hidden absolute z-10 p-2 mt-1 text-xs text-white bg-gray-800 rounded shadow-lg max-w-xs">
                    Ratio of real to apparent power. Higher values (>90%) indicate efficient power usage.
                </div>
            </div>
        </div>
    </div>

    {{-- line graph --}}
</div>
