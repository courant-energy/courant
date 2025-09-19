<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Courant Energy Solutions</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        
        <!-- Favicon -->
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-white text-gray-900">
        <!-- Navigation -->
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <a href="/" class="flex items-center">
                        <img src="/logo.svg" alt="Courant Logo" class="h-8 w-auto">
                        {{-- <span class="ml-3 text-xl font-bold">Courant</span> --}}
                    </a>
                </div>
                <div class="flex items-center space-x-4">

                    @auth
                        <a href="{{ route('filament.app.pages.dashboard') }}" class="px-4 py-2 text-sm hover:text-gray-600">Dashboard</a>
                        <form method="POST" action="{{ route('filament.app.auth.logout') }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-sm bg-black text-white rounded-md hover:bg-gray-800">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('filament.app.auth.login') }}" class="px-4 py-2 text-sm hover:text-gray-600">Login</a>
                        <a href="{{ route('filament.app.auth.register') }}" class="px-4 py-2 text-sm bg-black text-white rounded-md hover:bg-gray-800">Register</a>
                    @endauth

                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="py-20 bg-white hero-wrapper">
            <div class="container mx-auto px-6">
                <div class="flex flex-col md:flex-row items-center">

                    <!-- Hero Text -->
                    <div class="md:w-1/2 md:pr-10">

                        <div class="flex flex-col hero-heading">
                        <h1 class="text-5xl md:text-7xl font-bold leading-tight mb-3 md:mb-6 text-center md:text-left">Smarter Energy, Smarter Business</h1>
                        <h1 class="text-2xl md:text-3xl font-bold leading-tight mb-8 md:mb-6 text-center md:text-left">For MSMEs</h1>
                        </div>

                        <div class="hero-subtext flex flex-col space-y-3 sm:space-y-0">
                            <p class="text-lg text-gray-600 mb-8 text-center md:text-left">We simplified energy management so you can focus on what matters: running your business.</p>
                            <div class="flex flex-col md:flex-row gap-4 items-center justify-center md:justify-start">
                                <a href="{{ route('filament.app.auth.register') }}" class="w-full md:w-auto px-8 py-3 bg-black text-white font-medium rounded-sm text-center hover:bg-gray-800 transition">Get Started</a>
                                <a href="#features" class="w-full md:w-auto px-8 py-3 border border-gray-200 text-black font-medium rounded-sm text-center hover:bg-gray-100 transition">Learn More</a>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-1/2 mt-10 md:mt-0">
                        <div class="relative h-full">
                            <!-- Placeholder for an illustration or screenshot -->
                            <div class="mockups-container overflow-hidden aspect-h-1 aspect-w-1 md:aspect-h-9 md:aspect-w-16 flex items-center justify-center rounded-md bg-gray-50">
                                <livewire:hero-animation/>   
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="pt-20 bg-white">
            <div class="container mx-auto pb-10 border-l border-r border-gray-200">
                <h2 class="text-5xl section-header font-bold text-center mb-16">Why Choose Courant</h2>
                <livewire:feature-card>
            </div>
        </section>

        <!-- Mission Section -->
        <section class="bg-white border border-gray-200">
            <div class="container mx-auto px-6 pt-20 border-l border-r border-gray-200">
                <div class="flex flex-col items-center gap-y-10">
                    <div class="w-full items-left justify-left md:pr-6">
                        <h2 class="text-5xl section-header font-bold mb-6">Our Mission</h2>
                        <p class="text-lg text-gray-600 mb-8 md:w-2/3">At Courant, we're committed to democratizing energy intelligence. Our mission is to provide accessible, smart solutions that empower households and businesses to take control of their energy consumption while contributing to a more sustainable future.</p>
                        <div class="border-l-4 border-black pl-4 italic">
                            <p class="text-gray-800">"Creating an energy-conscious world through innovation and accessibility."</p>
                        </div>
                    </div>
                    <div class="w-full mt-10 md:mt-0 px-3">
                        <livewire:mission-grid>
                    </div>
                </div>
            </div>
        </section>

        <!-- Vision Section -->
        <section class="bg-white border border-gray-200">
            <div class="container py-20 mx-auto border-l border-r border-gray-200">
                <div class="flex flex-col items-center gap-y-5">
                    <div class="w-full items-left justify-left md:pr-6 px-6">
                        <h2 class="text-5xl section-header font-bold mb-6">Our Vision</h2>
                        <p class="text-lg text-gray-600 mb-3 max-w-3xl">We envision a world where energy management is seamless, efficient, and sustainable. Where every kilowatt is optimized, and renewable energy is the standard.</p>
                    </div>
                    <livewire:vision-grid>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-24 bg-black text-white">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Transform Your Energy Usage?</h2>
                <p class="text-lg opacity-80 mb-10 max-w-2xl mx-auto">Join thousands of homes and businesses already saving money and reducing their carbon footprint with Courant's intelligent energy solutions.</p>
                
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                    <a href="{{ route('filament.app.auth.register') }}" class="px-8 py-4 bg-white text-black font-semibold rounded-sm hover:bg-gray-100 transition">Start Your Free Trial</a>
                    <a href="#" class="px-8 py-4 border border-white text-white font-semibold rounded-sm hover:bg-white hover:text-black transition">Schedule a Demo</a>
                </div>
                
                <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="flex flex-col items-center">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span>Secure Data</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Expert Support</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Money Back Guarantee</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span>Trusted by Industry</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-10 bg-white border-t border-gray-200">
            <div class="container mx-auto px-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-6 md:mb-0">
                        <img src="/logo.svg" alt="Courant Logo" class="h-8 w-auto">
                        <p class="text-sm text-gray-600 mt-2">© 2025 Courant Energy Solutions. All rights reserved.</p>
                    </div>
                    <div class="flex space-x-6">
                        <a href="#" class="text-gray-600 hover:text-gray-900">Privacy</a>
                        <a href="#" class="text-gray-600 hover:text-gray-900">Terms</a>
                        <a href="#" class="text-gray-600 hover:text-gray-900">Contact</a>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
