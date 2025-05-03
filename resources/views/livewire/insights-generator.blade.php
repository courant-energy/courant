<x-filament::section>
    <div class="flex justify-between items-center mb-4">
        <x-filament::section.heading>AI-Powered Insights</x-filament::section.heading>

        <div class="flex items-center gap-4">
            @if ($cooldown_minutes_remaining > 0)
                <x-filament::badge color="warning">
                    <span class="font-medium">Cooldown:</span> {{ $cooldown_minutes_remaining }} min remaining
                </x-filament::badge>
            @else
                <x-filament::badge color="gray">
                    <span class="font-medium">Attempts:</span> {{ $remainingAttempts }} of {{ $max_attempts }} remaining
                </x-filament::badge>
            @endif

            <x-filament::button 
            wire:disabled="!$canGenerateInsights || $cooldown_minutes_remaining > 0"
            wire:click="generateInsights" icon="heroicon-o-bolt" color="primary">

                Generate Insights

            </x-filament::button>
        </div>
    </div>

    @if ($cooldown_minutes_remaining > 0 && empty($insights))
        <div class="flex items-center mb-4 text-center">
            <div class="text-amber-700 flex gap-2">
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 text-amber-400" />
                <p class="text-sm">
                    You've reached the maximum number of insight generations. Please wait
                    {{ $cooldown_minutes_remaining }} minutes before generating more insights.
                </p>
            </div>
        </div>
    @endif

    @if ($isLoading)
        <div class="flex flex-col items-center justify-center py-8">
            <div class="animate-pulse flex flex-col items-center space-y-4">
                <div class="rounded-full bg-blue-100 h-16 w-16 flex items-center justify-center">
                    <x-filament::loading-indicator class="h-8 w-8 text-blue-600" />
                </div>
                <div class="text-center">
                    <p class="text-lg font-medium text-gray-700">Analyzing your data</p>
                    <p class="text-sm text-gray-500 mt-1">This might take a few moments...</p>
                </div>
                <div class="w-full max-w-md">
                    <div class="h-2 bg-blue-200 rounded"></div>
                </div>
            </div>
        </div>
    @elseif($error)
        <x-filament::section.heading>
            <x-filament::badge color="danger" class="w-full">
                <strong class="font-bold">Error:</strong>
                <span class="block sm:inline">{{ $error }}</span>
            </x-filament::badge>
        </x-filament::section.heading>
    @elseif(count($insights) > 0)
        <div class="space-y-6">
            @foreach ($insights as $insight)
                <x-filament::section>
                    <div class="flex items-center mb-2">
                        @if ($insight['insight_type'] === 'Energy Wastage')
                            <x-filament::badge color="warning">Energy Wastage</x-filament::badge>
                        @elseif($insight['insight_type'] === 'Anomaly Detection')
                            <x-filament::badge color="danger">Anomaly Detection</x-filament::badge>
                        @elseif($insight['insight_type'] === 'Predictive Maintenance')
                            <x-filament::badge color="info">Predictive Maintenance</x-filament::badge>
                        @endif

                        <div class="ml-auto">
                            @if ($insight['priority'] === 'High')
                                <x-filament::badge color="danger">High Priority</x-filament::badge>
                            @elseif($insight['priority'] === 'Medium')
                                <x-filament::badge color="warning">Medium Priority</x-filament::badge>
                            @elseif($insight['priority'] === 'Low')
                                <x-filament::badge color="success">Low Priority</x-filament::badge>
                            @endif
                        </div>
                    </div>

                    <x-filament::section.heading size="sm" tag="h3">Insight
                        #{{ $insight['insight_id'] }}</x-filament::section.heading>
                    <p class="text-gray-700 mb-3">{{ $insight['description'] }}</p>

                    @if (isset($insight['supporting_data']) && count($insight['supporting_data']) > 0)
                        <div class="mb-3">
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Affected Devices:</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($insight['supporting_data'] as $data)
                                    <x-filament::badge color="info">
                                        {{ $data['device_name'] ?? 'Unknown device' }} (Socket ID:
                                        {{ $data['socket_id'] ?? 'unknown' }})
                                    </x-filament::badge>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (isset($insight['potential_causes']) && count($insight['potential_causes']) > 0)
                        <div class="mb-3">
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Potential Causes:</h4>
                            <ul class="list-disc list-inside text-sm text-gray-700 pl-2">
                                @foreach ($insight['potential_causes'] as $cause)
                                    <li>{{ $cause }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (isset($insight['recommendations']) && count($insight['recommendations']) > 0)
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Recommendations:</h4>
                            <ul class="list-disc list-inside text-sm text-gray-700 pl-2">
                                @foreach ($insight['recommendations'] as $recommendation)
                                    <li>{{ $recommendation }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </x-filament::section>
            @endforeach
        </div>
    @else
        <x-filament::section class="text-center py-6">
            <x-filament::icon icon="heroicon-o-information-circle" class="mx-auto h-12 w-12 text-gray-400" />
            <x-filament::section.heading size="sm" tag="h3" class="mt-2">No insights generated
                yet</x-filament::section.heading>
            <p class="mt-1 text-sm text-gray-500">
                Click the 'Generate Insights' button to analyze your data from the past 30 days.
                @if ($remainingAttempts < $max_attempts)
                    <br>
                    <span class="text-amber-600 font-medium">
                        Note: You have {{ $remainingAttempts }} attempts remaining in this 20-minute window.
                    </span>
                @endif
            </p>
        </x-filament::section>
    @endif
</x-filament::section>
