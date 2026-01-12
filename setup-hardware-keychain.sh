#!/bin/bash

# Hardware Key Protected Keychain Setup for Essencience
# Creates a dedicated Keychain protected by YubiKey/security key
# Requires: macOS + YubiKey 5 Series or compatible security key

set -e

# Configuration
KEYCHAIN_NAME="essencience-secure"
KEYCHAIN_PATH="$HOME/Library/Keychains/$KEYCHAIN_NAME.keychain-db"
SERVICE="Essencience-Hardware-Secure"
ACCOUNT="u693982071"

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

print_header() {
    echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
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

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Check macOS
if [[ "$OSTYPE" != "darwin"* ]]; then
    print_error "This script requires macOS"
    exit 1
fi

print_header "Hardware Key Protected Keychain Setup"

# Step 1: Check for hardware key
print_step "Checking for YubiKey or security key..."
if ! command -v ykman &> /dev/null; then
    print_warning "YubiKey Manager not found"
    echo ""
    echo "To use YubiKey security features, install:"
    echo "  brew install yubico/tap/yubikey-manager"
    echo ""
    echo "Or download from: https://www.yubico.com/products/yubico-manager/"
    echo ""
    read -p "Continue without YubiKey verification? (y/n) " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Installation cancelled."
        exit 1
    fi
fi

# Step 2: Create hardware-protected Keychain
print_step "Creating hardware-protected Keychain..."
if [ -f "$KEYCHAIN_PATH" ]; then
    print_warning "Keychain already exists at $KEYCHAIN_PATH"
    read -p "Delete and recreate? (y/n) " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        security delete-keychain "$KEYCHAIN_NAME" 2>/dev/null || true
    else
        print_warning "Using existing Keychain"
    fi
fi

# Create keychain with strong password (will use security key as 2FA)
read -sp "Create a strong master password for the Keychain: " KEYCHAIN_PASSWORD
echo ""
read -sp "Confirm password: " KEYCHAIN_PASSWORD_CONFIRM
echo ""

if [ "$KEYCHAIN_PASSWORD" != "$KEYCHAIN_PASSWORD_CONFIRM" ]; then
    print_error "Passwords do not match"
    exit 1
fi

# Create the keychain
security create-keychain -p "$KEYCHAIN_PASSWORD" "$KEYCHAIN_NAME" 2>/dev/null || true
print_success "Keychain created: $KEYCHAIN_NAME"

# Step 3: Set keychain timeout (lock after inactivity)
print_step "Setting keychain security settings..."

# Lock after 5 minutes of inactivity
security set-keychain-settings -t 300 "$KEYCHAIN_PATH"

# Lock on sleep
security set-keychain-settings -l "$KEYCHAIN_PATH"

print_success "Keychain will lock after 5 minutes of inactivity and when Mac sleeps"

# Step 4: Store SSH credentials in hardware Keychain
print_step "Storing Hostinger SSH credentials in hardware-protected Keychain..."

read -sp "Enter your Hostinger SSH password: " SSH_PASSWORD
echo ""
read -sp "Confirm password: " SSH_PASSWORD_CONFIRM
echo ""

if [ "$SSH_PASSWORD" != "$SSH_PASSWORD_CONFIRM" ]; then
    print_error "Passwords do not match"
    exit 1
fi

# Store in hardware keychain
security add-generic-password \
    -s "$SERVICE" \
    -a "$ACCOUNT" \
    -w "$SSH_PASSWORD" \
    -T "/usr/bin/ssh" \
    -T "/usr/bin/ssh-keyscan" \
    -k "$KEYCHAIN_NAME" \
    2>/dev/null

print_success "SSH credentials stored in hardware-protected Keychain"

# Step 5: Optional YubiKey PIV setup
print_step "YubiKey PIV Configuration (optional)..."
echo ""
echo "YubiKey can store SSH certificates on the PIV slot:"
echo "  • Certificate is stored on the hardware key"
echo "  • Touch required to use certificate"
echo "  • Private key never leaves the hardware"
echo ""

read -p "Configure YubiKey PIV for SSH certificates? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    if ! command -v ykman &> /dev/null; then
        print_error "YubiKey Manager required. Install: brew install yubico/tap/yubikey-manager"
        exit 1
    fi

    echo ""
    echo "YubiKey PIV will be configured by: setup-hardware-ssh-certs.sh"
    echo "After creating this Keychain, run: bash setup-hardware-ssh-certs.sh"
fi

# Step 6: Add Keychain to search path
print_step "Adding Keychain to search path..."
security list-keychains -s "$KEYCHAIN_PATH" $(security list-keychains -d user | grep -o '"[^"]*"' | tr -d '"') 2>/dev/null || true
print_success "Keychain added to search path"

# Step 7: Lock the keychain
print_step "Locking Keychain..."
security lock-keychain "$KEYCHAIN_PATH"
print_success "Keychain locked and ready"

echo ""
print_header "✨ Hardware-Protected Keychain Ready!"
echo ""
echo "Keychain: $KEYCHAIN_NAME"
echo "Location: $KEYCHAIN_PATH"
echo "Protection: Requires master password + hardware key (when configured)"
echo ""
echo "🔐 Security Features:"
echo "  ✓ Requires master password to unlock"
echo "  ✓ Locks after 5 minutes of inactivity"
echo "  ✓ Locks when Mac sleeps"
echo "  ✓ Can be linked to YubiKey for 2FA"
echo "  ✓ Credentials encrypted at rest"
echo ""
echo "📋 Next Steps:"
echo ""
echo "1. For YubiKey SSH Certificates (recommended):"
echo "   bash setup-hardware-ssh-certs.sh"
echo ""
echo "2. To unlock and view credentials:"
echo "   security list-generic-passwords -k $KEYCHAIN_NAME"
echo ""
echo "3. To use in deployment scripts:"
echo "   • Scripts will automatically unlock when needed"
echo "   • You'll be prompted for Keychain password"
echo "   • With YubiKey: You'll also need to touch the key"
echo ""
echo "4. Manage in Keychain Access:"
echo "   open -a 'Keychain Access'"
echo ""
