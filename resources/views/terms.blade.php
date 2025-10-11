<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Terms and Conditions - Courant Energy Solutions</title>

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

    <!-- Header Section -->
    <section class="py-20 bg-white">
        <div class="container px-6 mx-auto">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="mb-6 text-5xl font-bold leading-tight">Terms and Conditions</h1>
                <p class="mb-8 text-lg text-gray-600">Last updated: {{ now()->format('F d, Y') }}</p>
                <div class="h-1 mx-auto mb-8 bg-black w-28"></div>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="pb-20 bg-white">
        <div class="container px-6 mx-auto">
            <div class="max-w-4xl mx-auto prose prose-lg">
                
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing and using the Courant Energy Solutions platform ("Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>

                <h2>2. Description of Service</h2>
                <p>Courant Energy Solutions provides intelligent energy management solutions for households and businesses, including but not limited to:</p>
                <ul>
                    <li>Energy consumption monitoring and analytics</li>
                    <li>Smart energy optimization recommendations</li>
                    <li>Automated energy management systems</li>
                    <li>Cost analysis and reporting tools</li>
                    <li>Integration with smart appliances and IoT devices</li>
                </ul>

                <h2>3. User Accounts</h2>
                <p>To access certain features of the Service, you must create an account. You agree to:</p>
                <ul>
                    <li>Provide accurate and complete registration information</li>
                    <li>Keep your account information updated</li>
                    <li>Maintain the security of your password and account</li>
                    <li>Notify us immediately of any unauthorized use of your account</li>
                </ul>

                <h2>4. Acceptable Use</h2>
                <p>You agree to use the Service only for lawful purposes and in accordance with these Terms. You agree not to:</p>
                <ul>
                    <li>Use the Service for any illegal or unauthorized purpose</li>
                    <li>Interfere with or disrupt the Service or servers connected to the Service</li>
                    <li>Attempt to gain unauthorized access to any portion of the Service</li>
                    <li>Use the Service to transmit any harmful, threatening, or offensive content</li>
                </ul>

                <h2>5. Data and Privacy</h2>
                <p>Your privacy is important to us. Our collection and use of personal information is governed by our Privacy Policy. By using the Service, you consent to the collection and use of your information as outlined in the Privacy Policy.</p>

                <h2>6. Energy Data</h2>
                <p>You grant Courant permission to collect, analyze, and use energy consumption data from your connected devices to provide the Service. This data helps us:</p>
                <ul>
                    <li>Provide personalized energy insights and recommendations</li>
                    <li>Improve our algorithms and service quality</li>
                    <li>Generate industry insights (in anonymized, aggregated form)</li>
                </ul>

                <h2>7. Intellectual Property</h2>
                <p>The Service and its original content, features, and functionality are owned by Courant Energy Solutions and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.</p>

                <h2>8. Limitation of Liability</h2>
                <p>In no event shall Courant Energy Solutions be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your use of the Service.</p>

                <h2>9. Service Availability</h2>
                <p>We strive to maintain high availability of our Service, but we do not guarantee uninterrupted access. The Service may be temporarily unavailable due to maintenance, updates, or technical issues.</p>

                <h2>10. Termination</h2>
                <p>We may terminate or suspend your account and access to the Service immediately, without prior notice, if you breach these Terms of Service.</p>

                <h2>11. Changes to Terms</h2>
                <p>We reserve the right to modify these Terms at any time. We will notify users of any material changes via email or through the Service. Continued use of the Service after such modifications constitutes acceptance of the updated Terms.</p>

                <h2>12. Governing Law</h2>
                <p>These Terms shall be interpreted and governed by the laws of the jurisdiction in which Courant Energy Solutions operates, without regard to conflict of law provisions.</p>

                <h2>13. Contact Information</h2>
                <p>If you have any questions about these Terms and Conditions, please contact us at:</p>
                <div class="p-6 mt-6 border border-gray-200 rounded-lg bg-gray-50">
                    <p><strong>Courant Energy Solutions</strong><br>
                    Email: legal@courant.energy<br>
                    Support: support@courant.energy</p>
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
                        <a href="{{ route('privacy') }}" class="text-gray-600 hover:text-gray-900">Privacy</a>
                        <a href="{{ route('contact') }}" class="text-gray-600 hover:text-gray-900">Contact</a>
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
