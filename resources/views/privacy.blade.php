<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Privacy Policy - Courant Energy Solutions</title>

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
                <h1 class="mb-6 text-5xl font-bold leading-tight">Privacy Policy</h1>
                <p class="mb-8 text-lg text-gray-600">Last updated: {{ now()->format('F d, Y') }}</p>
                <div class="h-1 mx-auto mb-8 bg-black w-28"></div>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="pb-20 bg-white">
        <div class="container px-6 mx-auto">
            <div class="max-w-4xl mx-auto prose prose-lg">
                
                <h2>1. Introduction</h2>
                <p>At Courant Energy Solutions ("we," "our," or "us"), we respect your privacy and are committed to protecting your personal data. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our energy management platform and services.</p>

                <h2>2. Information We Collect</h2>
                
                <h3>2.1 Personal Information</h3>
                <p>We may collect the following personal information:</p>
                <ul>
                    <li>Name, email address, phone number, and contact details</li>
                    <li>Account credentials and authentication information</li>
                    <li>Billing and payment information</li>
                    <li>Communication preferences</li>
                    <li>Profile information and preferences</li>
                </ul>

                <h3>2.2 Energy Data</h3>
                <p>We collect energy-related data including:</p>
                <ul>
                    <li>Energy consumption patterns and usage data</li>
                    <li>Smart device and appliance information</li>
                    <li>Utility meter readings and billing data</li>
                    <li>Home or business energy profiles</li>
                    <li>Environmental conditions (temperature, humidity, etc.)</li>
                </ul>

                <h3>2.3 Technical Information</h3>
                <p>We automatically collect:</p>
                <ul>
                    <li>IP addresses, device identifiers, and browser information</li>
                    <li>Usage logs, clickstream data, and interaction patterns</li>
                    <li>Location data (when permitted)</li>
                    <li>Performance metrics and error reports</li>
                </ul>

                <h2>3. How We Use Your Information</h2>
                <p>We use your information to:</p>
                <ul>
                    <li>Provide, maintain, and improve our energy management services</li>
                    <li>Generate personalized energy insights and recommendations</li>
                    <li>Process transactions and manage your account</li>
                    <li>Send important service notifications and updates</li>
                    <li>Conduct research and analytics to improve our algorithms</li>
                    <li>Ensure platform security and prevent fraud</li>
                    <li>Comply with legal obligations and regulatory requirements</li>
                </ul>

                <h2>4. Information Sharing and Disclosure</h2>
                
                <h3>4.1 We Do Not Sell Your Personal Information</h3>
                <p>We do not sell, rent, or trade your personal information to third parties for marketing purposes.</p>

                <h3>4.2 When We May Share Information</h3>
                <p>We may share your information in the following circumstances:</p>
                <ul>
                    <li><strong>Service Providers:</strong> With trusted third-party service providers who assist in operating our platform</li>
                    <li><strong>Legal Compliance:</strong> When required by law, regulation, or legal process</li>
                    <li><strong>Safety and Security:</strong> To protect the safety, rights, or property of users or others</li>
                    <li><strong>Business Transfers:</strong> In connection with mergers, acquisitions, or asset sales</li>
                    <li><strong>Consent:</strong> When you explicitly consent to sharing</li>
                </ul>

                <h3>4.3 Aggregated and Anonymized Data</h3>
                <p>We may share aggregated, anonymized data that cannot identify you personally for research, industry insights, and service improvement purposes.</p>

                <h2>5. Data Security</h2>
                <p>We implement industry-standard security measures to protect your information:</p>
                <ul>
                    <li>Encryption of data in transit and at rest</li>
                    <li>Secure authentication and access controls</li>
                    <li>Regular security audits and monitoring</li>
                    <li>Employee training on data protection practices</li>
                    <li>Incident response procedures</li>
                </ul>

                <h2>6. Data Retention</h2>
                <p>We retain your personal information only as long as necessary to:</p>
                <ul>
                    <li>Provide our services and fulfill the purposes outlined in this policy</li>
                    <li>Comply with legal, regulatory, or contractual obligations</li>
                    <li>Resolve disputes and enforce our agreements</li>
                </ul>

                <h2>7. Your Privacy Rights</h2>
                <p>Depending on your location, you may have the following rights:</p>
                <ul>
                    <li><strong>Access:</strong> Request information about the personal data we hold about you</li>
                    <li><strong>Correction:</strong> Update or correct inaccurate personal information</li>
                    <li><strong>Deletion:</strong> Request deletion of your personal data</li>
                    <li><strong>Portability:</strong> Receive a copy of your data in a portable format</li>
                    <li><strong>Restriction:</strong> Limit how we process your personal information</li>
                    <li><strong>Objection:</strong> Object to certain types of data processing</li>
                </ul>

                <h2>8. Cookies and Tracking Technologies</h2>
                <p>We use cookies and similar technologies to:</p>
                <ul>
                    <li>Enhance user experience and remember preferences</li>
                    <li>Analyze usage patterns and improve our services</li>
                    <li>Provide personalized content and recommendations</li>
                    <li>Ensure platform security and functionality</li>
                </ul>
                <p>You can control cookie settings through your browser preferences.</p>

                <h2>9. Third-Party Links and Services</h2>
                <p>Our platform may contain links to third-party websites or integrate with external services. We are not responsible for the privacy practices of these third parties. We encourage you to read their privacy policies.</p>

                <h2>10. Children's Privacy</h2>
                <p>Our services are not directed to children under 13 years of age. We do not knowingly collect personal information from children under 13. If we become aware of such collection, we will take steps to remove the information.</p>

                <h2>11. International Data Transfers</h2>
                <p>Your information may be processed and stored in countries other than your own. We ensure appropriate safeguards are in place to protect your data during international transfers.</p>

                <h2>12. Changes to This Privacy Policy</h2>
                <p>We may update this Privacy Policy periodically to reflect changes in our practices or applicable law. We will notify you of material changes via email or through our platform.</p>

                <h2>13. Contact Us</h2>
                <p>If you have questions about this Privacy Policy or wish to exercise your privacy rights, please contact us:</p>
                <div class="p-6 mt-6 border border-gray-200 rounded-lg bg-gray-50">
                    <p><strong>Courant Energy Solutions</strong><br>
                    Privacy Officer<br>
                    Email: privacy@courant.energy<br>
                    Support: support@courant.energy</p>
                </div>

                <div class="p-6 mt-6 border-l-4 border-black bg-gray-50">
                    <h3 class="text-lg font-semibold mb-2">Your Data, Your Control</h3>
                    <p class="text-sm">We believe in transparency and giving you control over your data. If you have any concerns or questions about how we handle your information, don't hesitate to reach out to our privacy team.</p>
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
                        <a href="{{ route('terms') }}" class="text-gray-600 hover:text-gray-900">Terms</a>
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