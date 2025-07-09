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
        <section class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="flex flex-col md:flex-row items-center">
                    <div class="md:w-1/2 md:pr-10">
                        <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-6">Smart Energy Solutions for Tomorrow</h1>
                        <p class="text-lg text-gray-600 mb-8">Optimize your energy consumption and reduce costs with our intelligent energy management platform.</p>
                        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                            <a href="{{ route('filament.app.auth.register') }}" class="px-8 py-3 bg-black text-white font-medium rounded-md text-center hover:bg-gray-800 transition">Get Started</a>
                            <a href="#features" class="px-8 py-3 border border-black text-black font-medium rounded-md text-center hover:bg-gray-100 transition">Learn More</a>
                        </div>
                    </div>
                    <div class="md:w-1/2 mt-10 md:mt-0">
                        <div class="bg-black rounded-lg p-10">
                            <!-- Placeholder for an illustration or screenshot -->
                            <div class="aspect-w-16 aspect-h-9 flex items-center justify-center">
                                <img src="{{ asset('images/hero.jpg') }}" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-gray-50">
            <div class="container mx-auto px-6">
                <h2 class="text-3xl font-bold text-center mb-16">Why Choose Courant</h2>
                
                <div class="grid md:grid-cols-3 gap-12">
                    <!-- Feature 1 -->
                    <div class="flex flex-col items-start">
                        <div class="bg-black rounded-full p-3 mb-6">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Real-time Monitoring</h3>
                        <p class="text-gray-600">Track energy consumption patterns in real-time and make informed decisions.</p>
                    </div>
                    
                    <!-- Feature 2 -->
                    <div class="flex flex-col items-start">
                        <div class="bg-black rounded-full p-3 mb-6">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Cost Optimization</h3>
                        <p class="text-gray-600">Reduce energy bills by up to 30% with our intelligent optimization algorithms.</p>
                    </div>
                    
                    <!-- Feature 3 -->
                    <div class="flex flex-col items-start">
                        <div class="bg-black rounded-full p-3 mb-6">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Sustainability Impact</h3>
                        <p class="text-gray-600">Track and report on your carbon footprint reduction and environmental impact.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission Section -->
        <section class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="flex flex-col md:flex-row items-center">
                    <div class="md:w-1/2 md:pr-10">
                        <h2 class="text-3xl font-bold mb-6">Our Mission</h2>
                        <p class="text-lg text-gray-600 mb-8">At Courant, we're committed to democratizing energy intelligence. Our mission is to provide accessible, smart solutions that empower households and businesses to take control of their energy consumption while contributing to a more sustainable future.</p>
                        <div class="border-l-4 border-black pl-4 italic">
                            <p class="text-gray-800">"Creating an energy-conscious world through innovation and accessibility."</p>
                        </div>
                    </div>
                    <div class="md:w-1/2 mt-10 md:mt-0">
                        <div class="bg-gray-50 rounded-lg p-10">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-black rounded-md p-6 text-white text-center">
                                    <span class="block text-3xl font-bold mb-2">30%</span>
                                    <span class="text-sm">Average Energy Savings</span>
                                </div>
                                <div class="bg-black rounded-md p-6 text-white text-center">
                                    <span class="block text-3xl font-bold mb-2">10k+</span>
                                    <span class="text-sm">Active Users</span>
                                </div>
                                <div class="bg-black rounded-md p-6 text-white text-center">
                                    <span class="block text-3xl font-bold mb-2">24/7</span>
                                    <span class="text-sm">Monitoring</span>
                                </div>
                                <div class="bg-black rounded-md p-6 text-white text-center">
                                    <span class="block text-3xl font-bold mb-2">100%</span>
                                    <span class="text-sm">Renewable Focus</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Vision Section -->
        <section class="py-20 bg-gray-50">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-3xl font-bold mb-6">Our Vision</h2>
                <p class="text-lg text-gray-600 mb-12 max-w-3xl mx-auto">We envision a world where energy management is seamless, efficient, and sustainable. Where every kilowatt is optimized, and renewable energy is the standard.</p>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white p-8 rounded-lg shadow-sm">
                        <div class="bg-black rounded-full p-3 inline-block mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Global Impact</h3>
                        <p class="text-gray-600">Creating solutions that scale across communities and continents.</p>
                    </div>
                    <div class="bg-white p-8 rounded-lg shadow-sm">
                        <div class="bg-black rounded-full p-3 inline-block mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Innovation First</h3>
                        <p class="text-gray-600">Pioneering technologies that make energy management intuitive.</p>
                    </div>
                    <div class="bg-white p-8 rounded-lg shadow-sm">
                        <div class="bg-black rounded-full p-3 inline-block mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h.5A2.5 2.5 0 0020 5.5v-1.5"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Sustainable Future</h3>
                        <p class="text-gray-600">Building tomorrow's infrastructure for carbon-neutral living.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-24 bg-black text-white">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Transform Your Energy Usage?</h2>
                <p class="text-lg opacity-80 mb-10 max-w-2xl mx-auto">Join thousands of homes and businesses already saving money and reducing their carbon footprint with Courant's intelligent energy solutions.</p>
                
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                    <a href="{{ route('filament.app.auth.register') }}" class="px-8 py-4 bg-white text-black font-semibold rounded-md hover:bg-gray-100 transition">Start Your Free Trial</a>
                    <a href="#" class="px-8 py-4 border border-white text-white font-semibold rounded-md hover:bg-white hover:text-black transition">Schedule a Demo</a>
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
