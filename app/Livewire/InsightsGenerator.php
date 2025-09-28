<?php

namespace App\Livewire;

use App\Services\GeminiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class InsightsGenerator extends Component
{
    public $isLoading = false;
    public $insights = [];
    public $error = null;
    public $generation_in_progress = false;
    public $cooldown_minutes_remaining = 0;
    public $max_attempts = 5; // Maximum attempts allowed within the time window
    public $decay_minutes = 20; // Time window in minutes
    public $cache_duration_hours = 24; // Cache insights for 24 hours
    public $hasFreshData = false; // Track if data was just generated
    public $lastUpdated = null; // Track when insights were last updated

    protected $listeners = ['refreshInsights'];
    
    // Reactive properties to ensure Livewire tracks changes
    protected $rules = [];
    
    // Properties that should trigger re-render when changed
    public function updatedInsights()
    {
        // This will trigger when insights array changes
    }
    
    public function updatedError()
    {
        // This will trigger when error state changes
    }
    
    public function updatedIsLoading()
    {
        // This will trigger when loading state changes
    }
    
    public function updatedHasFreshData()
    {
        // This will trigger when fresh data flag changes
    }
    
    /**
     * Force refresh the component
     */
    public function forceRefresh()
    {
        $this->loadCachedInsights();
    }

    /**
     * Load cached insights when component mounts
     */
    public function mount()
    {
        $this->loadCachedInsights();
    }

    /**
     * Get cache key for current user
     */
    private function getCacheKey()
    {
        $user = Auth::user();
        return 'insights:' . ($user ? $user->id : request()->ip());
    }

    /**
     * Load cached insights if available
     */
    private function loadCachedInsights()
    {
        $cacheKey = $this->getCacheKey();
        $cachedData = Cache::get($cacheKey);
        
        if ($cachedData) {
            $this->insights = $cachedData['insights'] ?? [];
            $this->error = $cachedData['error'] ?? null;
            $this->hasFreshData = false; // This is cached data
            $this->lastUpdated = $cachedData['generated_at'] ?? null;
        }
    }

    /**
     * Cache the insights data
     */
    private function cacheInsights()
    {
        $cacheKey = $this->getCacheKey();
        $dataToCache = [
            'insights' => $this->insights,
            'error' => $this->error,
            'generated_at' => now()->toDateTimeString(),
        ];
        
        Cache::put($cacheKey, $dataToCache, now()->addHours($this->cache_duration_hours));
    }

    /**
     * Clear cached insights for current user
     */
    public function clearCache()
    {
        $cacheKey = $this->getCacheKey();
        Cache::forget($cacheKey);
        $this->insights = [];
        $this->error = null;
        $this->hasFreshData = false;
        $this->lastUpdated = null;
    }

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
        $this->hasFreshData = false;
        
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
                $this->hasFreshData = false;
            } else {
                // Store the insights in the component state
                $this->insights = $response['insights'] ?? [];
                $this->error = null; // Clear any previous errors
                $this->hasFreshData = true; // Mark as fresh data
                $this->lastUpdated = now()->toDateTimeString();
            }
            
            // Cache the results
            $this->cacheInsights();
            
        } catch (\Exception $e) {
            $this->error = 'An error occurred while generating insights: ' . $e->getMessage();
            $this->insights = []; // Clear insights on error
            $this->hasFreshData = false;
            // Cache the error as well
            $this->cacheInsights();
        } finally {
            // Always ensure loading states are reset
            $this->isLoading = false;
            $this->generation_in_progress = false;
        }
        
        // Dispatch a browser event to ensure UI updates
        $this->dispatch('insights-updated', ['hasInsights' => count($this->insights) > 0]);
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
     * Check if insights can be generated (not rate limited and not currently loading)
     */
    public function getCanGenerateInsightsProperty()
    {
        $user = Auth::user();
        $key = 'generate-insights:' . ($user ? $user->id : request()->ip());
        
        return !$this->isLoading && 
               !$this->generation_in_progress && 
               !RateLimiter::tooManyAttempts($key, $this->max_attempts);
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
            'canGenerateInsights' => $this->getCanGenerateInsightsProperty(),
            'lastUpdated' => $this->lastUpdated,
        ]);
    }
}
