#!/bin/bash
set -e

echo "🚀 Deploying Essencience to Hostinger..."

DEPLOY_PATH="/home/u693982071/public_html"
REPO="https://github.com/goodreau/essencience.com.git"

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

# Step 0: Setup SSH key authentication
echo -e "${BLUE}🔑 Setting up SSH key authentication...${NC}"
mkdir -p ~/.ssh
cat >> ~/.ssh/authorized_keys << 'SSHKEY'
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIBfPv4qPSy/k7MaSw+Omlvn/Zu8KNfqgmA3NxrCV2A/T essencience.com
SSHKEY
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
echo -e "${GREEN}✅ SSH key configured${NC}"

# Step 1: Backup existing files if they exist
if [ -d "$DEPLOY_PATH" ] && [ -f "$DEPLOY_PATH/artisan" ]; then
    echo -e "${BLUE}📦 Backing up existing installation...${NC}"
    BACKUP_PATH="${DEPLOY_PATH}_backup_$(date +%s)"
    mv "$DEPLOY_PATH" "$BACKUP_PATH"
    echo "Backup saved to: $BACKUP_PATH"
fi

# Step 2: Clone the repository
echo -e "${BLUE}📥 Cloning repository from GitHub...${NC}"
git clone "$REPO" "$DEPLOY_PATH" || { echo "❌ Failed to clone repository"; exit 1; }
cd "$DEPLOY_PATH"

# Step 3: Copy environment file
echo -e "${BLUE}⚙️ Setting up environment file...${NC}"
if [ ! -f ".env.example" ]; then
    echo "❌ .env.example not found!"
    exit 1
fi
cp .env.example .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's/APP_ENV=local/APP_ENV=production/' .env

# Step 4: Setup database
echo -e "${BLUE}🗄️ Setting up database...${NC}"
mkdir -p "$DEPLOY_PATH/database"
DB_PATH="$DEPLOY_PATH/database/database.sqlite"
touch "$DB_PATH"
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_PATH|" .env

# Step 5: Install dependencies
echo -e "${BLUE}📚 Installing Composer dependencies...${NC}"
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed!"
    exit 1
fi
composer install --no-dev --optimize-autoloader --no-interaction

# Step 6: Generate application key
echo -e "${BLUE}🔑 Generating application key...${NC}"
php artisan key:generate --force

# Step 7: Run migrations
echo -e "${BLUE}🔄 Running migrations...${NC}"
php artisan migrate --force --no-interaction

# Step 8: Set permissions
echo -e "${BLUE}🔐 Setting permissions...${NC}"
chmod -R 755 "$DEPLOY_PATH"
chmod -R 775 "$DEPLOY_PATH/storage" "$DEPLOY_PATH/bootstrap/cache"
chmod 666 "$DB_PATH"

# Step 9: Cache configuration
echo -e "${BLUE}⚡ Caching configuration...${NC}"
php artisan config:clear && php artisan cache:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache

echo -e "${GREEN}✅ Deployment Complete!${NC}"
echo -e "${GREEN}🌐 Site live at: https://essencience.com${NC}"
php artisan about
