<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <h2 class="text-xl font-bold dark:text-white">Energy Cost Calculator</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Calculate your monthly energy cost based on power consumption and rate</p>

            <form wire:submit="calculateCost">
                {{ $this->form }}
            </form>

            <div class="p-4 mt-4 bg-gray-50 rounded-lg dark:bg-gray-800">
                <div class="text-sm text-gray-500 dark:text-gray-400">Monthly Cost Estimate</div>
                <div class="flex items-end space-x-1">
                    <span class="text-xl font-bold dark:text-white">PHP {{ number_format($calculatedCost, 2) }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">/month</span>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Based on constant usage for 30 days</p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
