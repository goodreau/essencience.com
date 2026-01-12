#!/bin/bash

# macOS Keychain Credential Storage Script
# Stores Hostinger SSH credentials securely in system Keychain
# Credentials are encrypted and never exposed in environment files or scripts

set -e

echo "🔐 macOS Keychain Setup for Essencience Deployment"
echo "=================================================="
echo ""

# Check if running on macOS
if [[ "$OSTYPE" != "darwin"* ]]; then
    echo "❌ Error: This script requires macOS (Keychain)"
    exit 1
fi

# Service name for Keychain
SERVICE="Essencience-Hostinger"
ACCOUNT="u693982071"

echo "This script will store your Hostinger credentials in macOS Keychain."
echo "Your password will be encrypted and never stored in plain text."
echo ""

# Remove existing credentials if present
echo "Checking for existing credentials..."
if security find-generic-password -s "$SERVICE" -a "$ACCOUNT" >/dev/null 2>&1; then
    echo "Found existing credentials. Updating..."
    security delete-generic-password -s "$SERVICE" -a "$ACCOUNT" 2>/dev/null || true
fi

# Prompt for SSH password
read -sp "Enter your Hostinger SSH password: " SSH_PASSWORD
echo ""
read -sp "Confirm password: " SSH_PASSWORD_CONFIRM
echo ""

if [ "$SSH_PASSWORD" != "$SSH_PASSWORD_CONFIRM" ]; then
    echo "❌ Passwords do not match. Exiting."
    exit 1
fi

# Store in Keychain
security add-generic-password \
    -s "$SERVICE" \
    -a "$ACCOUNT" \
    -w "$SSH_PASSWORD" \
    -T "/usr/bin/ssh" \
    -T "/usr/bin/ssh-keyscan" \
    2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Password stored securely in Keychain"
    echo ""
    echo "Service: $SERVICE"
    echo "Account: $ACCOUNT"
    echo ""
    echo "Your credentials are now available to deployment scripts."
    echo "They will be automatically retrieved when needed."
else
    echo "❌ Failed to store password in Keychain"
    exit 1
fi

# Optional: Also store SSH port
read -p "Store SSH port (default 65002)? [y/n] " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "SSH Port: " -i "65002" SSH_PORT
    security add-generic-password \
        -s "${SERVICE}-PORT" \
        -a "$ACCOUNT" \
        -w "$SSH_PORT" \
        2>/dev/null && echo "✅ SSH port stored"
fi

# Optional: Also store SSH host
read -p "Store SSH host (default 147.93.42.19)? [y/n] " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "SSH Host: " -i "147.93.42.19" SSH_HOST
    security add-generic-password \
        -s "${SERVICE}-HOST" \
        -a "$ACCOUNT" \
        -w "$SSH_HOST" \
        2>/dev/null && echo "✅ SSH host stored"
fi

echo ""
echo "🎉 Setup complete!"
echo ""
echo "You can now use the deployment scripts:"
echo "  ./deploy.sh"
echo "  ./deploy-helper.sh"
echo ""
echo "Your password will be retrieved from Keychain automatically."
echo "To view stored credentials in Keychain:"
echo "  open /Applications/Utilities/Keychain\\ Access.app"
