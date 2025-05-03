<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800">AI-Powered Insights</h2>
        
        <div class="flex items-center gap-4">
            @if($cooldown_minutes_remaining > 0)
                <div class="text-sm text-amber-600">
                    <span class="font-medium">Cooldown:</span> {{ $cooldown_minutes_remaining }} min remaining
                </div>
            @else
                <div class="text-sm text-gray-600">
                    <span class="font-medium">Attempts:</span> {{ $remainingAttempts }} of {{ $max_attempts }} remaining
                </div>
            @endif
            
            <button 
                wire:click="generateInsights" 
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-300 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                {{ $generation_in_progress || $cooldown_minutes_remaining > 0 ? 'disabled' : '' }}
            >
                @if($isLoading)
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Generating Insights...</span>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Generate Insights</span>
                @endif
            </button>
        </div>
    </div>
    
    @if($cooldown_minutes_remaining > 0 && empty($insights))
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-700">
                        You've reached the maximum number of insight generations. Please wait {{ $cooldown_minutes_remaining }} minutes before generating more insights.
                    </p>
                </div>
            </div>
        </div>
    @endif
    
    @if($isLoading)
        <div class="flex flex-col items-center justify-center py-8">
            <div class="animate-pulse flex flex-col items-center space-y-4">
                <div class="rounded-full bg-blue-100 h-16 w-16 flex items-center justify-center">
                    <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
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
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error:</strong>
            <span class="block sm:inline">{{ $error }}</span>
        </div>
    @elseif(count($insights) > 0)
        <div class="space-y-6">
            @foreach($insights as $insight)
                <div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center mb-2">
                        @if($insight['insight_type'] === 'Energy Wastage')
                            <span class="text-yellow-500 bg-yellow-100 px-2 py-1 rounded-full text-xs font-semibold">Energy Wastage</span>
                        @elseif($insight['insight_type'] === 'Anomaly Detection')
                            <span class="text-red-500 bg-red-100 px-2 py-1 rounded-full text-xs font-semibold">Anomaly Detection</span>
                        @elseif($insight['insight_type'] === 'Predictive Maintenance')
                            <span class="text-purple-500 bg-purple-100 px-2 py-1 rounded-full text-xs font-semibold">Predictive Maintenance</span>
                        @endif
                        
                        <div class="ml-auto">
                            @if($insight['priority'] === 'High')
                                <span class="text-red-500 bg-red-100 px-2 py-1 rounded-full text-xs font-semibold">High Priority</span>
                            @elseif($insight['priority'] === 'Medium')
                                <span class="text-yellow-500 bg-yellow-100 px-2 py-1 rounded-full text-xs font-semibold">Medium Priority</span>
                            @elseif($insight['priority'] === 'Low')
                                <span class="text-green-500 bg-green-100 px-2 py-1 rounded-full text-xs font-semibold">Low Priority</span>
                            @endif
                        </div>
                    </div>
                    
                    <h3 class="text-gray-800 font-medium text-lg mb-2">Insight #{{ $insight['insight_id'] }}</h3>
                    <p class="text-gray-700 mb-3">{{ $insight['description'] }}</p>
                    
                    @if(isset($insight['supporting_data']) && count($insight['supporting_data']) > 0)
                        <div class="mb-3">
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Affected Devices:</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($insight['supporting_data'] as $data)
                                    <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded text-xs">
                                        {{ $data['device_name'] ?? 'Unknown device' }} (Socket ID: {{ $data['socket_id'] ?? 'unknown' }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @if(isset($insight['potential_causes']) && count($insight['potential_causes']) > 0)
                        <div class="mb-3">
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Potential Causes:</h4>
                            <ul class="list-disc list-inside text-sm text-gray-700 pl-2">
                                @foreach($insight['potential_causes'] as $cause)
                                    <li>{{ $cause }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    @if(isset($insight['recommendations']) && count($insight['recommendations']) > 0)
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Recommendations:</h4>
                            <ul class="list-disc list-inside text-sm text-gray-700 pl-2">
                                @foreach($insight['recommendations'] as $recommendation)
                                    <li>{{ $recommendation }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No insights generated yet</h3>
            <p class="mt-1 text-sm text-gray-500">
                Click the 'Generate Insights' button to analyze your data from the past 30 days.
                @if ($remainingAttempts < $max_attempts)
                    <br>
                    <span class="text-amber-600 font-medium">
                        Note: You have {{ $remainingAttempts }} attempts remaining in this hour.
                    </span>
                @endif
            </p>
        </div>
    @endif
</div>
