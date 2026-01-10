<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Essencience - φ</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:700" rel="stylesheet" />

        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            :root {
                --phi: φ;
                --primary: #2563eb;
                --primary-dark: #1e40af;
                --bg: #ffffff;
                --bg-secondary: #f8fafc;
                --text: #0f172a;
                --text-secondary: #64748b;
                --border: #e2e8f0;
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --primary: #3b82f6;
                    --primary-dark: #60a5fa;
                    --bg: #0f172a;
                    --bg-secondary: #1e293b;
                    --text: #f1f5f9;
                    --text-secondary: #94a3b8;
                    --border: #334155;
                }
            }

            body {
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
                background: var(--bg);
                color: var(--text);
                line-height: 1.6;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 1.5rem;
                width: 100%;
            }

            header {
                padding: 2rem 0;
                border-bottom: 1px solid var(--border);
            }

            nav {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .logo {
                display: flex;
                align-items: center;
                gap: 1rem;
                font-size: 1.5rem;
                font-weight: 700;
                text-decoration: none;
                color: var(--text);
            }

            .phi-symbol {
                font-family: 'Playfair Display', serif;
                font-size: 3rem;
                color: var(--primary);
                font-weight: 700;
                line-height: 1;
            }

            .nav-links {
                display: flex;
                gap: 2rem;
                list-style: none;
            }

            .nav-links a {
                text-decoration: none;
                color: var(--text-secondary);
                font-weight: 500;
                transition: color 0.2s;
                padding: 0.5rem 1rem;
                border-radius: 0.5rem;
                border: 1px solid transparent;
            }

            .nav-links a:hover {
                color: var(--primary);
            }

            .nav-links a.btn {
                background: var(--primary);
                color: white;
                border-color: var(--primary);
            }

            .nav-links a.btn:hover {
                background: var(--primary-dark);
                border-color: var(--primary-dark);
            }

            .hero {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                flex: 1;
                text-align: center;
                padding: 4rem 0;
            }

            .hero-phi {
                font-family: 'Playfair Display', serif;
                font-size: 12rem;
                color: var(--primary);
                font-weight: 700;
                line-height: 1;
                margin-bottom: 2rem;
                animation: fadeInScale 1s ease-out;
            }

            @keyframes fadeInScale {
                from {
                    opacity: 0;
                    transform: scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            .hero h1 {
                font-size: 3rem;
                font-weight: 700;
                margin-bottom: 1rem;
                animation: fadeInUp 1s ease-out 0.2s both;
            }

            .hero p {
                font-size: 1.25rem;
                color: var(--text-secondary);
                max-width: 600px;
                margin-bottom: 2rem;
                animation: fadeInUp 1s ease-out 0.4s both;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .hero-buttons {
                display: flex;
                gap: 1rem;
                animation: fadeInUp 1s ease-out 0.6s both;
                flex-wrap: wrap;
                justify-content: center;
            }

            .btn {
                display: inline-block;
                padding: 0.875rem 2rem;
                border-radius: 0.5rem;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.2s;
                border: 2px solid transparent;
            }

            .btn-primary {
                background: var(--primary);
                color: white;
                border-color: var(--primary);
            }

            .btn-primary:hover {
                background: var(--primary-dark);
                border-color: var(--primary-dark);
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(37, 99, 235, 0.2);
            }

            .btn-secondary {
                background: transparent;
                color: var(--text);
                border-color: var(--border);
            }

            .btn-secondary:hover {
                border-color: var(--primary);
                color: var(--primary);
            }

            .features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 2rem;
                padding: 4rem 0;
                animation: fadeInUp 1s ease-out 0.8s both;
            }

            .feature-card {
                padding: 2rem;
                background: var(--bg-secondary);
                border-radius: 1rem;
                border: 1px solid var(--border);
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .feature-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
            }

            .feature-icon {
                font-size: 2.5rem;
                margin-bottom: 1rem;
            }

            .feature-card h3 {
                font-size: 1.25rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
            }

            .feature-card p {
                color: var(--text-secondary);
            }

            footer {
                padding: 2rem 0;
                border-top: 1px solid var(--border);
                text-align: center;
                color: var(--text-secondary);
                margin-top: auto;
            }

            @media (max-width: 768px) {
                .hero-phi {
                    font-size: 8rem;
                }

                .hero h1 {
                    font-size: 2rem;
                }

                .hero p {
                    font-size: 1rem;
                }

                .hero-buttons {
                    flex-direction: column;
                    width: 100%;
                    max-width: 300px;
                }

                .nav-links {
                    gap: 1rem;
                }

                .phi-symbol {
                    font-size: 2rem;
                }

                .logo {
                    font-size: 1.25rem;
                }
            }
        </style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <header>
            <div class="container">
                <nav>
                    <a href="/" class="logo">
                        <span class="phi-symbol">φ</span>
                        <span>Essencience</span>
                    </a>
                    @if (Route::has('login'))
                        <ul class="nav-links">
                            @auth
                                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                            @else
                                <li><a href="{{ route('login') }}">Log in</a></li>
                                @if (Route::has('register'))
                                    <li><a href="{{ route('register') }}" class="btn">Get Started</a></li>
                                @endif
                            @endauth
                        </ul>
                    @endif
                </nav>
            </div>
        </header>

        <main class="hero">
            <div class="container">
                <div class="hero-phi">φ</div>
                <h1>Welcome to Essencience</h1>
                <p>Experience the golden ratio of web development. Built with Laravel and powered by elegance.</p>
                <div class="hero-buttons">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                    @endif
                    <a href="https://laravel.com/docs" target="_blank" class="btn btn-secondary">Documentation</a>
                </div>
                
                <div class="features">
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3>Lightning Fast</h3>
                        <p>Optimized for performance with modern Laravel features and best practices.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🎨</div>
                        <h3>Beautiful Design</h3>
                        <p>Clean, elegant interface following the principles of the golden ratio.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Secure by Default</h3>
                        <p>Built-in security features to keep your application and data safe.</p>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <div class="container">
                <p>&copy; {{ date('Y') }} Essencience. Powered by Laravel {{ app()->version() }}.</p>
            </div>
        </footer>
    </body>
</html>
