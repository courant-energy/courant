<div class="space-y-6">
    <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
        <h2 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Generate Report</h2>

        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
            <!-- Report Type -->
            <div>
                <label for="reportType" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Report
                    Type</label>
                <select id="reportType" wire:model.live="reportType"
                    class="w-full border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="energy_consumption">Energy Consumption</option>
                    <option value="cost_analysis">Cost Analysis</option>
                    <option value="equipment_usage">Equipment Usage</option>
                    <option value="anomaly">Anomaly Report</option>
                </select>
            </div>

            <!-- Time Range (not applicable to equipment usage) -->
            <div x-show="$wire.reportType !== 'equipment_usage' && $wire.reportType !== 'anomaly'">
                <label for="timeRange" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Time
                    Range</label>
                <select id="timeRange" wire:model.live="timeRange"
                    class="w-full border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>

            <!-- Cost per kWh (only for cost analysis) -->
            <div x-show="$wire.reportType === 'cost_analysis'">
                <label for="costPerKwh" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Cost per
                    kWh (PHP)</label>
                <input type="number" id="costPerKwh" wire:model.live="costPerKwh" step="0.01" min="0.01"
                    max="1"
                    class="w-full border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>

            <!-- Chart Type (not applicable to equipment usage) -->
            <div x-show="$wire.reportType !== 'equipment_usage'">
                <label for="chartType" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Chart
                    Type</label>
                <select id="chartType" wire:model.live="chartType"
                    class="w-full border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="bar">Bar Chart</option>
                    <option value="line">Line Chart</option>
                    <option value="pie" x-show="$wire.reportType === 'anomaly'">Pie Chart</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
            <!-- Date Range -->
            <div>
                <label for="startDate" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Start
                    Date</label>
                <input type="date" id="startDate" wire:model.live="startDate"
                    class="w-full border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <!-- Show minimum date requirement message -->
                <div x-show="$wire.reportType === 'energy_consumption' || $wire.reportType === 'cost_analysis'">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span x-show="$wire.timeRange === 'weekly'">Minimum 7 days required for weekly reports</span>
                        <span x-show="$wire.timeRange === 'monthly'">Minimum 30 days required for monthly reports</span>
                        <span x-show="$wire.timeRange === 'daily'">Daily reports can use any date range</span>
                    </p>
                </div>
            </div>

            <div>
                <label for="endDate" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">End
                    Date</label>
                <input type="date" id="endDate" wire:model.live="endDate"
                    class="w-full border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <span x-data="{ 
                        get daysDifference() {
                            if (!$wire.startDate || !$wire.endDate) return 0;
                            const start = new Date($wire.startDate);
                            const end = new Date($wire.endDate);
                            const diffTime = Math.abs(end - start);
                            return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                        }
                    }" x-text="`Selected range: ${daysDifference} day${daysDifference !== 1 ? 's' : ''}`"></span>
                </div>
            </div>

            <!-- Profile Filter -->
            <div>
                <label for="profileId"
                    class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Profile/Appliance
                    Type</label>
                <select id="profileId" wire:model.live="selectedProfileId"
                    class="w-full border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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
>                
                Generate Report
            </x-filament::button>
        </div>
    </div>

    <!-- Report Results -->
    <div x-show="$wire.showReport" class="p-4 bg-white rounded-lg shadow dark:bg-gray-800" x-cloak>
        <div class="flex items-center justify-between mb-4">
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
            <div class="w-full p-4 rounded-lg h-80 bg-gray-50 dark:bg-gray-900">
                <canvas id="reportChart"></canvas>
            </div>
        </div>

        <!-- Report Summary -->
        <div class="mb-6">
            <h3 class="mb-3 font-medium text-gray-800 text-md dark:text-gray-200">Summary</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <!-- Energy Consumption Summary -->
                <div x-show="$wire.reportType === 'energy_consumption'"
                    class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-300">Total Energy Consumption</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['total_energy']) ? number_format($reportData['summary']['total_energy'], 2) : 0 }}
                        kWh</p>
                </div>
                <div x-show="$wire.reportType === 'energy_consumption'"
                    class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-300">Average Daily Consumption</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['average_daily']) ? number_format($reportData['summary']['average_daily'], 2) : 0 }}
                        kWh</p>
                </div>

                <!-- Cost Analysis Summary -->
                <div x-show="$wire.reportType === 'cost_analysis'" class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-300">Total Energy Cost</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        PHP {{ isset($reportData['summary']['total_cost']) ? number_format($reportData['summary']['total_cost'], 2) : 0 }}
                    </p>
                </div>
                <div x-show="$wire.reportType === 'cost_analysis'" class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-300">Total Energy Used</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['total_energy']) ? number_format($reportData['summary']['total_energy'], 2) : 0 }}
                        kWh</p>
                </div>
                <div x-show="$wire.reportType === 'cost_analysis'" class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-300">Average Daily Cost</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        PHP {{ isset($reportData['summary']['average_daily_cost']) ? number_format($reportData['summary']['average_daily_cost'], 2) : 0 }}
                    </p>
                </div>

                <!-- Equipment Usage Summary -->
                <div x-show="$wire.reportType === 'equipment_usage'"
                    class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-300">Total Monitored Profiles</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['total_profiles']) ? $reportData['summary']['total_profiles'] : 0 }}
                    </p>
                </div>
                <div x-show="$wire.reportType === 'equipment_usage'"
                    class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-300">Average Uptime</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['average_uptime']) ? number_format($reportData['summary']['average_uptime'], 1) : 0 }}%
                    </p>
                </div>

                <!-- Anomaly Summary -->
                <div x-show="$wire.reportType === 'anomaly'" class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-300">Total Anomalies</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['total_anomalies']) ? $reportData['summary']['total_anomalies'] : 0 }}
                    </p>
                </div>
                <div x-show="$wire.reportType === 'anomaly'" class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-300">Critical Anomalies</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['critical_count']) ? $reportData['summary']['critical_count'] : 0 }}
                    </p>
                </div>
                <div x-show="$wire.reportType === 'anomaly'" class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-300">Warning Anomalies</p>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ isset($reportData['summary']['warning_count']) ? $reportData['summary']['warning_count'] : 0 }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Detailed Results Section -->
        <div x-show="$wire.reportType === 'equipment_usage'">
            <h3 class="mb-3 font-medium text-gray-800 text-md dark:text-gray-200">Equipment Details</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Profile</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Uptime %</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Min Power</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Max Power</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Avg Power</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Voltage Range</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @if (isset($reportData['equipment_data']))
                            @foreach ($reportData['equipment_data'] as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                        {{ $item['profile_name'] }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                        {{ $item['uptime_percentage'] }}%</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                        {{ $item['power_fluctuation']['min'] }}W</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                        {{ $item['power_fluctuation']['max'] }}W</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                        {{ $item['power_fluctuation']['avg'] }}W</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
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
            <h3 class="mb-3 font-medium text-gray-800 text-md dark:text-gray-200">Anomaly Details</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Type</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Severity</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Profile</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Reading</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Message</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @if (isset($reportData['details']))
                            @foreach ($reportData['details'] as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                        {{ $item['type'] }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
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
                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                        {{ $item['profile'] }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                        {{ $item['reading_value'] }}W</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['message'] }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
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

        // Listen for date range adjustments
        Livewire.on('dateRangeAdjusted', (event) => {
            const data = event[0];
            const timeRange = data.timeRange;
            const reason = data.reason || 'validation';
            
            let message = '';
            if (timeRange === 'weekly') {
                message = 'Date range adjusted to minimum 7 days for weekly reporting.';
            } else if (timeRange === 'monthly') {
                message = 'Date range adjusted to minimum 30 days for monthly reporting.';
            }
            
            if (message) {
                // Show a temporary notification
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300';
                notification.textContent = message;
                document.body.appendChild(notification);
                
                // Remove after 3 seconds
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 300);
                }, 3000);
            }
        });

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
                type: $wire.chartType,
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
            if ($wire.chartType === 'pie') {
                delete chartConfig.options.scales;
            }

            // Create new chart
            reportChart = new Chart(ctx, chartConfig);
        });
    </script>
@endscript
