#!/bin/bash

# Secure Hostinger Deployment Script
# Supports:
#   1. Standard Keychain + password authentication
#   2. Hardware key (YubiKey) + SSH certificate authentication
# GitHub: https://github.com/goodreau/essencience.com

set -e

# Configuration
DEPLOY_PATH="/home/u693982071/public_html"
REPO="https://github.com/goodreau/essencience.com.git"
SERVICE="Essencience-Hostinger"
SERVICE_HARDWARE="Essencience-SSH-Cert"
ACCOUNT="u693982071"
DEFAULT_HOST="147.93.42.19"
DEFAULT_PORT="65002"
YUBIKEY_PATH="$HOME/.ssh/essencience"

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

# Detect authentication method
print_header "Detecting Authentication Method"

# Check for Titan Security Key first
TITAN_KEY="$YUBIKEY_PATH/essencience-titan"
if [ -f "$TITAN_KEY" ]; then
    print_success "Titan Security Key detected"
    print_success "Using Titan SSH key authentication (FIDO2)"
    AUTH_METHOD="titan"
    SSH_KEY="$TITAN_KEY"
# Check for YubiKey
elif [ -f "$YUBIKEY_PATH/essencience-yubikey" ]; then
    YUBIKEY_SERIAL=$(security find-generic-password -s "$SERVICE_HARDWARE" -a "$ACCOUNT" -w 2>/dev/null || echo "")
    if [ -n "$YUBIKEY_SERIAL" ]; then
        print_success "YubiKey detected (Serial: $YUBIKEY_SERIAL)"
        print_success "Using YubiKey SSH certificate authentication"
        AUTH_METHOD="yubikey"
        SSH_KEY="$YUBIKEY_PATH/essencience-yubikey"
    else
        print_step "YubiKey key found but not configured in Keychain"
        AUTH_METHOD="password"
    fi
else
    print_step "No hardware key detected, using password authentication"
    AUTH_METHOD="password"
fi

echo ""

# Retrieve credentials
if [ "$AUTH_METHOD" = "password" ]; then
    print_step "Retrieving password from Keychain..."
    SSH_PASSWORD=$(security find-generic-password -s "$SERVICE" -a "$ACCOUNT" -w 2>/dev/null)
    if [ -z "$SSH_PASSWORD" ]; then
        print_error "Credentials not found in Keychain"
        echo ""
        echo "For password authentication: bash setup-keychain.sh"
        echo "For Titan Security Key:      bash setup-titan-ssh-certs.sh"
        echo "For YubiKey:                 bash setup-hardware-ssh-certs.sh"
        exit 1
    fi
    print_success "Password retrieved from Keychain"
fi

# Get optional SSH host/port from Keychain or use defaults
SSH_HOST=$(security find-generic-password -s "${SERVICE}-HOST" -a "$ACCOUNT" -w 2>/dev/null || echo "$DEFAULT_HOST")
SSH_PORT=$(security find-generic-password -s "${SERVICE}-PORT" -a "$ACCOUNT" -w 2>/dev/null || echo "$DEFAULT_PORT")

# Build SSH command based on authentication method
if [ "$AUTH_METHOD" = "titan" ]; then
    SSH_CMD="ssh -p $SSH_PORT -i $SSH_KEY -o ConnectTimeout=10 -o 'StrictHostKeyChecking=no' $ACCOUNT@$SSH_HOST"
    echo "Authentication: Titan Security Key (FIDO2)"
elif [ "$AUTH_METHOD" = "yubikey" ]; then
    SSH_CMD="ssh -p $SSH_PORT -i $SSH_KEY -o ConnectTimeout=10 -o 'StrictHostKeyChecking=no' $ACCOUNT@$SSH_HOST"
    echo "Authentication: YubiKey SSH Key"
else
    SSH_CMD="ssh -p $SSH_PORT -o ConnectTimeout=10 -o 'PreferredAuthentications=password' -o 'StrictHostKeyChecking=no' $ACCOUNT@$SSH_HOST"
    echo "Authentication: Password (from Keychain)"
fi

print_header "🚀 Deploying Essencience to Hostinger"

echo "Host: $SSH_HOST"
echo "Port: $SSH_PORT"
echo "User: $ACCOUNT"
echo "Path: $DEPLOY_PATH"
echo ""

# SSH function that handles both auth methods
run_ssh() {
    local cmd="$1"
    if [ "$AUTH_METHOD" = "hardware" ]; then
        # For hardware key, use the key directly
        eval "$SSH_CMD" << SSHCMD
$cmd
SSHCMD
    else
        # For password, use sshpass
        echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << SSHCMD
$cmd
SSHCMD
    fi
}

# Step 1: Backup existing files
print_step "Backing up existing installation..."
if [ "$AUTH_METHOD" = "hardware" ]; then
# Step 1: Backup existing files
print_step "Backing up existing installation..."
if [ "$AUTH_METHOD" = "password" ]; then
    echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << SSHCMD 2>/dev/null || true
if [ -d "$DEPLOY_PATH" ] && [ -f "$DEPLOY_PATH/artisan" ]; then
    BACKUP_PATH="${DEPLOY_PATH}_backup_\$(date +%s)"
    mv "$DEPLOY_PATH" "\$BACKUP_PATH"
    echo "Backup: \$BACKUP_PATH"
fi
SSHCMD
else
    # Hardware key (Titan or YubiKey)
    eval "$SSH_CMD" << SSHCMD 2>/dev/null || true
if [ -d "$DEPLOY_PATH" ] && [ -f "$DEPLOY_PATH/artisan" ]; then
    BACKUP_PATH="${DEPLOY_PATH}_backup_\$(date +%s)"
    mv "$DEPLOY_PATH" "\$BACKUP_PATH"
    echo "Backup: \$BACKUP_PATH"
fi
SSHCMD
fi
print_success "Backup completed (if previous install existed)"

# Step 2: Clone or update repository
print_step "Cloning repository..."
if [ "$AUTH_METHOD" = "password" ]; then
    echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << SSHCMD
git clone --depth=1 "$REPO" "$DEPLOY_PATH" || true
cd "$DEPLOY_PATH"
git fetch origin main
git reset --hard origin/main
SSHCMD
else
    eval "$SSH_CMD" << SSHCMD
git clone --depth=1 "$REPO" "$DEPLOY_PATH" || true
cd "$DEPLOY_PATH"
git fetch origin main
git reset --hard origin/main
SSHCMD
else
    echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << SSHCMD
git clone --depth=1 "$REPO" "$DEPLOY_PATH" || true
cd "$DEPLOY_PATH"
git fetch origin main
git reset --hard origin/main
SSHCMD
fi
print_success "Repository cloned/updated"

# Step 3: Install dependencies
print_step "Installing dependencies..."
if [ "$AUTH_METHOD" = "hardware" ]; then
    eval "$SSH_CMD" << SSHCMD
cd "$DEPLOY_PATH"
export COMPOSER_PROCESS_TIMEOUT=600
composer install --optimize-autoloader --no-dev --no-progress --no-interaction
SSHCMD
else
    echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << SSHCMD
cd "$DEPLOY_PATH"
export COMPOSER_PROCESS_TIMEOUT=600
composer install --optimize-autoloader --no-dev --no-progress --no-interaction
SSHCMD
fi
print_success "Composer dependencies installed"

# Step 4: Setup environment
print_step "Setting up environment..."
if [ "$AUTH_METHOD" = "hardware" ]; then
    eval "$SSH_CMD" << SSHCMD
cd "$DEPLOY_PATH"
[ -f .env ] || cp .env.example .env
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
SSHCMD
else
    echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << SSHCMD
cd "$DEPLOY_PATH"
[ -f .env ] || cp .env.example .env
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
SSHCMD
fi
print_success "Environment configured"

# Step 5: Set permissions
print_step "Setting file permissions..."
if [ "$AUTH_METHOD" = "hardware" ]; then
    eval "$SSH_CMD" << SSHCMD
cd "$DEPLOY_PATH"
chmod -R 755 storage bootstrap/cache
chmod 644 database/database.sqlite
chmod +x artisan
SSHCMD
else
    echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << SSHCMD
cd "$DEPLOY_PATH"
chmod -R 755 storage bootstrap/cache
chmod 644 database/database.sqlite
chmod +x artisan
SSHCMD
fi
print_success "Permissions set correctly"

# Step 6: Verify deployment
print_step "Verifying deployment..."
if [ "$AUTH_METHOD" = "hardware" ]; then
    LARAVEL_VERSION=$(eval "$SSH_CMD" << SSHCMD 2>/dev/null
cd "$DEPLOY_PATH"
php artisan --version
SSHCMD
)
else
    LARAVEL_VERSION=$(echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << SSHCMD 2>/dev/null
cd "$DEPLOY_PATH"
php artisan --version
SSHCMD
)
fi

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
