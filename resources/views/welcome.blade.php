@extends('layouts.app')

@section('content')
<div class="relative overflow-hidden">
    <!-- Hero Section - Proclamation Style -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
            <!-- Theta Symbol -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-orange-500 to-red-500 shadow-2xl">
                    <span class="text-6xl font-serif text-white">θ</span>
                </div>
            </div>
            
            <h1 class="text-6xl md:text-7xl font-bold text-white mb-6 font-serif">
                Essencience
            </h1>
            <p class="text-2xl md:text-3xl text-gray-300 mb-4">
                The Age of Quintessence
            </p>
            <p class="text-xl text-gray-400 mb-12 max-w-3xl mx-auto">
                A Portal of Philosophy, Science, and Sacred Practice
            </p>

            <!-- CTA Button -->
            <div class="mb-16">
                <a href="#quintessentials" class="inline-block bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold px-8 py-4 rounded-lg text-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    Enter the Circle
                </a>
            </div>
        </div>
    </div>

    <!-- The Ten Quintessentials Section -->
    <div id="quintessentials" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-4 font-serif">The Ten Quintessentials</h2>
            <p class="text-xl text-gray-400">Core principles of harmonic living and transformation</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
            <!-- Truth -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-orange-500 rounded-xl p-6 text-center hover:shadow-2xl hover:shadow-orange-500/50 transition-all duration-300">
                <div class="text-4xl mb-3">🔍</div>
                <h3 class="text-lg font-semibold text-white mb-2">Truth</h3>
                <p class="text-sm text-gray-400">Clarity of Being</p>
            </div>

            <!-- Justice -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-orange-500 rounded-xl p-6 text-center hover:shadow-2xl hover:shadow-orange-500/50 transition-all duration-300">
                <div class="text-4xl mb-3">⚖️</div>
                <h3 class="text-lg font-semibold text-white mb-2">Justice</h3>
                <p class="text-sm text-gray-400">Balanced Expression</p>
            </div>

            <!-- Beauty -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-orange-500 rounded-xl p-6 text-center hover:shadow-2xl hover:shadow-orange-500/50 transition-all duration-300">
                <div class="text-4xl mb-3">✨</div>
                <h3 class="text-lg font-semibold text-white mb-2">Beauty</h3>
                <p class="text-sm text-gray-400">Aesthetic Harmony</p>
            </div>

            <!-- Love -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-orange-500 rounded-xl p-6 text-center hover:shadow-2xl hover:shadow-orange-500/50 transition-all duration-300">
                <div class="text-4xl mb-3">❤️</div>
                <h3 class="text-lg font-semibold text-white mb-2">Love</h3>
                <p class="text-sm text-gray-400">Universal Connection</p>
            </div>

            <!-- Balance -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-orange-500 rounded-xl p-6 text-center hover:shadow-2xl hover:shadow-orange-500/50 transition-all duration-300">
                <div class="text-4xl mb-3">☯️</div>
                <h3 class="text-lg font-semibold text-white mb-2">Balance</h3>
                <p class="text-sm text-gray-400">Dynamic Equilibrium</p>
            </div>

            <!-- Wisdom -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-orange-500 rounded-xl p-6 text-center hover:shadow-2xl hover:shadow-orange-500/50 transition-all duration-300">
                <div class="text-4xl mb-3">🦉</div>
                <h3 class="text-lg font-semibold text-white mb-2">Wisdom</h3>
                <p class="text-sm text-gray-400">Applied Knowledge</p>
            </div>

            <!-- Creativity -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-orange-500 rounded-xl p-6 text-center hover:shadow-2xl hover:shadow-orange-500/50 transition-all duration-300">
                <div class="text-4xl mb-3">🎨</div>
                <h3 class="text-lg font-semibold text-white mb-2">Creativity</h3>
                <p class="text-sm text-gray-400">Generative Force</p>
            </div>

            <!-- Harmonic Living -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-orange-500 rounded-xl p-6 text-center hover:shadow-2xl hover:shadow-orange-500/50 transition-all duration-300">
                <div class="text-4xl mb-3">🎵</div>
                <h3 class="text-lg font-semibold text-white mb-2">Harmony</h3>
                <p class="text-sm text-gray-400">Resonant Living</p>
            </div>

            <!-- Unity -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-orange-500 rounded-xl p-6 text-center hover:shadow-2xl hover:shadow-orange-500/50 transition-all duration-300">
                <div class="text-4xl mb-3">🌐</div>
                <h3 class="text-lg font-semibold text-white mb-2">Unity</h3>
                <p class="text-sm text-gray-400">Collective Essence</p>
            </div>

            <!-- Transformation -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-orange-500 rounded-xl p-6 text-center hover:shadow-2xl hover:shadow-orange-500/50 transition-all duration-300">
                <div class="text-4xl mb-3">🦋</div>
                <h3 class="text-lg font-semibold text-white mb-2">Transformation</h3>
                <p class="text-sm text-gray-400">Eternal Becoming</p>
            </div>
        </div>
    </div>

    <!-- Philosophy & Science Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Philosopher King -->
            <div class="bg-gradient-to-br from-gray-900 to-black border border-gray-700 rounded-xl p-8 hover:border-orange-500 transition-all duration-300">
                <h3 class="text-2xl font-bold text-white mb-4 font-serif">The Philosopher King</h3>
                <p class="text-gray-400 mb-6">
                    Gene K. Goodreau serves as steward of Quintessence—a sovereign vision for civic responsibility and harmonic transformation.
                </p>
                <a href="#" class="text-orange-500 hover:text-orange-400 font-semibold">Learn More →</a>
            </div>

            <!-- Quintessential Science -->
            <div class="bg-gradient-to-br from-gray-900 to-black border border-gray-700 rounded-xl p-8 hover:border-orange-500 transition-all duration-300">
                <h3 class="text-2xl font-bold text-white mb-4 font-serif">Quintessential Science</h3>
                <p class="text-gray-400 mb-6">
                    Explore the mathematics of θ (Theta), morphogenesis, and the scientific foundations of Essencience.
                </p>
                <a href="#" class="text-orange-500 hover:text-orange-400 font-semibold">Discover →</a>
            </div>

            <!-- Sacred Rituals -->
            <div class="bg-gradient-to-br from-gray-900 to-black border border-gray-700 rounded-xl p-8 hover:border-orange-500 transition-all duration-300">
                <h3 class="text-2xl font-bold text-white mb-4 font-serif">Sacred Rituals</h3>
                <p class="text-gray-400 mb-6">
                    Practical ceremonies and paths aligned with the Ten Quintessentials for personal transformation.
                </p>
                <a href="#" class="text-orange-500 hover:text-orange-400 font-semibold">Explore →</a>
            </div>
        </div>
    </div>

    <!-- Proclamation CTA -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-gradient-to-r from-orange-600 to-red-600 rounded-2xl p-12 text-center shadow-2xl">
            <h2 class="text-4xl font-bold text-white mb-4 font-serif">Join the Age of Quintessence</h2>
            <p class="text-xl text-gray-100 mb-8">
                Receive proclamations and updates on your path to transformation
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <input type="email" placeholder="your@email.com" class="px-6 py-3 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-300">
                <button class="bg-gray-900 text-white px-8 py-3 rounded-lg font-semibold hover:bg-gray-800 transition-colors">
                    Subscribe
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
