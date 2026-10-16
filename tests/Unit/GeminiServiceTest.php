<?php

use App\Services\GeminiService;

test('it redacts gemini api key from url query parameter', function () {
    $service = new GeminiService;
    $method = new ReflectionMethod($service, 'redactGeminiApiKeyInText');
    $method->setAccessible(true);

    $text = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=secret-key-value';
    $result = $method->invoke($service, $text, 'secret-key-value');

    expect($result)->toContain('key=[REDACTED]')
        ->not->toContain('secret-key-value');
});

test('it redacts gemini api key from exception messages', function () {
    $service = new GeminiService;
    $method = new ReflectionMethod($service, 'redactGeminiApiKeyInText');
    $method->setAccessible(true);

    $text = 'HTTP 400 for https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=secret-key-value';
    $result = $method->invoke($service, $text, 'secret-key-value');

    expect($result)->toContain('[REDACTED]')
        ->not->toContain('secret-key-value');
});
