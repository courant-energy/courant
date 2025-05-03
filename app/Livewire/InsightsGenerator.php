<?php

namespace App\Livewire;

use App\Services\GeminiService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class InsightsGenerator extends Component
{
    public $isLoading = false;
    public $insights = [];
    public $error = null;
    public $generation_in_progress = false;

    protected $listeners = ['refreshInsights'];

    /**
     * Generate insights using the Gemini API
     */
    public function generateInsights()
    {
        $this->isLoading = true;
        $this->generation_in_progress = true;
        $this->error = null;
        $this->insights = [];
        
        try {
            // Get the current authenticated user
            $user = Auth::user();
            
            // Call the GeminiService to generate insights
            $geminiService = app(GeminiService::class);
            $response = $geminiService->generateInsights($user, $this->isLoading);
            
            // Check if there was an error
            if (isset($response['error'])) {
                $this->error = $response['error'];
                $this->insights = [];
            } else {
                // Store the insights in the component state
                $this->insights = $response['insights'] ?? [];
            }
        } catch (\Exception $e) {
            $this->error = 'An error occurred while generating insights: ' . $e->getMessage();
        }
        
        $this->isLoading = false;
        $this->generation_in_progress = false;
    }

    /**
     * Refresh the insights (can be triggered by parent components)
     */
    public function refreshInsights()
    {
        if (!$this->generation_in_progress) {
            $this->generateInsights();
        }
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.insights-generator');
    }
}
