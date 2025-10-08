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

    @include('components.site-settings')

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900 bg-white">
    <!-- Navigation -->
    <nav class="container px-6 py-4 mx-auto">
        <div class="flex items-center justify-between">
            <div>
                <a href="/" class="flex items-center">
                    <img src="/logo.svg" alt="Courant Logo" class="w-auto h-8">
                    {{-- <span class="ml-3 text-xl font-bold">Courant</span> --}}
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

    <!-- Hero Section -->
    <section class="py-20 bg-white hero-wrapper">
        <div class="container px-6 mx-auto">
            <div class="flex flex-col items-center md:flex-row">

                <!-- Hero Text -->
                <div class="md:w-1/2 md:pr-10">

                    <div class="flex flex-col hero-heading">
                        <h1 class="mb-3 text-5xl font-bold leading-tight text-center md:text-7xl md:mb-6 md:text-left">
                            Smarter Energy, Smarter Business</h1>
                        <h1 class="mb-8 text-2xl font-bold leading-tight text-center md:text-3xl md:mb-6 md:text-left">
                            For MSMEs</h1>
                    </div>

                    <div class="flex flex-col space-y-3 hero-subtext sm:space-y-0">
                        <p class="mb-8 text-lg text-center text-gray-600 md:text-left">We simplified energy management
                            so you can focus on what matters: running your business.</p>
                        <div class="flex flex-col items-center justify-center gap-4 md:flex-row md:justify-start">
                            <a href="{{ route('filament.app.auth.register') }}"
                                class="w-full px-8 py-3 font-medium text-center text-white transition bg-black rounded-sm md:w-auto hover:bg-gray-800">Get
                                Started</a>
                            <a href="#features"
                                class="w-full px-8 py-3 font-medium text-center text-black transition border border-gray-200 rounded-sm md:w-auto hover:bg-gray-100">Learn
                                More</a>
                        </div>
                    </div>
                </div>
                <div class="w-full mt-10 md:w-1/2 md:mt-0">
                    <div class="relative h-full">
                        <!-- Placeholder for an illustration or screenshot -->
                        <div
                            class="flex items-center justify-center overflow-hidden rounded-md mockups-container aspect-h-1 aspect-w-1 md:aspect-h-9 md:aspect-w-16 bg-gray-50">
                            <livewire:hero-animation />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="pt-20 bg-white">
        <div class="container mx-auto border-l border-r border-gray-200">
            <h2 class="mb-16 text-5xl font-bold text-center section-header">Why Choose Courant</h2>
            <livewire:feature-card>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="bg-white border border-gray-200">
        <div class="container px-6 pt-20 mx-auto border-l border-r border-gray-200">
            <div class="flex flex-col items-center gap-y-10">
                <div class="w-full items-left justify-left md:pr-6">
                    <h2 class="mb-6 text-5xl font-bold section-header">Our Mission</h2>
                    <p class="mb-8 text-lg text-gray-600 md:w-2/3">At Courant, we're committed to democratizing energy
                        intelligence. Our mission is to provide accessible, smart solutions that empower households and
                        businesses to take control of their energy consumption while contributing to a more sustainable
                        future.</p>
                    <div class="pl-4 italic border-l-4 border-black">
                        <p class="text-gray-800">"Creating an energy-conscious world through innovation and
                            accessibility."</p>
                    </div>
                </div>
                <div class="w-full px-3 mt-10 md:mt-0">
                    <livewire:mission-grid>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision Section -->
    <section class="bg-white border border-gray-200">
        <div class="container py-20 mx-auto border-l border-r border-gray-200">
            <div class="flex flex-col items-center gap-y-5">
                <div class="w-full px-6 items-left justify-left md:pr-6">
                    <h2 class="mb-6 text-5xl font-bold section-header">Our Vision</h2>
                    <p class="max-w-3xl mb-3 text-lg text-gray-600">We envision a world where energy management is
                        seamless, efficient, and sustainable. Where every kilowatt is optimized, and renewable energy is
                        the standard.</p>
                </div>
                <livewire:vision-grid>
            </div>
        </div>
    </section>

    <!-- Our Team Section -->
    <section
        class="relative overflow-hidden border border-gray-200 bg-gradient-to-br from-gray-50 via-white to-gray-100">
        <!-- Background decorative elements -->
        <div
            class="absolute top-0 right-0 transform translate-x-24 -translate-y-24 bg-black rounded-full w-72 h-72 opacity-5">
        </div>
        <div
            class="absolute bottom-0 left-0 transform -translate-x-32 translate-y-32 rounded-full bg-gray-900/5 w-96 h-96 opacity-">
        </div>

        <div class="container relative py-20 mx-auto border-l border-r border-gray-200">
            <div class="flex flex-col items-center gap-y-12">
                <!-- Header Section -->
                <div class="w-full px-6 text-center">
                    <div
                        class="inline-block px-4 py-2 mb-4 text-sm font-semibold text-gray-700 bg-gray-200 rounded-full">
                        Meet the Innovators
                    </div>
                    <h2
                        class="mb-6 text-5xl font-bold text-transparent bg-gradient-to-r from-gray-900 via-black to-gray-800 bg-clip-text section-header">
                        Our Team
                    </h2>
                    <div class="max-w-4xl mx-auto">
                        <p class="mb-6 text-xl leading-relaxed text-gray-700">
                            We are a dedicated team of <span class="font-semibold text-black">BS Computer Science
                                students</span>, leveraging a diverse skill set to drive this project forward.
                        </p>
                        <p class="text-lg text-gray-600">
                            Our collective expertise spans <span class="font-medium">full-stack development</span>,
                            <span class="font-medium">design and branding</span>,
                            <span class="font-medium">strategic research</span>,
                            <span class="font-medium">project management</span>, and
                            <span class="font-medium">innovative ideation</span>, ensuring strong synergy and successful
                            execution.
                        </p>
                    </div>
                </div>

                <!-- Team Image Section -->
                <div class="w-full px-6">
                    <div class="relative group">
                        <!-- Main image container with enhanced styling -->
                        <div
                            class="relative overflow-hidden rounded-2xl max-w-5xl mx-auto shadow-2xl transform transition-all duration-300 aspect-video group-hover:scale-[1.02] group-hover:shadow-3xl">
                            <img src="{{ asset('images/team.jpg') }}" alt="Our Team - Courant Energy Solutions"
                                class="object-cover object-center w-full mx-auto aspect-video rounded-2xl">

                            <!-- Gradient overlay for better text readability if needed -->
                            <div
                                class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-black/20 via-transparent to-transparent group-hover:opacity-100">
                            </div>

                            <!-- Decorative corner accent -->
                            <div
                                class="absolute top-0 left-0 w-20 h-20 transform rotate-45 -translate-x-10 -translate-y-10 bg-black opacity-10">
                            </div>
                        </div>

                        <!-- Team stats/highlights -->
                        <div class="grid grid-cols-2 gap-6 mt-8 md:grid-cols-4">
                            <div class="text-center">
                                <div
                                    class="flex items-center justify-center w-12 h-12 mx-auto mb-3 bg-black rounded-full">
                                
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="text-white size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 13.5V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m12-3V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m-6-9V3.75m0 3.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 9.75V10.5" />
                                    </svg>

                                </div>
                                <h3 class="font-semibold text-gray-900">Management</h3>
                                <p class="text-sm text-gray-600">Project Leadership</p>
                            </div>
                            <div class="text-center">
                                <div
                                    class="flex items-center justify-center w-12 h-12 mx-auto mb-3 bg-black rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="font-semibold text-gray-900">Research</h3>
                                <p class="text-sm text-gray-600">Strategic Analysis</p>
                            </div>
                            <div class="text-center">
                                <div
                                    class="flex items-center justify-center w-12 h-12 mx-auto mb-3 bg-black rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                    </svg>
                                </div>
                                <h3 class="font-semibold text-gray-900">Development</h3>
                                <p class="text-sm text-gray-600">Full-Stack Expertise</p>
                            </div>
                            <div class="text-center">
                                <div
                                    class="flex items-center justify-center w-12 h-12 mx-auto mb-3 bg-black rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h4a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v6a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="font-semibold text-gray-900">Design</h3>
                                <p class="text-sm text-gray-600">Brand & UX</p>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 text-white bg-black">
        <div class="container px-6 mx-auto text-center">
            <h2 class="mb-6 text-3xl font-bold md:text-4xl">Ready to Transform Your Energy Usage?</h2>
            <p class="max-w-2xl mx-auto mb-10 text-lg opacity-80">Join thousands of homes and businesses already saving
                money and reducing their carbon footprint with Courant's intelligent energy solutions.</p>

            <div class="flex flex-col justify-center space-y-4 sm:flex-row sm:space-y-0 sm:space-x-6">
                <a href="{{ route('filament.app.auth.register') }}"
                    class="px-8 py-4 font-semibold text-black transition bg-white rounded-sm hover:bg-gray-100">Start
                    Your Free Trial</a>
                <a href="#"
                    class="px-8 py-4 font-semibold text-white transition border border-white rounded-sm hover:bg-white hover:text-black">Schedule
                    a Demo</a>
            </div>

            <div class="grid grid-cols-2 gap-8 mt-16 md:grid-cols-4">
                <div class="flex flex-col items-center">
                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                    <span>Secure Data</span>
                </div>
                <div class="flex flex-col items-center">
                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <span>Expert Support</span>
                </div>
                <div class="flex flex-col items-center">
                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                    <span>Money Back Guarantee</span>
                </div>
                <div class="flex flex-col items-center">
                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                        </path>
                    </svg>
                    <span>Trusted by Industry</span>
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
                        <a href="#" class="text-gray-600 hover:text-gray-900">Privacy</a>
                        <a href="#" class="text-gray-600 hover:text-gray-900">Terms</a>
                        <a href="#" class="text-gray-600 hover:text-gray-900">Contact</a>
                    </div>

                    <!-- Social Media Links -->
                    @if(setting('site_social'))
                        <div class="flex flex-col space-y-4">
                            @foreach(setting('site_social') as $social)
                                <a href="{{ $social['link'] }}" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="text-gray-600 hover:text-gray-900 transition-colors"
                                   title="{{ ucfirst($social['vendor']) }}">
                                    <span class="sr-only">{{ ucfirst($social['vendor']) }}</span>
                                    <!-- You can add specific icons based on vendor here -->
                                    {{ $social['vendor']  }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                    
                    
                </div>
            </div>
        </div>
    </footer>
</body>

</html>
