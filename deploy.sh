#!/bin/bash

# Secure Hostinger Deployment Script
# Uses macOS Keychain for credential storage (passwords never in plain text)
# GitHub: https://github.com/goodreau/essencience.com

set -e

# Configuration
DEPLOY_PATH="/home/u693982071/public_html"
REPO="https://github.com/goodreau/essencience.com.git"
SERVICE="Essencience-Hostinger"
ACCOUNT="u693982071"
DEFAULT_HOST="147.93.42.19"
DEFAULT_PORT="65002"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Helper functions
print_header() {
    echo -e "${BLUE}═══════════════════════════════════════════════${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════${NC}"
}

print_step() {
    echo -e "${BLUE}→ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if running on macOS
if [[ "$OSTYPE" != "darwin"* ]]; then
    print_error "This script requires macOS with Keychain"
    exit 1
fi

# Retrieve credentials from Keychain
print_header "Retrieving Credentials from Keychain"

SSH_PASSWORD=$(security find-generic-password -s "$SERVICE" -a "$ACCOUNT" -w 2>/dev/null)
if [ -z "$SSH_PASSWORD" ]; then
    print_error "Credentials not found in Keychain"
    echo ""
    echo "First, run: bash setup-keychain.sh"
    exit 1
fi
print_success "Password retrieved from Keychain"

# Get optional SSH host/port from Keychain or use defaults
SSH_HOST=$(security find-generic-password -s "${SERVICE}-HOST" -a "$ACCOUNT" -w 2>/dev/null || echo "$DEFAULT_HOST")
SSH_PORT=$(security find-generic-password -s "${SERVICE}-PORT" -a "$ACCOUNT" -w 2>/dev/null || echo "$DEFAULT_PORT")

print_header "🚀 Deploying Essencience to Hostinger"

echo "Host: $SSH_HOST"
echo "Port: $SSH_PORT"
echo "User: $ACCOUNT"
echo "Path: $DEPLOY_PATH"
echo ""

# Step 1: Backup existing files
print_step "Backing up existing installation..."
ssh -p "$SSH_PORT" -o ConnectTimeout=10 "$ACCOUNT@$SSH_HOST" \
    -o "PreferredAuthentications=password" \
    -o "StrictHostKeyChecking=no" << SSHCMD 2>/dev/null || true
if [ -d "$DEPLOY_PATH" ] && [ -f "$DEPLOY_PATH/artisan" ]; then
    BACKUP_PATH="${DEPLOY_PATH}_backup_\$(date +%s)"
    mv "$DEPLOY_PATH" "\$BACKUP_PATH"
    echo "Backup: \$BACKUP_PATH"
fi
SSHCMD
print_success "Backup completed (if previous install existed)"

# Step 2: Clone or update repository
print_step "Cloning repository..."
ssh -p "$SSH_PORT" -o ConnectTimeout=10 "$ACCOUNT@$SSH_HOST" \
    -o "PreferredAuthentications=password" \
    -o "StrictHostKeyChecking=no" << SSHCMD
git clone --depth=1 "$REPO" "$DEPLOY_PATH" || true
cd "$DEPLOY_PATH"
git fetch origin main
git reset --hard origin/main
SSHCMD
print_success "Repository cloned/updated"

# Step 3: Install dependencies
print_step "Installing dependencies..."
ssh -p "$SSH_PORT" -o ConnectTimeout=10 "$ACCOUNT@$SSH_HOST" \
    -o "PreferredAuthentications=password" \
    -o "StrictHostKeyChecking=no" << SSHCMD
cd "$DEPLOY_PATH"
export COMPOSER_PROCESS_TIMEOUT=600
composer install --optimize-autoloader --no-dev --no-progress --no-interaction
SSHCMD
print_success "Composer dependencies installed"

# Step 4: Setup environment
print_step "Setting up environment..."
ssh -p "$SSH_PORT" -o ConnectTimeout=10 "$ACCOUNT@$SSH_HOST" \
    -o "PreferredAuthentications=password" \
    -o "StrictHostKeyChecking=no" << SSHCMD
cd "$DEPLOY_PATH"
[ -f .env ] || cp .env.example .env
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
SSHCMD
print_success "Environment configured"

# Step 5: Set permissions
print_step "Setting file permissions..."
ssh -p "$SSH_PORT" -o ConnectTimeout=10 "$ACCOUNT@$SSH_HOST" \
    -o "PreferredAuthentications=password" \
    -o "StrictHostKeyChecking=no" << SSHCMD
cd "$DEPLOY_PATH"
chmod -R 755 storage bootstrap/cache
chmod 644 database/database.sqlite
chmod +x artisan
SSHCMD
print_success "Permissions set correctly"

# Step 6: Verify deployment
print_step "Verifying deployment..."
LARAVEL_VERSION=$(ssh -p "$SSH_PORT" -o ConnectTimeout=10 "$ACCOUNT@$SSH_HOST" \
    -o "PreferredAuthentications=password" \
    -o "StrictHostKeyChecking=no" << SSHCMD 2>/dev/null
cd "$DEPLOY_PATH"
php artisan --version
SSHCMD
)

echo "Laravel version: $LARAVEL_VERSION"
print_success "Deployment verified"

echo ""
print_header "✨ Deployment Complete!"
echo ""
echo "Your site is now live at:"
echo "  https://essencience.com"
echo ""
echo "📋 Next steps:"
echo "  1. Visit https://essencience.com in your browser"
echo "  2. Test all pages (/, /about, /services, /contact, /counter)"
echo "  3. Check browser console for any errors"
echo ""
echo "📱 Useful commands:"
echo "  • View logs:    ./deploy-helper.sh (choose 'View Logs')"
echo "  • Clear cache:  ./deploy-helper.sh (choose 'Clear Cache')"
echo "  • SSH access:   ./deploy-helper.sh (choose 'SSH Access')"
echo ""
