<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PdfReportService;
use Carbon\Carbon;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePdfReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportData;

    protected $reportType;

    protected $timeRange;

    protected $startDate;

    protected $endDate;

    protected $selectedProfileId;

    protected $userId;

    protected $companyName;

    /**
     * Create a new job instance.
     */
    public function __construct(
        array $reportData,
        string $reportType,
        string $timeRange,
        Carbon $startDate,
        Carbon $endDate,
        ?int $selectedProfileId,
        int $userId,
        ?string $companyName
    ) {
        $this->reportData = $reportData;
        $this->reportType = $reportType;
        $this->timeRange = $timeRange;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->selectedProfileId = $selectedProfileId;
        $this->userId = $userId;
        $this->companyName = $companyName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);

        try {
            $pdfReportService = app(PdfReportService::class);

            $pdf = $pdfReportService->generatePdf(
                $this->reportData,
                $this->reportType,
                $this->timeRange,
                $this->startDate,
                $this->endDate,
                $this->selectedProfileId,
                $this->companyName
            );

            $timestamp = now()->format('Y-m-d_H-i');
            $filename = "reports/{$this->reportType}_{$timestamp}.pdf";

            // Store the PDF file
            Storage::disk('public')->put($filename, $pdf->output());

            // Get the URL to the stored file
            $fileUrl = Storage::disk('public')->url($filename);

            // Send a notification to the user with the download link
            Notification::make()
                ->title('PDF Report Generated')
                ->body("Your {$this->reportType} report is ready to download.")
                ->actions([
                    \Filament\Notifications\Actions\Action::make('download')
                        ->label('Download PDF')
                        ->url($fileUrl, shouldOpenInNewTab: true)
                        ->icon('heroicon-o-document-arrow-down'),
                ])
                ->success()
                ->sendToDatabase($user);
        } catch (Exception $e) {
            // Log the error
            \Log::error('PDF generation failed: '.$e->getMessage(), [
                'userId' => $this->userId,
                'reportType' => $this->reportType,
                'exception' => $e,
            ]);

            // Send a notification about the failure
            Notification::make()
                ->title('PDF Generation Failed')
                ->body('There was an error while generating your PDF report. Please try again or contact support if the issue persists.')
                ->danger()
                ->sendToDatabase($user);
        }
    }
}
