<?php

namespace App\Console\Commands;

use App\Services\PredictionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunPredictionAnalysis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-prediction-analysis {--time-window=24 : Hours of data to analyze}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyzes device readings to predict potential failures';

    /**
     * Execute the console command.
     */
    public function handle(PredictionService $predictionService)
    {
        $timeWindow = (int) $this->option('time-window');
        $this->info("Starting device failure prediction analysis (analyzing last {$timeWindow} hours of data)...");

        try {
            $startTime = microtime(true);
            $predictionsCount = $predictionService->runPredictionForAllActiveSockets($timeWindow);
            $duration = round(microtime(true) - $startTime, 2);

            $this->info("Prediction analysis completed: {$predictionsCount} potential issues detected.");
            $this->info("Analysis duration: {$duration} seconds");

            Log::info('Prediction analysis completed', [
                'predictions_count' => $predictionsCount,
                'duration_seconds' => $duration,
                'time_window_hours' => $timeWindow,
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error running prediction analysis: '.$e->getMessage());
            Log::error('Prediction analysis failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
