<div wire:poll.5s>
    {{-- stats --}}
    <div class="grid grid-cols-1 gap-4 my-4 md:grid-cols-2">
        <h2 class="text-lg font-semibold md:col-span-2">Status</h2>
        <div class="hidden">
            <span class="text-gray-900"></span>
            <span class="text-red-600"></span>
            <span class="text-green-600"></span>
            <span class="text-yellow-500"></span>
        </div>
        {{-- Power --}}
        <div>
            @php
                $deviation = 0.05; // 5% deviation allowed
                $lowerGreen = $socket->profile->expected_power * (1 - $deviation);
                $upperGreen = $socket->profile->expected_power * (1 + $deviation);

                $textColorClass = 'text-gray-900'; // Default color

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
            @endphp

            <h3 class="text-xl font-semibold {{ $textColorClass }}">
                {{ $socket->currentPower }} W
            </h3>
            <p class="text-sm text-gray-500">
                Current Power
                <span class="block mt-1 text-xs text-gray-400">
                    Electrical power consumption measured in watts. Represents the rate at which electricity is being
                    used.
                </span>
            </p>
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
            <p class="text-sm text-gray-500">
                Power Factor
                <span class="block mt-1 text-xs text-gray-400">
                    Ratio of real to apparent power. Higher values (>90%) indicate efficient power usage.
                </span>
            </p>
        </div>
    </div>

    {{-- line graph --}}
</div>
