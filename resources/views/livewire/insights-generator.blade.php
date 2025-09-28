<x-filament::section
    wire:key="insights-section-{{ md5(serialize($insights) . $isLoading . $error . $hasFreshData . ($lastUpdated ?? '')) }}">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-4">
        <x-filament::section.heading>AI-Powered Insights</x-filament::section.heading>

        <div class="flex flex-col md:flex-row mt-4 md:items-center gap-4">
            @if ($cooldown_minutes_remaining > 0)
                <x-filament::badge color="warning">
                    <span class="font-medium">Cooldown:</span> {{ $cooldown_minutes_remaining }} min remaining
                </x-filament::badge>
            @else
                <x-filament::badge color="gray">
                    <span class="font-medium">Attempts:</span> {{ $remainingAttempts }} of {{ $max_attempts }} remaining
                </x-filament::badge>
            @endif

         
                @if (count($insights) > 0)
                    <x-filament::button wire:click="clearCache" icon="heroicon-o-trash" color="gray" size="sm">
                        Clear Cache
                    </x-filament::button>
                @endif
    
                <x-filament::button wire:disabled="!$canGenerateInsights || $cooldown_minutes_remaining > 0"
                    wire:click="generateInsights" icon="heroicon-o-bolt" color="primary">
    
                    Generate Insights
    
                </x-filament::button>
          
        </div>
    </div>

    @if ($cooldown_minutes_remaining > 0 && empty($insights))
        <div class="flex items-center mb-4 text-center">
            <div class="flex gap-2 text-amber-700 dark:text-amber-400">
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-5 h-5 text-amber-400" />
                <p class="text-sm">
                    You've reached the maximum number of insight generations. Please wait
                    {{ $cooldown_minutes_remaining }} minutes before generating more insights.
                </p>
            </div>
        </div>
    @endif

    @if ($isLoading)
        <div class="flex flex-col items-center justify-center py-8">
            <div class="flex flex-col items-center space-y-4 animate-pulse">
                <div class="flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full dark:bg-blue-900">
                    <x-filament::loading-indicator class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="text-center">
                    <p class="text-lg font-medium text-gray-700 dark:text-gray-300">Analyzing your data</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This might take a few moments...</p>
                </div>
                <div class="w-full max-w-md">
                    <div class="h-2 bg-blue-200 rounded dark:bg-blue-800"></div>
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
        <div class="mb-4">
            @if ($hasFreshData)
                <div class="inline-block">
                    <x-filament::badge color="success" class="text-xs">
                        <div class="flex items-center">
                            <x-filament::icon icon="heroicon-o-check-circle" class="w-3 h-3 mr-1" />
                            Fresh insights generated
                        </div>
                    </x-filament::badge>
                </div>
            @else
                <div class="inline-block">
                    <x-filament::badge color="info" class="text-xs">
                        <div class="flex items-center">
                            <x-filament::icon icon="heroicon-o-clock" class="w-3 h-3 mr-1" />
                            Cached insights
                        </div>
                    </x-filament::badge>
                </div>
            @endif
        </div>
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
                        @elseif($insight['insight_type'] === 'Alert System Effectiveness')
                            <x-filament::badge color="primary">Alert System Effectiveness</x-filament::badge>
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
                    <p class="mb-3 text-gray-700 dark:text-gray-300">{{ $insight['description'] }}</p>

                    @if (isset($insight['supporting_data']) && count($insight['supporting_data']) > 0)
                        <div class="mb-3">
                            <h4 class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Affected Devices:</h4>
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
                            <h4 class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Potential Causes:</h4>
                            <ul class="pl-2 text-sm text-gray-700 list-disc list-inside dark:text-gray-300">
                                @foreach ($insight['potential_causes'] as $cause)
                                    <li>{{ $cause }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (isset($insight['recommendations']) && count($insight['recommendations']) > 0)
                        <div>
                            <h4 class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Recommendations:</h4>
                            <ul class="pl-2 text-sm text-gray-700 list-disc list-inside dark:text-gray-300">
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
        <x-filament::section class="py-6 text-center">
            <x-filament::icon icon="heroicon-o-information-circle"
                class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500" />
            <x-filament::section.heading size="sm" tag="h3" class="mt-2">No insights generated
                yet</x-filament::section.heading>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Click the 'Generate Insights' button to analyze your data from the past 30 days.
                @if ($remainingAttempts < $max_attempts)
                    <br>
                    <span class="font-medium text-amber-600 dark:text-amber-500">
                        Note: You have {{ $remainingAttempts }} attempts remaining in this 20-minute window.
                    </span>
                @endif
            </p>
        </x-filament::section>
    @endif
</x-filament::section>

<script>
    document.addEventListener('livewire:initialized', function() {
        @this.on('insights-updated', function(data) {
            // Force component refresh
            setTimeout(() => {
                @this.$refresh();
            }, 100);
        });
    });
</script>
