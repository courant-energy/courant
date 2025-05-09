<?php

namespace App\Services;

use App\Models\Reading;
use App\Models\Profile;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class PdfReportService
{
    /**
     * Generate PDF for a report
     *
     * @param array $reportData
     * @param string $reportType
     * @param string $timeRange
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $profileId
     * @param string|null $companyName
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePdf(array $reportData, string $reportType, string $timeRange, Carbon $startDate, Carbon $endDate, ?int $profileId = null, ?string $companyName = null): \Barryvdh\DomPDF\PDF
    {
        $title = $this->getReportTitle($reportType, $timeRange);
        $profileName = 'All Profiles';
        \Log::info($reportData);
        
        if ($profileId) {
            $profile = Profile::find($profileId);
            $profileName = $profile ? $profile->name : 'Unknown Profile';
        }
        
        $reportPeriod = $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
        
        // Generate chart image if applicable
        $chartImageData = null;
        
        // Select the appropriate view based on report type
        $viewName = match($reportType) {
            'energy_consumption' => 'reports.pdf.energy-consumption',
            'cost_analysis' => 'reports.pdf.cost-analysis',
            'equipment_usage' => 'reports.pdf.equipment-usage',
            'anomaly' => 'reports.pdf.anomaly',
            default => 'reports.pdf.generic'
        };
        
        // Generate PDF
        $pdf = PDF::loadView($viewName, [
            'reportData' => $reportData,
            'title' => $title,
            'companyName' => $companyName,
            'profileName' => $profileName,
            'reportPeriod' => $reportPeriod,
            'generatedAt' => now()->format('M d, Y H:i'),
            'chartImageData' => $chartImageData,
        ]);
        
        // Set paper size and orientation
        $pdf->setPaper('a4');
        
        return $pdf;
    }
    
    /**
     * Get report title based on type and time range
     *
     * @param string $reportType
     * @param string $timeRange
     * @return string
     */
    protected function getReportTitle(string $reportType, string $timeRange): string
    {
        $typeTitles = [
            'energy_consumption' => 'Energy Consumption Report',
            'cost_analysis' => 'Cost Analysis Report',
            'equipment_usage' => 'Equipment Usage Report',
            'anomaly' => 'Anomaly Report',
        ];
        
        $timeRangeTitles = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
        ];
        
        $title = $typeTitles[$reportType] ?? 'Report';
        
        if (isset($timeRangeTitles[$timeRange]) && $reportType != 'equipment_usage' && $reportType != 'anomaly') {
            $title = $timeRangeTitles[$timeRange] . ' ' . $title;
        }
        
        return $title;
    }
}