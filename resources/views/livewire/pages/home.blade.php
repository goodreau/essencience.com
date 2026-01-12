<div>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-20">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <h1 class="text-5xl font-bold mb-4">Welcome to Essencience</h1>
            <p class="text-xl mb-8 opacity-90">Build modern web applications with Livewire and Laravel</p>
            <a href="{{ route('services') }}" class="inline-block bg-white text-purple-600 px-8 py-3 rounded-lg font-bold hover:bg-gray-100 transition" wire:navigate>
                Get Started
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">Why Choose Essencience?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition">
                    <div class="text-4xl mb-4">⚡</div>
                    <h3 class="text-2xl font-bold mb-4">Real-time Interactivity</h3>
                    <p class="text-gray-600">Livewire components provide instant, reactive updates without page refreshes. Pure PHP magic.</p>
                </div>
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition">
                    <div class="text-4xl mb-4">🎨</div>
                    <h3 class="text-2xl font-bold mb-4">Beautiful UI</h3>
                    <p class="text-gray-600">Built with Flux components and Tailwind CSS. Create stunning interfaces with minimal effort.</p>
                </div>
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition">
                    <div class="text-4xl mb-4">🔒</div>
                    <h3 class="text-2xl font-bold mb-4">Secure & Reliable</h3>
                    <p class="text-gray-600">Laravel's proven security features combined with Livewire's server-side processing.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div>
                    <div class="text-4xl font-bold text-purple-600">1000+</div>
                    <p class="text-gray-600">Active Projects</p>
                </div>
                <div>
                    <div class="text-4xl font-bold text-purple-600">50K+</div>
                    <p class="text-gray-600">Happy Developers</p>
                </div>
                <div>
                    <div class="text-4xl font-bold text-purple-600">99.9%</div>
                    <p class="text-gray-600">Uptime Guarantee</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-gradient-to-r from-purple-600 to-blue-600 text-white py-16">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-4">Ready to Build Something Amazing?</h2>
            <p class="text-lg mb-8 opacity-90">Start building your next project with Livewire today.</p>
            <a href="{{ route('contact') }}" class="inline-block bg-white text-purple-600 px-8 py-3 rounded-lg font-bold hover:bg-gray-100 transition" wire:navigate>
                Get in Touch
            </a>
        </div>
    </section>
</div>
