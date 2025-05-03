<x-filament-panels::page>
    <div class="space-y-8">
        <p class="text-gray-500 dark:text-gray-400">
            Generate customized reports based on your device data, energy consumption, and anomaly detection.
        </p>
        
        <div class="mt-6 mb-10">
            @livewire('report-generator')
        </div>
        
        <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">AI-Powered Insights</h2>
            <p class="text-gray-500 dark:text-gray-400 mb-6">
                Generate actionable insights from your data using Gemini AI. The system analyzes your readings, profiles, and alerts from the past 30 days to identify energy wastage, anomalies, and potential equipment failures.
            </p>
            @livewire('insights-generator')
        </div>
    </div>
</x-filament-panels::page>
