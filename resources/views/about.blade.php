<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>About Us - Courant Energy Solutions</title>

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
    <section class="py-20 bg-white">
        <div class="container px-6 mx-auto">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="mb-6 text-5xl font-bold leading-tight md:text-6xl">About Courant</h1>
                <p class="mb-8 text-xl text-gray-600">Revolutionizing Power Management for MSMEs with IoT Integration and Predictive Maintenance</p>
            </div>
        </div>
    </section>

    <!-- Video Section -->
    <section class="py-16 bg-gray-50">
        <div class="container px-6 mx-auto">
            <div class="max-w-4xl mx-auto">
                <h2 class="mb-8 text-3xl font-bold text-center">Project Overview</h2>
                <div class="relative overflow-hidden bg-white rounded-lg shadow-lg aspect-video">
                    <iframe
                        src="https://www.facebook.com/plugins/video.php?height=314&href=https%3A%2F%2Fwww.facebook.com%2F61580447784881%2Fvideos%2F1514551849699310%2F&show_text=false&width=560&t=0"
                        class="absolute inset-0 w-full h-full"
                        style="border:none;"
                        scrolling="no"
                        frameborder="0"
                        allowfullscreen="true"
                        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                    </iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- Abstract Section -->
    <section class="py-20 bg-white">
        <div class="container px-6 mx-auto">
            <div class="max-w-5xl mx-auto">
                <h2 class="mb-12 text-4xl font-bold text-center">Abstract</h2>
                <div class="prose prose-lg max-w-none">
                    <p class="text-lg leading-relaxed text-gray-700">
                        Courant: Revolutionizing Power Management for MSMEs with IoT Integration and Predictive Maintenance using Isolation Forest and LSTM Algorithms is a progressive web application that monitors power consumption and provides data-driven insights to MSME owners. Designed to improve efficiency, reduce costs, and prevent equipment damage, it features remote device management, real-time alerts, and AI-powered predictive maintenance.
                    </p>
                    <p class="mt-6 text-lg leading-relaxed text-gray-700">
                        Courant operates with EDMER devices, IoT hardware where appliances are connected to measure consumption and detect anomalies. On the MSME side, users can register accounts, link devices, profile appliances, monitor energy use, receive maintenance insights, generate reports, and access system support. For super administrators, the platform provides account management, device whitelisting, energy-saving content, and configuration tools.
                    </p>
                    <p class="mt-6 text-lg leading-relaxed text-gray-700">
                        Evaluated by 50 respondents spanning MSME owners and licensed IT, ECE, and EE experts under ISO 25010 standards, Courant achieved an overall mean rating of 4.52, validating its reliability, functionality, and effectiveness in addressing MSME energy management needs.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Research Team Section -->
    <section class="py-20 bg-gray-50">
        <div class="container px-6 mx-auto">
            <div class="max-w-4xl mx-auto">
                <h2 class="mb-12 text-4xl font-bold text-center">Research Team</h2>
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="flex items-center space-x-4">
                            <img src="{{ asset('images/team/edmer.jpg') }}" alt="John Edmerson L. Pizarra" 
                                 class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">John Edmerson L. Pizarra</h3>
                                <p class="mt-1 text-gray-600">Lead Researcher</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="flex items-center space-x-4">
                            <img src="{{ asset('images/team/kevern.jpg') }}" alt="Kevern Joebert C. Angeles" 
                                 class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Kevern Joebert C. Angeles</h3>
                                <p class="mt-1 text-gray-600">Research Team Member</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="flex items-center space-x-4">
                            <img src="{{ asset('images/team/jian.jpg') }}" alt="Jian Ross G. Dela Rosa" 
                                 class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Jian Ross G. Dela Rosa</h3>
                                <p class="mt-1 text-gray-600">Research Team Member</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="flex items-center space-x-4">
                            <img src="{{ asset('images/team/florence.jpg') }}" alt="Florence Gayle C. Magpoc" 
                                 class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Florence Gayle C. Magpoc</h3>
                                <p class="mt-1 text-gray-600">Research Team Member</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Problem Statement Section -->
    <section class="py-20 bg-white">
        <div class="container px-6 mx-auto">
            <div class="max-w-5xl mx-auto">
                <h2 class="mb-12 text-4xl font-bold text-center">The Problem We're Solving</h2>
                <div class="prose prose-lg max-w-none">
                    <p class="text-lg leading-relaxed text-gray-700 mb-6">
                        Micro, Small, and Medium-sized Enterprises (MSMEs) face significant initial hurdles in adopting advanced power management systems, beginning with foundational challenges in utilizing the Internet of Things (IoT). The primary issues include:
                    </p>
                    
                    <div class="grid grid-cols-1 gap-8 mt-12 md:grid-cols-3">
                        <div class="p-6 border border-gray-200 rounded-lg">
                            <div class="w-12 h-12 mb-4 bg-red-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.98-.833-2.75 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <h3 class="mb-3 text-xl font-semibold">Connectivity Challenges</h3>
                            <p class="text-gray-600">Difficulty in securely registering and linking IoT devices to web applications, creating barriers for many MSMEs.</p>
                        </div>
                        
                        <div class="p-6 border border-gray-200 rounded-lg">
                            <div class="w-12 h-12 mb-4 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <h3 class="mb-3 text-xl font-semibold">Data Context Issues</h3>
                            <p class="text-gray-600">Limited value from raw data without proper appliance profiling and contextual analysis for meaningful insights.</p>
                        </div>
                        
                        <div class="p-6 border border-gray-200 rounded-lg">
                            <div class="w-12 h-12 mb-4 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                </svg>
                            </div>
                            <h3 class="mb-3 text-xl font-semibold">Reactive Maintenance</h3>
                            <p class="text-gray-600">Lack of systems to predict electrical faults, forcing businesses into reactive rather than proactive maintenance models.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Objectives Section -->
    <section class="py-20 bg-gray-50">
        <div class="container px-6 mx-auto">
            <div class="max-w-5xl mx-auto">
                <h2 class="mb-12 text-4xl font-bold text-center">Our Objectives</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-6 h-6 mt-1 bg-green-500 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold text-white">✓</span>
                            </div>
                            <p class="text-gray-700">Enable MSMEs to register and create business accounts through our registration module</p>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-6 h-6 mt-1 bg-green-500 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold text-white">✓</span>
                            </div>
                            <p class="text-gray-700">Allow device linking through our IoT device identification module</p>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-6 h-6 mt-1 bg-green-500 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold text-white">✓</span>
                            </div>
                            <p class="text-gray-700">Provide appliance profiling capabilities for better energy tracking</p>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-6 h-6 mt-1 bg-green-500 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold text-white">✓</span>
                            </div>
                            <p class="text-gray-700">Enable remote appliance power management through socket control</p>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-6 h-6 mt-1 bg-green-500 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold text-white">✓</span>
                            </div>
                            <p class="text-gray-700">Deliver real-time power consumption and power factor monitoring</p>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-6 h-6 mt-1 bg-green-500 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold text-white">✓</span>
                            </div>
                            <p class="text-gray-700">Provide AI-powered predictive maintenance insights and recommendations</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Technology Stack Section -->
    <section class="py-20 bg-white">
        <div class="container px-6 mx-auto">
            <div class="max-w-5xl mx-auto">
                <h2 class="mb-12 text-4xl font-bold text-center">Technology Stack</h2>
                
                <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">
                    <!-- Software Components -->
                    <div>
                        <h3 class="mb-6 text-2xl font-semibold">Software Components</h3>
                        <div class="space-y-4">
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                <span class="font-medium">PHP & Laravel Framework</span>
                            </div>
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                <span class="font-medium">Python & Flask</span>
                            </div>
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="w-3 h-3 bg-purple-500 rounded-full mr-3"></div>
                                <span class="font-medium">FilamentPHP Admin Panel</span>
                            </div>
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                                <span class="font-medium">TensorFlow & NumPy</span>
                            </div>
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                                <span class="font-medium">MySQL Database</span>
                            </div>
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="w-3 h-3 bg-indigo-500 rounded-full mr-3"></div>
                                <span class="font-medium">MQTT Protocol</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hardware Components -->
                    <div>
                        <h3 class="mb-6 text-2xl font-semibold">Hardware Components</h3>
                        <div class="space-y-4">
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="w-3 h-3 bg-orange-500 rounded-full mr-3"></div>
                                <span class="font-medium">NodeMCU (ESP8266) Microcontroller</span>
                            </div>
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="w-3 h-3 bg-teal-500 rounded-full mr-3"></div>
                                <span class="font-medium">PZEM-004T 100A Power Sensor</span>
                            </div>
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="w-3 h-3 bg-pink-500 rounded-full mr-3"></div>
                                <span class="font-medium">Multi-channel Relay Module</span>
                            </div>
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="w-3 h-3 bg-gray-500 rounded-full mr-3"></div>
                                <span class="font-medium">Overcurrent Protection Fuse</span>
                            </div>
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="w-3 h-3 bg-cyan-500 rounded-full mr-3"></div>
                                <span class="font-medium">Wi-Fi Connectivity Module</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Results Section -->
    <section class="py-20 bg-gray-50">
        <div class="container px-6 mx-auto">
            <div class="max-w-5xl mx-auto">
                <h2 class="mb-12 text-4xl font-bold text-center">Research Results</h2>
                
                <div class="p-8 mb-12 bg-white rounded-lg shadow-sm">
                    <h3 class="mb-6 text-2xl font-semibold text-center">Overall System Rating</h3>
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-32 h-32 mb-4 bg-green-100 rounded-full">
                            <span class="text-4xl font-bold text-green-600">4.52</span>
                        </div>
                        <p class="text-lg text-gray-600">Out of 5.0 - Excellent Rating</p>
                        <p class="mt-2 text-sm text-gray-500">Based on ISO 25010 standards evaluation by 50 respondents</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div class="p-6 bg-white rounded-lg shadow-sm text-center">
                        <h4 class="text-lg font-semibold mb-2">Functional Suitability</h4>
                        <div class="text-2xl font-bold text-blue-600 mb-1">4.6</div>
                        <div class="text-sm text-gray-500">Excellent</div>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow-sm text-center">
                        <h4 class="text-lg font-semibold mb-2">Performance Efficiency</h4>
                        <div class="text-2xl font-bold text-blue-600 mb-1">4.49</div>
                        <div class="text-sm text-gray-500">Very Good</div>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow-sm text-center">
                        <h4 class="text-lg font-semibold mb-2">Security</h4>
                        <div class="text-2xl font-bold text-blue-600 mb-1">4.55</div>
                        <div class="text-sm text-gray-500">Excellent</div>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow-sm text-center">
                        <h4 class="text-lg font-semibold mb-2">Maintainability</h4>
                        <div class="text-2xl font-bold text-blue-600 mb-1">4.52</div>
                        <div class="text-sm text-gray-500">Excellent</div>
                    </div>
                </div>
                
                <div class="p-8 mt-12 bg-green-50 rounded-lg border border-green-200">
                    <h3 class="mb-4 text-xl font-semibold text-green-800">Implementation Success</h3>
                    <p class="text-green-700">
                        <strong>98% of respondents</strong> were in favor of implementing the system because it is reliable, user-friendly, and effective. Courant combines essential modules such as monitoring, predictive maintenance, reporting, and learning resources into one application, making it convenient and beneficial for MSMEs in managing their power consumption.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Methodology Section -->
    <section class="py-20 bg-white">
        <div class="container px-6 mx-auto">
            <div class="max-w-5xl mx-auto">
                <h2 class="mb-12 text-4xl font-bold text-center">Development Methodology</h2>
                
                <div class="mb-12 text-center">
                    <p class="text-lg text-gray-600 mb-8">
                        We adopted the Rapid Application Development (RAD) methodology for developing our IoT-based system, 
                        promoting rapid prototyping, iterative testing, and continuous user feedback.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-purple-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-purple-600">1</span>
                        </div>
                        <h3 class="mb-3 text-xl font-semibold">Requirements Planning</h3>
                        <p class="text-gray-600">Establishing scope, objectives, and essential requirements through stakeholder collaboration.</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-blue-600">2</span>
                        </div>
                        <h3 class="mb-3 text-xl font-semibold">User Design</h3>
                        <p class="text-gray-600">Developing detailed system models and prototypes with wireframes and UI design.</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-green-600">3</span>
                        </div>
                        <h3 class="mb-3 text-xl font-semibold">Rapid Construction</h3>
                        <p class="text-gray-600">Building core functionality using iterative processes with continuous user feedback.</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-orange-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-orange-600">4</span>
                        </div>
                        <h3 class="mb-3 text-xl font-semibold">Cutover Phase</h3>
                        <p class="text-gray-600">Final deployment with comprehensive testing and ongoing maintenance support.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Future Recommendations Section -->
    <section class="py-20 bg-gray-50">
        <div class="container px-6 mx-auto">
            <div class="max-w-5xl mx-auto">
                <h2 class="mb-12 text-4xl font-bold text-center">Future Enhancements</h2>
                
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="w-12 h-12 mb-4 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-semibold">Enhanced Predictive Maintenance</h3>
                        <p class="text-gray-600">Include corrective actions, allowing the system to automatically address or prevent faults when possible.</p>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="w-12 h-12 mb-4 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-semibold">Advanced Monitoring</h3>
                        <p class="text-gray-600">Expand capabilities to cover issues requiring physical inspection such as overheating and mechanical damage.</p>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="w-12 h-12 mb-4 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-semibold">Connectivity Improvements</h3>
                        <p class="text-gray-600">Implement measures to minimize delays in real-time reporting caused by internet interruptions.</p>
                    </div>
                    
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <div class="w-12 h-12 mb-4 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-semibold">Hardware Upgrades</h3>
                        <p class="text-gray-600">Upgrade hardware to support appliances with load above 10A (2,200 W) and increase simultaneous monitoring capacity.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 text-white bg-black">
        <div class="container px-6 mx-auto text-center">
            <h2 class="mb-6 text-3xl font-bold md:text-4xl">Ready to Transform Your Energy Management?</h2>
            <p class="max-w-2xl mx-auto mb-10 text-lg opacity-80">
                Join the revolution in smart energy management for MSMEs. Experience the power of IoT integration and AI-driven predictive maintenance.
            </p>

            <div class="flex flex-col justify-center space-y-4 sm:flex-row sm:space-y-0 sm:space-x-6">
                <a href="{{ route('filament.app.auth.register') }}"
                    class="px-8 py-4 font-semibold text-black transition bg-white rounded-sm hover:bg-gray-100">Get Started Today</a>
                <a href="{{ route('contact') }}"
                    class="px-8 py-4 font-semibold text-white transition border border-white rounded-sm hover:bg-white hover:text-black">Contact Our Team</a>
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
</body>

</html>
