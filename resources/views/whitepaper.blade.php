<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>White Paper - Courant Energy Solutions</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @include('components.site-settings')

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900 bg-white overflow-x-hidden">
    <!-- Navigation -->
    <nav class="container px-6 py-4 mx-auto">
        <div class="flex items-center justify-between">
            <div>
                <a href="/" class="flex items-center">
                    <img src="/logo.svg" alt="Courant Logo" class="w-auto h-8">
                </a>
            </div>
            <div class="flex items-center space-x-4">
                @auth
                    <a href="{{ route('filament.app.pages.dashboard') }}"
                        class="px-4 py-2 text-sm hover:text-gray-600">Dashboard</a>
                    <form method="POST" action="{{ route('filament.app.auth.logout') }}">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-black rounded-md hover:bg-gray-800">Logout</button>
                    </form>
                @else
                    <a href="{{ route('filament.app.auth.login') }}" class="px-4 py-2 text-sm hover:text-gray-600">Login</a>
                    <a href="{{ route('filament.app.auth.register') }}"
                        class="px-4 py-2 text-sm text-white bg-black rounded-md hover:bg-gray-800">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <section class="py-12 bg-white border-b">
        <div class="container px-6 mx-auto">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <a href="{{ route('about') }}" class="flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to About
                    </a>
                    <div class="text-sm text-gray-500">
                        Research Paper
                    </div>
                </div>
                <h1 class="text-4xl font-bold text-center mb-4">Technical White Paper</h1>
                <p class="text-xl text-gray-600 text-center">Complete research documentation for the Courant energy management system</p>
            </div>
        </div>
    </section>

    <!-- White Paper Content -->
    <section class="py-16 bg-white">
        <div class="container px-6 mx-auto">
            <div class="max-w-4xl mx-auto">
                <div class="prose prose-lg max-w-none">
                    <div class="bg-gray-50 p-6 rounded-lg mb-8">
                        <div class="flex items-center mb-4 mt-5">
                            <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 m-0">Document Information</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Document Type:</span>
                                <span class="text-gray-600 ml-2">IMRAD Research Paper</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Research Focus:</span>
                                <span class="text-gray-600 ml-2">IoT & Predictive Maintenance</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Target Audience:</span>
                                <span class="text-gray-600 ml-2">MSMEs & Technical Professionals</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Methodology:</span>
                                <span class="text-gray-600 ml-2">Rapid Application Development</span>
                            </div>
                        </div>
                    </div>

                    <!-- Render the markdown content -->
                    <div class="whitepaper-content">
                        {!! $content !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Actions -->
    <section class="py-12 bg-gray-50 border-t">
        <div class="container px-6 mx-auto">
            <div class="max-w-4xl mx-auto text-center">
                <h3 class="text-2xl font-bold mb-4">Interested in Learning More?</h3>
                <p class="text-gray-600 mb-6">Get in touch with our research team or try our system today.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('contact') }}" 
                       class="px-6 py-3 bg-black text-white rounded-md hover:bg-gray-800 transition-colors">
                        Contact Research Team
                    </a>
                    <a href="{{ route('filament.app.auth.register') }}" 
                       class="px-6 py-3 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                        Try the System
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-10 bg-white border-t border-gray-200">
        <div class="container px-6 mx-auto">
            <div class="flex flex-col md:items-center justify-between md:flex-row">
                <div class="mb-6 md:mb-0">
                    <img src="/logo.svg" alt="Courant Logo" class="w-auto h-8">
                    <p class="mt-2 text-sm text-gray-600">© 2025 Courant Energy Solutions. All rights reserved.</p>
                </div>

                <div class="flex flex-col gap-4 md:flex-row">
                    <!-- Navigation Links -->
                    <div class="flex flex-col space-y-4">
                        <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900">Home</a>
                        <a href="{{ route('about') }}" class="text-gray-600 hover:text-gray-900">About</a>
                        <a href="{{ route('privacy') }}" class="text-gray-600 hover:text-gray-900">Privacy</a>
                        <a href="{{ route('terms') }}" class="text-gray-600 hover:text-gray-900">Terms</a>
                        <a href="{{ route('contact') }}" class="text-gray-600 hover:text-gray-900">Contact</a>
                    </div>

                    <!-- Social Media Links -->
                    @if (setting('site_social'))
                        <div class="flex flex-col space-y-4">
                            @foreach (setting('site_social') as $social)
                                <a href="{{ $social['link'] }}" target="_blank" rel="noopener noreferrer"
                                    class="text-gray-600 hover:text-gray-900 transition-colors"
                                    title="{{ ucfirst($social['vendor']) }}">
                                    <span class="sr-only">{{ ucfirst($social['vendor']) }}</span>
                                    {{ $social['vendor'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </footer>

    <style>
        /* Custom styles for the white paper content */
        .whitepaper-content h1 {
            @apply text-3xl font-bold mt-12 mb-6 text-gray-900 border-b border-gray-200 pb-4;
        }
        
        .whitepaper-content h2 {
            @apply text-2xl font-bold mt-10 mb-4 text-gray-900;
        }
        
        .whitepaper-content h3 {
            @apply text-xl font-semibold mt-8 mb-3 text-gray-800;
        }
        
        .whitepaper-content p {
            @apply text-gray-700 leading-relaxed mb-4;
        }
        
        .whitepaper-content ul, .whitepaper-content ol {
            @apply ml-6 mb-4;
        }
        
        .whitepaper-content li {
            @apply mb-2 text-gray-700;
        }
        
        .whitepaper-content strong {
            @apply font-semibold text-gray-900;
        }
        
        .whitepaper-content blockquote {
            @apply border-l-4 border-gray-300 pl-4 italic text-gray-600 my-6;
        }
        
        .whitepaper-content img {
            @apply max-w-full h-auto mx-auto rounded-lg shadow-lg my-8;
            display: block;
            max-height: 600px;
            object-fit: contain;
        }
        
        .whitepaper-content table {
            @apply w-full border-collapse border border-gray-300 my-6;
        }
        
        .whitepaper-content th, .whitepaper-content td {
            @apply border border-gray-300 px-4 py-2 text-left;
        }
        
        .whitepaper-content th {
            @apply bg-gray-100 font-semibold;
        }
    </style>
</body>

</html>