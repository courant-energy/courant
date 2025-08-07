<?php

namespace App\Livewire;

use App\Models\Profile;
use App\Services\ReportService;
use App\Services\PdfReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Livewire\Component;

class ReportGenerator extends Component
{
    public string $reportType = 'energy_consumption';
    public string $timeRange = 'daily';
    public string $startDate;
    public string $endDate;
    public ?int $selectedProfileId = null;
    public float $costPerKwh = 11;
    public array $reportData = [];
    public bool $isLoading = false;
    public string $chartType = 'bar';
    public bool $showReport = false;

    protected $listeners = ['refreshReport'];

    public function mount()
    {
        $this->startDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }

    public function updatedTimeRange()
    {
        $this->adjustDateRangeForTimeRange();
    }

    public function updatedStartDate()
    {
        $this->validateAndAdjustDateRange();
    }

    public function updatedEndDate()
    {
        $this->validateAndAdjustDateRange();
    }

    protected function adjustDateRangeForTimeRange()
    {
        $endDate = Carbon::parse($this->endDate);
        $adjusted = false;
        
        switch ($this->timeRange) {
            case 'daily':
                // No minimum requirement for daily, but ensure at least 1 day
                $minStartDate = $endDate->copy()->subDay();
                break;
            case 'weekly':
                // Minimum 7 days for weekly reporting
                $minStartDate = $endDate->copy()->subDays(6); // 7 days total including end date
                break;
            case 'monthly':
                // Minimum 30 days for monthly reporting
                $minStartDate = $endDate->copy()->subDays(29); // 30 days total including end date
                break;
            default:
                return;
        }

        $currentStartDate = Carbon::parse($this->startDate);
        
        // If current start date is after the minimum required, adjust it
        if ($currentStartDate->gt($minStartDate)) {
            $this->startDate = $minStartDate->format('Y-m-d');
            $adjusted = true;
        }
        
        if ($adjusted) {
            $this->dispatch('dateRangeAdjusted', [
                'timeRange' => $this->timeRange,
                'newStartDate' => $this->startDate,
                'endDate' => $this->endDate,
                'reason' => 'time_range_changed'
            ]);
        }
    }

    protected function validateAndAdjustDateRange()
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        
        // Ensure start date is not after end date
        if ($startDate->gt($endDate)) {
            $this->endDate = $startDate->format('Y-m-d');
            $endDate = $startDate;
        }
        
        $daysDifference = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end dates
        $adjusted = false;
        
        switch ($this->timeRange) {
            case 'weekly':
                if ($daysDifference < 7) {
                    // Extend the start date to make it 7 days
                    $this->startDate = $endDate->copy()->subDays(6)->format('Y-m-d');
                    $adjusted = true;
                }
                break;
            case 'monthly':
                if ($daysDifference < 30) {
                    // Extend the start date to make it 30 days
                    $this->startDate = $endDate->copy()->subDays(29)->format('Y-m-d');
                    $adjusted = true;
                }
                break;
        }
        
        if ($adjusted) {
            $this->dispatch('dateRangeAdjusted', [
                'timeRange' => $this->timeRange,
                'newStartDate' => $this->startDate,
                'endDate' => $this->endDate
            ]);
        }
    }

    public function getMinimumDaysForTimeRange(): int
    {
        switch ($this->timeRange) {
            case 'daily':
                return 1;
            case 'weekly':
                return 7;
            case 'monthly':
                return 30;
            default:
                return 1;
        }
    }

    protected function isDateRangeValid(): bool
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        
        $daysDifference = $startDate->diffInDays($endDate) + 1;
        $minimumDays = $this->getMinimumDaysForTimeRange();
        
        return $daysDifference >= $minimumDays;
    }

    public function generateReport()
    {
        $this->isLoading = true;
        $this->showReport = false;

        // Validate and adjust date range before generating report
        $this->validateAndAdjustDateRange();

        // Double-check the date range meets minimum requirements
        if (!$this->isDateRangeValid()) {
            $this->isLoading = false;
            \Filament\Notifications\Notification::make()
                ->title('Invalid Date Range')
                ->body('Please select a valid date range that meets the minimum requirements for the selected time range.')
                ->danger()
                ->send();
            return;
        }

        $reportService = app(ReportService::class);
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        switch ($this->reportType) {
            case 'energy_consumption':
                $this->reportData = $reportService->generateEnergyConsumptionReport(
                    $this->timeRange,
                    $startDate,
                    $endDate,
                    $this->selectedProfileId
                );
                break;

            case 'cost_analysis':
                $this->reportData = $reportService->generateCostAnalysisReport(
                    $this->timeRange,
                    $startDate,
                    $endDate,
                    $this->costPerKwh,
                    $this->selectedProfileId
                );
                break;

            case 'equipment_usage':
                $this->reportData = $reportService->generateEquipmentUsageReport(
                    $startDate,
                    $endDate,
                    $this->selectedProfileId
                );
                break;

            case 'anomaly':
                $this->reportData = $reportService->generateAnomalyReport(
                    $startDate,
                    $endDate,
                    $this->selectedProfileId
                );
                break;
        }

        $this->isLoading = false;
        $this->showReport = true;
        
        // Dispatch browser event to render chart
        $this->dispatch('reportGenerated', [
            'reportType' => $this->reportType,
            'chartData' => $this->getChartData(),
        ]);
    }

    public function refreshReport()
    {
        if ($this->showReport) {
            $this->generateReport();
        }
    }

    public function downloadCsv()
    {
        $reportService = app(ReportService::class);
        $csv = $reportService->exportToCsv($this->reportData, $this->reportType);
        
        $timestamp = now()->format('Y-m-d_H-i');
        $filename = "report_{$this->reportType}_{$timestamp}.csv";
        
        // Return a download response with the CSV content
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function downloadPdf()
    {
        $user = Auth::user();
        
        // Dispatch job to generate PDF in the background
        \App\Jobs\GeneratePdfReport::dispatch(
            $this->reportData,
            $this->reportType,
            $this->timeRange,
            Carbon::parse($this->startDate),
            Carbon::parse($this->endDate)->endOfDay(),
            $this->selectedProfileId,
            $user->id,
            $user->company_name
        );
        
        // Show immediate feedback to the user
        \Filament\Notifications\Notification::make()
            ->title('PDF Generation Started')
            ->body('Your report is being generated. You will be notified when it is ready.')
            ->info()
            ->send();
    }
    
    public function getProfiles()
    {
        return Profile::whereHas('sockets')
            ->where('user_id', Auth::user()->id)
            ->orderBy('name')
            ->get()
            ->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'socket_count' => $profile->sockets->count(),
                ];
            });
    }
    
    protected function getChartData()
    {
        switch ($this->reportType) {
            case 'energy_consumption':
            case 'cost_analysis':
                return [
                    'labels' => $this->reportData['labels'],
                    'datasets' => [
                        [
                            'label' => $this->reportData['datasets'][0]['label'],
                            'data' => $this->reportData['datasets'][0]['data'],
                            'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                            'borderColor' => 'rgb(59, 130, 246)',
                            'borderWidth' => 1
                        ]
                    ]
                ];
                
            case 'anomaly':
                if (isset($this->reportData['anomalies_by_date'])) {
                    return [
                        'labels' => $this->reportData['anomalies_by_date']['labels'],
                        'datasets' => [
                            [
                                'label' => 'Anomalies',
                                'data' => $this->reportData['anomalies_by_date']['data'],
                                'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                                'borderColor' => 'rgb(239, 68, 68)',
                                'borderWidth' => 1
                            ]
                        ]
                    ];
                }
                return [];
                
            case 'equipment_usage':
                if (isset($this->reportData['equipment_data'])) {
                    $profileNames = collect($this->reportData['equipment_data'])->pluck('profile_name')->toArray();
                    $uptimeData = collect($this->reportData['equipment_data'])->pluck('uptime_percentage')->toArray();
                    $downtimeData = collect($this->reportData['equipment_data'])->pluck('downtime_percentage')->toArray();
                    
                    return [
                        'labels' => $profileNames,
                        'datasets' => [
                            [
                                'label' => 'Uptime %',
                                'data' => $uptimeData,
                                'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                                'borderColor' => 'rgb(16, 185, 129)',
                                'borderWidth' => 1
                            ],
                            [
                                'label' => 'Downtime %',
                                'data' => $downtimeData,
                                'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                                'borderColor' => 'rgb(239, 68, 68)',
                                'borderWidth' => 1
                            ]
                        ]
                    ];
                }
                return [];
        }
        
        return [];
    }

    public function render()
    {
        return view('livewire.report-generator', [
            'profiles' => $this->getProfiles()
        ]);
    }
}