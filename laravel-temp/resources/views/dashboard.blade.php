@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-black bg-opacity-30 backdrop-blur-lg border border-purple-500 rounded-2xl p-8">
        <h1 class="text-4xl font-bold text-white mb-6">Dashboard</h1>

        <div class="bg-purple-600 bg-opacity-20 border border-purple-400 rounded-lg p-6 mb-8">
            <div class="flex items-center space-x-4">
                <div class="bg-purple-600 rounded-full w-16 h-16 flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-semibold text-white">Welcome, {{ $user->name }}</h2>
                    <p class="text-gray-400">{{ $user->email }}</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-600 bg-opacity-20 text-green-400 border border-green-500 mt-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Authenticated via Certificate
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-purple-600 bg-opacity-10 backdrop-blur-lg border border-purple-500 rounded-lg p-6">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto mb-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-white mb-2">Active Certificates</h3>
                    <p class="text-3xl font-bold text-purple-400">{{ auth()->user()->certificates()->where('is_active', true)->count() ?? 0 }}</p>
                </div>
            </div>

            <div class="bg-pink-600 bg-opacity-10 backdrop-blur-lg border border-pink-500 rounded-lg p-6">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto mb-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-white mb-2">Security Level</h3>
                    <p class="text-3xl font-bold text-pink-400">High</p>
                </div>
            </div>

            <div class="bg-red-600 bg-opacity-10 backdrop-blur-lg border border-red-500 rounded-lg p-6">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-white mb-2">Last Login</h3>
                    <p class="text-lg text-red-400">{{ now()->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="{{ route('profile') }}" class="block bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg p-6 hover:shadow-lg transition">
                <h3 class="text-xl font-semibold text-white mb-2">View Profile</h3>
                <p class="text-gray-200">Manage your account and certificates</p>
            </a>

            <div class="bg-gradient-to-r from-pink-600 to-red-600 rounded-lg p-6">
                <h3 class="text-xl font-semibold text-white mb-2">Documentation</h3>
                <p class="text-gray-200">Learn about certificate-based authentication</p>
            </div>
        </div>
    </div>
</div>
@endsection
