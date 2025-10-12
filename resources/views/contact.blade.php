<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Contact Us - Courant Energy Solutions</title>

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
                <h1 class="mb-6 text-5xl font-bold leading-tight">Contact Us</h1>
                <p class="mb-8 text-lg text-gray-600">Get in touch with our team. We're here to help you optimize your
                    energy management.</p>
                <div class="h-1 mx-auto mb-8 bg-black w-28"></div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="pb-20 bg-white">
        <div class="container px-6 mx-auto">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">

                    <!-- Contact Information -->
                    <div class="space-y-8">
                        <div>
                            <h2 class="mb-6 text-3xl font-bold">Get in Touch</h2>
                            <p class="mb-8 text-lg text-gray-600">Have questions about our energy management solutions?
                                Our team is ready to help you take control of your energy consumption.</p>
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-start space-x-4">
                                <div class="flex items-center justify-center w-12 h-12 bg-black rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Email</h3>
                                    <p class="text-gray-600">support@courant.energy</p>
                                    <p class="text-gray-600">sales@courant.energy</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="flex items-center justify-center w-12 h-12 bg-black rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Phone</h3>
                                    <p class="text-gray-600">(+63)995 752 4651</p>
                                    <p class="text-sm text-gray-500">Monday - Friday, 9AM - 6PM Asia/Manila</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="flex items-center justify-center w-12 h-12 bg-black rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Office</h3>
                                    <p class="text-gray-600">Bataan, Central Luzon, Philippines</p>
                                </div>
                            </div>
                        </div>

                        <!-- Call to Action -->
                        <div class="p-6 border border-gray-200 rounded-lg bg-gray-50">
                            <h3 class="mb-2 text-lg font-semibold">Ready to Get Started?</h3>
                            <p class="mb-4 text-gray-600">Join thousands of satisfied customers who are already saving
                                money and energy.</p>
                            <div class="flex flex-col space-y-3 sm:flex-row sm:space-y-0 sm:space-x-3">
                                <a href="{{ route('filament.app.auth.register') }}"
                                    class="px-6 py-2 text-center text-white transition bg-black rounded-md hover:bg-gray-800">
                                    Start Free Trial
                                </a>
                               
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="p-8 bg-white border border-gray-200 rounded-lg">
                        <h3 class="mb-6 text-2xl font-bold">Send us a Message</h3>
                        
                        <!-- Success Message -->
                        <div id="success-message" class="hidden p-4 mb-6 text-green-800 bg-green-100 border border-green-300 rounded-md">
                            <div class="flex flex-col items-start">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <strong>Thank you!</strong>
                                </div>
                                 Your message has been sent successfully. We'll get back to you soon.
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div id="error-message" class="hidden p-4 mb-6 text-red-800 bg-red-100 border border-red-300 rounded-md">
                            <div class="flex flex-col items-start">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    <strong>Error!</strong>
                                </div>
                                <span id="error-text">There was an error sending your message. Please try again.</span>
                            </div>
                        </div>

                        <form id="contact-form" class="space-y-6" action="https://formspree.io/f/meorrbpa" method="POST">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">First
                                        Name</label>
                                    <input type="text" name="first_name" id="first_name" required
                                        class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-black focus:border-black">
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last
                                        Name</label>
                                    <input type="text" name="last_name" id="last_name" required
                                        class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-black focus:border-black">
                                </div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="email" required
                                    class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-black focus:border-black">
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone
                                    Number</label>
                                <input type="tel" name="phone" id="phone"
                                    class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-black focus:border-black">
                            </div>

                            <div>
                                <label for="company" class="block text-sm font-medium text-gray-700">Company
                                    (Optional)</label>
                                <input type="text" name="company" id="company"
                                    class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-black focus:border-black">
                            </div>

                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                                <select name="subject" id="subject" required
                                    class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-black focus:border-black">
                                    <option value="">Select a subject</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="support">Technical Support</option>
                                    <option value="sales">Sales Question</option>
                                    <option value="partnership">Partnership</option>
                                    <option value="billing">Billing Issue</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                                <textarea name="message" id="message" rows="4" required
                                    class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-black focus:border-black"
                                    placeholder="Tell us about your energy management needs..."></textarea>
                            </div>

                            <div>
                                <button type="submit" id="submit-button"
                                    class="w-full px-6 py-3 font-medium text-white transition duration-200 bg-black rounded-md hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span id="submit-text">Send Message</span>
                                    <span id="submit-loading" class="hidden">
                                        <svg class="inline w-4 h-4 mr-2 animate-spin" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Sending...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-10 bg-white border-t border-gray-200">
        <div class="container px-6 mx-auto">
            <div class="flex flex-col justify-between md:items-center md:flex-row">
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
                    </div>

                    <!-- Social Media Links -->
                    @if (setting('site_social'))
                        <div class="flex flex-col space-y-4">
                            @foreach (setting('site_social') as $social)
                                <a href="{{ $social['link'] }}" target="_blank" rel="noopener noreferrer"
                                    class="text-gray-600 transition-colors hover:text-gray-900"
                                    title="{{ ucfirst($social['vendor']) }}">
                                    <span class="sr-only">{{ ucfirst($social['vendor']) }}</span>
                                    <!-- You can add specific icons based on vendor here -->
                                    {{ $social['vendor'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif


                </div>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('contact-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitButton = document.getElementById('submit-button');
            const submitText = document.getElementById('submit-text');
            const submitLoading = document.getElementById('submit-loading');
            const successMessage = document.getElementById('success-message');
            const errorMessage = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');
            
            // Hide any previous messages
            successMessage.classList.add('hidden');
            errorMessage.classList.add('hidden');
            
            // Show loading state
            submitButton.disabled = true;
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');
            
            try {
                const formData = new FormData(form);
                
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    // Success - show success message and reset form
                    successMessage.classList.remove('hidden');
                    form.reset();
                    
                    // Scroll to success message
                    successMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    
                    // Auto-hide success message after 10 seconds
                    setTimeout(() => {
                        successMessage.classList.add('hidden');
                    }, 10000);
                } else {
                    // Handle Formspree errors
                    const data = await response.json();
                    if (data.errors) {
                        // Show specific validation errors
                        const errorMessages = Object.values(data.errors).flat();
                        errorText.textContent = errorMessages.join(', ');
                    } else {
                        errorText.textContent = 'There was an error sending your message. Please try again.';
                    }
                    errorMessage.classList.remove('hidden');
                    errorMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            } catch (error) {
                // Network or other errors
                errorText.textContent = 'There was a problem connecting to the server. Please check your internet connection and try again.';
                errorMessage.classList.remove('hidden');
                errorMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } finally {
                // Reset button state
                submitButton.disabled = false;
                submitText.classList.remove('hidden');
                submitLoading.classList.add('hidden');
            }
        });

        // Auto-hide error messages after 10 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const errorMessage = document.getElementById('error-message');
            if (!errorMessage.classList.contains('hidden')) {
                setTimeout(() => {
                    errorMessage.classList.add('hidden');
                }, 10000);
            }
        });
    </script>
</body>

</html>
