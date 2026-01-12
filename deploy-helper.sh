#!/bin/bash

# SSH Deployment Helper for Essencience
# This script helps you deploy to Hostinger securely using SSH

set -e

# Color codes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Configuration file
CONFIG_FILE=".env.deployment"

if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}❌ Error: $CONFIG_FILE not found!${NC}"
    echo -e "${YELLOW}Please create $CONFIG_FILE with your Hostinger credentials:${NC}"
    echo ""
    cp .env.deployment.example $CONFIG_FILE
    echo -e "${YELLOW}Created .env.deployment from .env.deployment.example${NC}"
    echo -e "${YELLOW}Edit the file with your actual Hostinger credentials${NC}"
    exit 1
fi

# Load configuration
source "$CONFIG_FILE"

# Validate required variables
if [ -z "$HOSTINGER_SSH_HOST" ] || [ -z "$HOSTINGER_SSH_USER" ]; then
    echo -e "${RED}❌ Missing SSH configuration in $CONFIG_FILE${NC}"
    exit 1
fi

echo -e "${BLUE}🚀 Essencience Deployment Helper${NC}"
echo -e "${BLUE}======================================${NC}"
echo "Server: $HOSTINGER_SSH_HOST:$HOSTINGER_SSH_PORT"
echo "User: $HOSTINGER_SSH_USER"
echo "Path: $HOSTINGER_DEPLOY_PATH"
echo ""

# Determine SSH command
if [ -n "$HOSTINGER_SSH_KEY" ] && [ -f "$HOSTINGER_SSH_KEY" ]; then
    echo -e "${GREEN}Using SSH key authentication${NC}"
    SSH_CMD="ssh -p $HOSTINGER_SSH_PORT -i $HOSTINGER_SSH_KEY $HOSTINGER_SSH_USER@$HOSTINGER_SSH_HOST"
else
    echo -e "${GREEN}Using password authentication${NC}"
    SSH_CMD="ssh -p $HOSTINGER_SSH_PORT $HOSTINGER_SSH_USER@$HOSTINGER_SSH_HOST"
fi

# Menu
echo -e "${BLUE}What would you like to do?${NC}"
echo "1) Deploy from scratch"
echo "2) Pull latest changes from GitHub"
echo "3) Run migrations"
echo "4) Clear cache"
echo "5) View server logs"
echo "6) SSH to server"
echo ""
read -p "Select option (1-6): " OPTION

case $OPTION in
    1)
        echo -e "${YELLOW}Running deployment script on server...${NC}"
        $SSH_CMD "bash <(curl -s https://raw.githubusercontent.com/goodreau/essencience.com/main/deploy.sh)"
        ;;
    2)
        echo -e "${YELLOW}Pulling latest code from GitHub...${NC}"
        $SSH_CMD "cd $HOSTINGER_DEPLOY_PATH && git pull origin main && composer install --no-dev --optimize-autoloader"
        ;;
    3)
        echo -e "${YELLOW}Running migrations...${NC}"
        $SSH_CMD "cd $HOSTINGER_DEPLOY_PATH && php artisan migrate --force"
        ;;
    4)
        echo -e "${YELLOW}Clearing application cache...${NC}"
        $SSH_CMD "cd $HOSTINGER_DEPLOY_PATH && php artisan config:clear && php artisan cache:clear && php artisan route:clear"
        ;;
    5)
        echo -e "${YELLOW}Showing latest logs (last 50 lines):${NC}"
        $SSH_CMD "tail -50 $HOSTINGER_DEPLOY_PATH/storage/logs/laravel.log"
        ;;
    6)
        echo -e "${YELLOW}Connecting to server...${NC}"
        $SSH_CMD
        ;;
    *)
        echo -e "${RED}Invalid option${NC}"
        exit 1
        ;;
esac

echo -e "${GREEN}Done!${NC}"
