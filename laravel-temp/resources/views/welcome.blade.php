@extends('layouts.app')

@section('content')
<div class="relative overflow-hidden">
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
            <h1 class="text-6xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 mb-6">
                {{ $title ?? 'Essencience' }}
            </h1>
            <p class="text-2xl text-gray-300 mb-12">
                {{ $tagline ?? 'Secure, Passwordless Authentication' }}
            </p>

            <div class="flex justify-center space-x-4">
                <div class="bg-purple-600 bg-opacity-20 backdrop-blur-lg border border-purple-500 rounded-lg p-6 max-w-sm">
                    <svg class="w-16 h-16 mx-auto mb-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-white mb-2">Certificate-Based Auth</h3>
                    <p class="text-gray-400">No passwords needed. Authenticate with X.509 certificates.</p>
                </div>

                <div class="bg-pink-600 bg-opacity-20 backdrop-blur-lg border border-pink-500 rounded-lg p-6 max-w-sm">
                    <svg class="w-16 h-16 mx-auto mb-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-white mb-2">Secure by Design</h3>
                    <p class="text-gray-400">Built-in Certificate Authority with macOS Keychain integration.</p>
                </div>

                <div class="bg-red-600 bg-opacity-20 backdrop-blur-lg border border-red-500 rounded-lg p-6 max-w-sm">
                    <svg class="w-16 h-16 mx-auto mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-white mb-2">Laravel Powered</h3>
                    <p class="text-gray-400">Modern PHP framework with custom authentication packages.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-black bg-opacity-30 backdrop-blur-lg border border-purple-500 rounded-2xl p-12">
            <h2 class="text-4xl font-bold text-white mb-8 text-center">How It Works</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-purple-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white">1</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">Get Certificate</h3>
                    <p class="text-gray-400">Administrator issues you a personal X.509 certificate</p>
                </div>

                <div class="text-center">
                    <div class="bg-pink-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white">2</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">Install Certificate</h3>
                    <p class="text-gray-400">Import the .p12 file into your browser or system keychain</p>
                </div>

                <div class="text-center">
                    <div class="bg-red-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white">3</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">Access Securely</h3>
                    <p class="text-gray-400">Browse protected pages - automatic certificate authentication</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-12">
            <h2 class="text-4xl font-bold text-white mb-4">Ready to Get Started?</h2>
            <p class="text-xl text-gray-100 mb-8">Contact your administrator for certificate issuance</p>
            <code class="bg-black bg-opacity-50 px-6 py-3 rounded-lg text-green-400 inline-block">
                php artisan passport:issue your@email.com
            </code>
        </div>
    </div>
</div>
@endsection
