#!/bin/bash

# Essencience.com Laravel Setup Script
# This script sets up the Laravel application from scratch

set -e

echo "🎨 Essencience.com Laravel Setup"
echo "================================"
echo ""

# Check prerequisites
echo "Checking prerequisites..."

if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.2 or higher."
    exit 1
fi

if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer."
    exit 1
fi

if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js 18 or higher."
    exit 1
fi

echo "✅ All prerequisites met!"
echo ""

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction

# Install Node dependencies
echo "📦 Installing Node dependencies..."
npm install

# Setup environment file
if [ ! -f .env ]; then
    echo "⚙️  Creating .env file..."
    cp .env.example .env
    php artisan key:generate
else
    echo "⚠️  .env file already exists, skipping..."
fi

# Setup database
if [ ! -f database/database.sqlite ]; then
    echo "🗄️  Creating SQLite database..."
    touch database/database.sqlite
else
    echo "⚠️  Database already exists, skipping..."
fi

# Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# Build assets
echo "🎨 Building frontend assets..."
npm run build

# Success message
echo ""
echo "✨ Setup complete!"
echo ""
echo "To start the development server, run:"
echo "  php artisan serve"
echo ""
echo "Then visit http://localhost:8000 in your browser."
echo ""
echo "For development with hot reload, run in separate terminals:"
echo "  php artisan serve"
echo "  npm run dev"
echo ""
echo "🌟 Welcome to The Age of Quintessence! θ"
