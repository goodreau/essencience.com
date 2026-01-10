<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Essencience - The Age of Quintessence' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&family=Roboto+Serif:wght@400;700&display=swap');
        
        body {
            font-family: 'Open Sans', sans-serif;
        }
        
        .font-serif {
            font-family: 'Roboto Serif', serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-black to-gray-900 min-h-screen text-white">
    <!-- Navigation -->
    <nav class="bg-black bg-opacity-50 backdrop-blur-lg border-b border-orange-500/30 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center">
                        <span class="text-2xl font-serif text-white">θ</span>
                    </div>
                    <h1 class="text-xl md:text-2xl font-bold font-serif">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-500">
                            Essencience
                        </span>
                    </h1>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="/" class="text-gray-300 hover:text-orange-500 transition-colors">Home</a>
                    <a href="#quintessentials" class="text-gray-300 hover:text-orange-500 transition-colors">Quintessentials</a>
                    <a href="#" class="text-gray-300 hover:text-orange-500 transition-colors">Philosophy</a>
                    <a href="#" class="text-gray-300 hover:text-orange-500 transition-colors">Science</a>
                    <a href="#" class="text-gray-300 hover:text-orange-500 transition-colors">Rituals</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-black bg-opacity-50 backdrop-blur-lg border-t border-orange-500/30 mt-16">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div>
                    <h3 class="text-lg font-bold font-serif mb-4">Essencience</h3>
                    <p class="text-gray-400 text-sm">
                        A portal for philosophy, science, and sacred practice in The Age of Quintessence.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-bold font-serif mb-4">Explore</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-gray-400 hover:text-orange-500 transition-colors">The Ten Quintessentials</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-orange-500 transition-colors">Philosopher King</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-orange-500 transition-colors">Quintessential Science</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-orange-500 transition-colors">Sacred Rituals</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold font-serif mb-4">Community</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-gray-400 hover:text-orange-500 transition-colors">The Circle</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-orange-500 transition-colors">Chronicle</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-orange-500 transition-colors">Events</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-orange-500 transition-colors">Knowledge Base</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-6 text-center">
                <p class="text-gray-400 text-sm">
                    &copy; {{ date('Y') }} Essencience. The Age of Quintessence. Gene K. Goodreau, The Philosopher King.
                </p>
                <div class="mt-4 flex items-center justify-center space-x-2">
                    <span class="text-2xl">θ</span>
                    <span class="text-gray-500 text-sm">Origin • Transformation • Quintessence</span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
