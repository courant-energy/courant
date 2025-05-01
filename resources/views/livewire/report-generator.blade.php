<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Generate Report</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Report Type -->
            <div>
                <label for="reportType" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Report
                    Type</label>
                <select id="reportType" wire:model.live="reportType"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="energy_consumption">Energy Consumption</option>
                    <option value="cost_analysis">Cost Analysis</option>
                    <option value="equipment_usage">Equipment Usage</option>
                    <option value="anomaly">Anomaly Report</option>
                </select>
            </div>

            <!-- Time Range (not applicable to equipment usage) -->
            <div x-show="$wire.reportType !== 'equipment_usage' && $wire.reportType !== 'anomaly'">
                <label for="timeRange" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Time
                    Range</label>
                <select id="timeRange" wire:model.live="timeRange"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>

            <!-- Cost per kWh (only for cost analysis) -->
            <div x-show="$wire.reportType === 'cost_analysis'">
                <label for="costPerKwh" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cost per
                    kWh ($)</label>
                <input type="number" id="costPerKwh" wire:model.live="costPerKwh" step="0.01" min="0.01"
                    max="1"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>

            <!-- Chart Type (not applicable to equipment usage) -->
            <div x-show="$wire.reportType !== 'equipment_usage'">
                <label for="chartType" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Chart
                    Type</label>
                <select id="chartType" wire:model.live="chartType"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="bar">Bar Chart</option>
                    <option value="line">Line Chart</option>
                    <option value="pie" x-show="$wire.reportType === 'anomaly'">Pie Chart</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Date Range -->
            <div>
                <label for="startDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start
                    Date</label>
                <input type="date" id="startDate" wire:model.live="startDate"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>

            <div>
                <label for="endDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End
                    Date</label>
                <input type="date" id="endDate" wire:model.live="endDate"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>

            <!-- Profile Filter -->
            <div>
                <label for="profileId"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profile/Appliance
                    Type</label>
                <select id="profileId" wire:model.live="selectedProfileId"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">All Profiles</option>
                    @foreach ($profiles as $profile)
                        <option value="{{ $profile['id'] }}">{{ $profile['name'] }} ({{ $profile['socket_count'] }}
                            devices)</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end">
            <x-filament::button wire:click="generateReport" wire:loading.attr="disabled"
>                <span wire:loading wire:target="generateReport" class="mr-2">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </span>
                Generate Report
            </x-filament::button>
        </div>
    </div>

    <!-- Report Results -->
    <div x-show="$wire.showReport" class="bg-white dark:bg-gray-800 shadow rounded-lg p-4" x-cloak>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Report Results</h2>
            <div class="flex space-x-2">
                <x-filament::button wire:click="downloadCsv" color="success">
                    Download CSV
                </x-filament::button>
                <x-filament::button wire:click="downloadPdf" color="danger">
                    Download PDF
                </x-filament::button>
            </div>
        </div>

        <!-- Chart Container -->
        <div class="mb-6">
            <div class="w-full h-80 bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                <canvas id="reportChart"></canvas>
            </div>
        </div>

        <!-- Report Summary -->
        <div class="mb-6">
            <h3 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Summary</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Energy Consumption Summary -->
                <div x-show="$wire.reportType === 'energy_consumption'"
                    class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Total Energy Consumption</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['total_energy']) ? number_format($reportData['summary']['total_energy'], 2) : 0 }}
                        kWh</p>
                </div>
                <div x-show="$wire.reportType === 'energy_consumption'"
                    class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Average Daily Consumption</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['average_daily']) ? number_format($reportData['summary']['average_daily'], 2) : 0 }}
                        kWh</p>
                </div>

                <!-- Cost Analysis Summary -->
                <div x-show="$wire.reportType === 'cost_analysis'" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Total Energy Cost</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        ${{ isset($reportData['summary']['total_cost']) ? number_format($reportData['summary']['total_cost'], 2) : 0 }}
                    </p>
                </div>
                <div x-show="$wire.reportType === 'cost_analysis'" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Total Energy Used</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['total_energy']) ? number_format($reportData['summary']['total_energy'], 2) : 0 }}
                        kWh</p>
                </div>
                <div x-show="$wire.reportType === 'cost_analysis'" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Average Daily Cost</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        ${{ isset($reportData['summary']['average_daily_cost']) ? number_format($reportData['summary']['average_daily_cost'], 2) : 0 }}
                    </p>
                </div>

                <!-- Equipment Usage Summary -->
                <div x-show="$wire.reportType === 'equipment_usage'"
                    class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Total Monitored Profiles</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['total_profiles']) ? $reportData['summary']['total_profiles'] : 0 }}
                    </p>
                </div>
                <div x-show="$wire.reportType === 'equipment_usage'"
                    class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Average Uptime</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['average_uptime']) ? number_format($reportData['summary']['average_uptime'], 1) : 0 }}%
                    </p>
                </div>

                <!-- Anomaly Summary -->
                <div x-show="$wire.reportType === 'anomaly'" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Total Anomalies</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['total_anomalies']) ? $reportData['summary']['total_anomalies'] : 0 }}
                    </p>
                </div>
                <div x-show="$wire.reportType === 'anomaly'" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Critical Anomalies</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['critical_count']) ? $reportData['summary']['critical_count'] : 0 }}
                    </p>
                </div>
                <div x-show="$wire.reportType === 'anomaly'" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Warning Anomalies</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['warning_count']) ? $reportData['summary']['warning_count'] : 0 }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Detailed Results Section -->
        <div x-show="$wire.reportType === 'equipment_usage'">
            <h3 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Equipment Details</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Profile</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Uptime %</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Min Power</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Max Power</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Avg Power</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Voltage Range</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @if (isset($reportData['equipment_data']))
                            @foreach ($reportData['equipment_data'] as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['profile_name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['uptime_percentage'] }}%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['power_fluctuation']['min'] }}W</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['power_fluctuation']['max'] }}W</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['power_fluctuation']['avg'] }}W</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['voltage_fluctuation']['min'] }} -
                                        {{ $item['voltage_fluctuation']['max'] }}V
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="$wire.reportType === 'anomaly'">
            <h3 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Anomaly Details</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Type</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Severity</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Profile</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Reading</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Message</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @if (isset($reportData['details']))
                            @foreach ($reportData['details'] as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['type'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $item['severity'] === 'critical'
                                                ? 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'
                                                : ($item['severity'] === 'warning'
                                                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
                                                    : 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100') }}">
                                            {{ ucfirst($item['severity']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['profile'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['reading_value'] }}W</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['message'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['timestamp'] }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@assets
    @vite(['resources/css/filament/app/theme.css'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endassets

@script
    <script>
        console.log('Report Generator Component Loaded');
        let reportChart = null;


        Livewire.on('reportGenerated', (event) => {
            console.log('Report Generated Event Triggered', event);
            const reportType = event[0].reportType;
            const chartData = event[0].chartData;
            console.log('Report Type:', reportType);
            console.log('Chart Data:', chartData);


            if (!chartData || !chartData.labels || !chartData.datasets) return;

            const ctx = document.getElementById('reportChart').getContext('2d');
            console.log('Chart Context:', ctx);
            // Destroy existing chart if it exists
            if (reportChart) {
                reportChart.destroy();
            }

            // Configure chart based on report and chart type
            const chartConfig = {
                type: @js($chartType),
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: document.documentElement.classList.contains('dark') ? '#e5e7eb' :
                                    '#374151'
                            }
                        },
                        tooltip: {
                            enabled: true,
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: document.documentElement.classList.contains('dark') ? '#e5e7eb' :
                                    '#374151'
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ?
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: document.documentElement.classList.contains('dark') ? '#e5e7eb' :
                                    '#374151'
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ?
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            };

            // Remove scales for pie chart
            if (@js($chartType) === 'pie') {
                delete chartConfig.options.scales;
            }

            // Create new chart
            reportChart = new Chart(ctx, chartConfig);
        });
    </script>
@endscript
