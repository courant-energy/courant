<?php

use Illuminate\Support\Facades\Route;

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

Route::get('whitepaper', function () {
    $markdownContent = file_get_contents(base_path('IMRAD COURANT.md'));
    return view('whitepaper', compact('markdownContent'));
})->name('whitepaper');

Route::get('login', function () {
    return redirect()->route('filament.app.auth.login');
})->name('login');

// Display server time
Route::get('server-time', function () {
    return response()->json(['time' => now()->toDateTimeString()]);
})->name('server.time');

// require __DIR__.'/auth.php';
