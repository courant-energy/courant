<?php

namespace App\Livewire;

use App\Models\Profile;
use App\Services\ReportService;
use App\Services\PdfReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Livewire\Component;

class ReportGenerator extends Component
{
    public string $reportType = 'energy_consumption';
    public string $timeRange = 'daily';
    public string $startDate;
    public string $endDate;
    public ?int $selectedProfileId = null;
    public float $costPerKwh = 0.12;
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

    public function generateReport()
    {
        $this->isLoading = true;
        $this->showReport = false;

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
        $pdfReportService = app(PdfReportService::class);
        
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate)->endOfDay();
        
        $pdf = $pdfReportService->generatePdf(
            $this->reportData,
            $this->reportType,
            $this->timeRange,
            $startDate,
            $endDate,
            $this->selectedProfileId
        );
        
        $timestamp = now()->format('Y-m-d_H-i');
        $filename = "report_{$this->reportType}_{$timestamp}.pdf";
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
    
    public function getProfiles()
    {
        return Profile::whereHas('sockets')
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