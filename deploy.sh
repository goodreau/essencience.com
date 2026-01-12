#!/bin/bash
set -e

echo "🚀 Deploying Essencience to Hostinger..."

DEPLOY_PATH="/home/u693982071/public_html"
REPO="https://github.com/goodreau/essencience.com.git"

# Step 1: Backup existing files if they exist
if [ -d "$DEPLOY_PATH" ] && [ -f "$DEPLOY_PATH/artisan" ]; then
    echo "📦 Backing up existing installation..."
    mv "$DEPLOY_PATH" "${DEPLOY_PATH}_backup_$(date +%s)"
fi

# Step 2: Clone the repository
echo "📥 Cloning repository..."
git clone "$REPO" "$DEPLOY_PATH"
cd "$DEPLOY_PATH"

# Step 3: Copy environment file
echo "⚙️ Setting up environment..."
cp .env.example .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=sqlite/' .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DEPLOY_PATH/database/database.sqlite|" .env

# Step 4: Create database file
echo "🗄️ Creating database..."
touch "$DEPLOY_PATH/database/database.sqlite"

# Step 5: Install Composer dependencies
echo "📚 Installing dependencies (this may take a few minutes)..."
composer install --no-dev --optimize-autoloader --no-progress

# Step 6: Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Step 7: Run migrations
echo "🔄 Running migrations..."
php artisan migrate --force

# Step 8: Set proper permissions
echo "🔐 Setting file permissions..."
chown -R nobody:nobody "$DEPLOY_PATH"
chmod -R 755 "$DEPLOY_PATH"
chmod -R 775 "$DEPLOY_PATH/storage"
chmod -R 775 "$DEPLOY_PATH/bootstrap/cache"
chmod 666 "$DEPLOY_PATH/database/database.sqlite"

# Step 9: Cache configuration
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deployment complete!"
echo "🌐 Visit https://essencience.com to see your site"
