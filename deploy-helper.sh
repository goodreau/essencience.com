#!/bin/bash

# Deployment Helper for Essencience
# Retrieves credentials from macOS Keychain for secure SSH access

set -e

# Color codes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Keychain configuration
SERVICE="Essencience-Hostinger"
ACCOUNT="u693982071"
DEFAULT_HOST="147.93.42.19"
DEFAULT_PORT="65002"
DEPLOY_PATH="/home/u693982071/public_html"

# Check if running on macOS
if [[ "$OSTYPE" != "darwin"* ]]; then
    echo -e "${RED}❌ Error: This script requires macOS with Keychain${NC}"
    exit 1
fi

# Helper functions
print_header() {
    echo -e "${BLUE}═══════════════════════════════════════════${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

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

SSH_HOST=$(security find-generic-password -s "${SERVICE}-HOST" -a "$ACCOUNT" -w 2>/dev/null || echo "$DEFAULT_HOST")
SSH_PORT=$(security find-generic-password -s "${SERVICE}-PORT" -a "$ACCOUNT" -w 2>/dev/null || echo "$DEFAULT_PORT")

# SSH command using password from Keychain
SSH_CMD="sshpass -p '$SSH_PASSWORD' ssh -p $SSH_PORT -o ConnectTimeout=10 -o 'PreferredAuthentications=password' -o 'StrictHostKeyChecking=no' $ACCOUNT@$SSH_HOST"

# Menu
print_header "Deployment Helper Menu"
echo ""
echo "1) Deploy from scratch (full deployment)"
echo "2) Pull latest changes from GitHub"
echo "3) Run migrations"
echo "4) Clear cache"
echo "5) View server logs"
echo "6) SSH to server"
echo "0) Exit"
echo ""
read -p "Select option (0-6): " OPTION

case $OPTION in
    1)
        echo -e "${YELLOW}Running deployment script on server...${NC}"
        bash deploy.sh
        ;;
    2)
        echo -e "${YELLOW}Pulling latest code from GitHub...${NC}"
        eval "$SSH_CMD" "cd $DEPLOY_PATH && git pull origin main && composer install --no-dev --optimize-autoloader"
        print_success "Code updated"
        ;;
    3)
        echo -e "${YELLOW}Running migrations...${NC}"
        eval "$SSH_CMD" "cd $DEPLOY_PATH && php artisan migrate --force"
        print_success "Migrations completed"
        ;;
    4)
        echo -e "${YELLOW}Clearing application cache...${NC}"
        eval "$SSH_CMD" "cd $DEPLOY_PATH && php artisan config:clear && php artisan cache:clear && php artisan route:clear"
        print_success "Cache cleared"
        ;;
    5)
        echo -e "${YELLOW}Showing latest logs (last 50 lines):${NC}"
        eval "$SSH_CMD" "tail -50 $DEPLOY_PATH/storage/logs/laravel.log"
        ;;
    6)
        echo -e "${YELLOW}Connecting to server...${NC}"
        eval "$SSH_CMD"
        ;;
    0)
        echo "Goodbye!"
        exit 0
        ;;
    *)
        print_error "Invalid option"
        exit 1
        ;;
esac

echo -e "${GREEN}Done!${NC}"
