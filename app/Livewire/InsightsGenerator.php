<?php

namespace App\Livewire;

use App\Services\GeminiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class InsightsGenerator extends Component
{
    public $isLoading = false;
    public $insights = [];
    public $error = null;
    public $generation_in_progress = false;
    public $cooldown_minutes_remaining = 0;
    public $max_attempts = 3; // Maximum attempts allowed within the time window
    public $decay_minutes = 60; // Time window in minutes

    protected $listeners = ['refreshInsights'];

    /**
     * Generate insights using the Gemini API with rate limiting
     */
    public function generateInsights()
    {
        // Get the current authenticated user
        $user = Auth::user();
        
        // Create a rate limiter key based on user ID or IP
        $key = 'generate-insights:' . ($user ? $user->id : request()->ip());
        
        // Check if the user has exceeded the rate limit
        if (RateLimiter::tooManyAttempts($key, $this->max_attempts)) {
            // Calculate remaining cooldown time
            $this->cooldown_minutes_remaining = ceil(RateLimiter::availableIn($key) / 60);
            
            $this->error = "You've reached the maximum number of insight generations. Please wait {$this->cooldown_minutes_remaining} minutes before trying again.";
            return;
        }
        
        $this->isLoading = true;
        $this->generation_in_progress = true;
        $this->error = null;
        $this->insights = [];
        
        try {
            // Increment the rate limiter counter for this user
            RateLimiter::hit($key, $this->decay_minutes * 60);
            
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
     * Get the number of attempts remaining for the current user
     */
    public function getRemainingAttemptsProperty()
    {
        $user = Auth::user();
        $key = 'generate-insights:' . ($user ? $user->id : request()->ip());
        return $this->max_attempts - RateLimiter::attempts($key);
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
        return view('livewire.insights-generator', [
            'remainingAttempts' => $this->getRemainingAttemptsProperty(),
        ]);
    }
}
