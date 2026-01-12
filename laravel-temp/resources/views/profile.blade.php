@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-black bg-opacity-30 backdrop-blur-lg border border-purple-500 rounded-2xl p-8">
        <h1 class="text-4xl font-bold text-white mb-8">Profile & Certificates</h1>

        <!-- User Info -->
        <div class="bg-purple-600 bg-opacity-20 border border-purple-400 rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold text-white mb-4">User Information</h2>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-400">Name</dt>
                    <dd class="mt-1 text-lg text-white">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-400">Email</dt>
                    <dd class="mt-1 text-lg text-white">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-400">Member Since</dt>
                    <dd class="mt-1 text-lg text-white">{{ $user->created_at->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-400">User ID</dt>
                    <dd class="mt-1 text-lg text-white">#{{ $user->id }}</dd>
                </div>
            </dl>
        </div>

        <!-- Certificates -->
        <div class="bg-pink-600 bg-opacity-20 border border-pink-400 rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-white mb-4">Your Certificates</h2>

            @if($certificates && $certificates->count() > 0)
                <div class="space-y-4">
                    @foreach($certificates as $cert)
                        <div class="bg-black bg-opacity-30 border border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-lg font-mono text-purple-400">{{ $cert->serial_number }}</span>
                                        @if($cert->isValid())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-600 bg-opacity-20 text-green-400 border border-green-500">
                                                Valid
                                            </span>
                                        @elseif($cert->is_revoked)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-600 bg-opacity-20 text-red-400 border border-red-500">
                                                Revoked
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-600 bg-opacity-20 text-yellow-400 border border-yellow-500">
                                                Expired
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-400 mt-2">
                                        Valid from {{ $cert->valid_from->format('M d, Y') }}
                                        to {{ $cert->valid_until->format('M d, Y') }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">Subject: {{ $cert->subject }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-400 mb-4">No certificates found</p>
                    <p class="text-sm text-gray-500">Contact your administrator to issue a certificate for your account</p>
                </div>
            @endif
        </div>

        <div class="mt-8">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
