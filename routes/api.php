<?php

use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/data',function (Request $request) {
    $service = new GeminiService();
    $data = $service->exportAllData(user:User::find(1));
    return response()->json(json_decode($data));
});
