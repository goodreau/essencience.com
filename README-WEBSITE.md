# Essencience.com - Laravel Website

A Laravel-based website for The Age of Quintessence, featuring the philosophy and teachings of Gene K. Goodreau, The Philosopher King.

## Features

- **Beautiful Homepage**: Featuring The Ten Quintessentials and Essencience branding
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Modern Stack**: Laravel 12, Tailwind CSS, Vite
- **Certificate-Based Authentication**: Custom Essencience Passport system
- **Modular Architecture**: Extensible module system for adding features

## Quick Start

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- SQLite (or MySQL/PostgreSQL)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/goodreau/essencience.com.git
   cd essencience.com
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Create environment file**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Set up database**
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   ```

7. **Start development server**
   ```bash
   php artisan serve
   ```

Visit http://localhost:8000 to see the website!

## Development

### Running in development mode

Start both the Laravel server and Vite dev server:

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server (for hot reload)
npm run dev
```

### Running tests

```bash
php artisan test
```

### Code style

```bash
./vendor/bin/pint
```

## Project Structure

```
essencience.com/
├── app/
│   ├── Http/Controllers/     # Controllers
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic
│   └── Providers/           # Service providers
├── config/                  # Configuration files
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/            # Database seeders
├── packages/
│   └── essencience/        # Custom packages
│       ├── certificate-authority/
│       └── passport/
├── public/                 # Public assets
├── resources/
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript
│   └── views/             # Blade templates
├── routes/                # Route definitions
├── storage/               # Storage for logs, cache, etc.
└── tests/                 # Test suite
```

## The Ten Quintessentials

The website features these core principles:

1. **Truth** - Clarity of Being
2. **Justice** - Balanced Expression
3. **Beauty** - Aesthetic Harmony
4. **Love** - Universal Connection
5. **Balance** - Dynamic Equilibrium
6. **Wisdom** - Applied Knowledge
7. **Creativity** - Generative Force
8. **Harmony** - Resonant Living
9. **Unity** - Collective Essence
10. **Transformation** - Eternal Becoming

## Branding

### Color Palette

- **Q-Core-Charcoal**: `#1F1F1F` (foundation, authority)
- **Q-Light**: `#F3F4F5` (canvas, light background)
- **Q-Sunrise**: `#FF5600` (primary accent)
- **Q-Flame**: `#F45800` (secondary accent)
- **Q-Radiance**: `#F6405F` (tertiary accent)

### Typography

- **Display/Headings**: Roboto Serif
- **Body**: Open Sans
- **Symbol**: θ (Theta) - Origin and Transformation

## Custom Packages

### Essencience Certificate Authority

Generate and manage X.509 certificates for secure authentication.

```bash
php artisan ca:generate
php artisan ca:generate-server essencience.com
```

### Essencience Passport

Certificate-based authentication system.

```bash
php artisan passport:issue user@example.com
php artisan passport:list
php artisan passport:revoke <certificate-id>
```

## Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for production deployment instructions.

## Documentation

- [Implementation Roadmap](IMPLEMENTATION_ROADMAP.md) - Full feature roadmap
- [Content Analysis](CONTENT_ANALYSIS.md) - Content strategy and structure
- [RAG Setup](README-RAG.md) - AI/ML integration documentation

## Contributing

This is a private project for Essencience.com. Contact the repository owner for contribution guidelines.

## License

Copyright © 2026 Gene K. Goodreau. All rights reserved.

---

**The Age of Quintessence** • Gene K. Goodreau, The Philosopher King • θ
