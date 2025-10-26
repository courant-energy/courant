<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::view('/', 'welcome')
    ->name('home');

Route::view('about', 'about')
    ->name('about');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('terms', 'terms')
    ->name('terms');

Route::view('privacy', 'privacy')
    ->name('privacy');

Route::view('contact', 'contact')
    ->name('contact');

Route::get('/whitepaper', function () {
    $markdownContent = file_get_contents(base_path('IMRAD COURANT.md'));
    
    // First, let's pre-process the markdown to handle reference-style images better
    // Convert reference links to inline images for better parsing
    $processedContent = preg_replace_callback(
        '/!\[\]\[([^\]]+)\]/',
        function ($matches) use ($markdownContent) {
            $refId = $matches[1];
            // Find the reference definition
            if (preg_match('/\[' . preg_quote($refId) . '\]:\s*<([^>]+)>/', $markdownContent, $refMatch)) {
                return '![Image](' . $refMatch[1] . ')';
            }
            return $matches[0];
        },
        $markdownContent
    );
    
    // Process the markdown content to HTML
    $htmlContent = Str::markdown($processedContent);
    
    // Enhance image tags with proper styling and loading attributes
    $htmlContent = preg_replace(
        '/(<img[^>]*src="data:image\/[^"]+")([^>]*>)/',
        '$1 loading="lazy" style="max-width: 100%; height: auto; margin: 2rem auto; display: block; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"$2',
        $htmlContent
    );
    
    // Clean up any leftover reference definitions that might still be showing
    $htmlContent = preg_replace('/\[([^\]]+)\]:\s*&lt;[^&]+&gt;/', '', $htmlContent);
    
    return view('whitepaper', [
        'content' => $htmlContent,
        'title' => 'IMRAD: Courant Research White Paper'
    ]);
})->name('whitepaper');

Route::get('login', function () {
    return redirect()->route('filament.app.auth.login');
})->name('login');

// Display server time
Route::get('server-time', function () {
    return response()->json(['time' => now()->toDateTimeString()]);
})->name('server.time');

// require __DIR__.'/auth.php';
