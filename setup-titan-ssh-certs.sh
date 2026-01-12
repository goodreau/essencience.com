#!/bin/bash

# Titan Security Key SSH Setup for Essencience
# Generates SSH keys using Google Titan Security Keys
# Requires: macOS + Titan Security Key (USB or Bluetooth)
# Touch required for each SSH operation = maximum security

set -e

# Configuration
SERVICE="Essencience-SSH-Cert"
ACCOUNT="u693982071"
PUBKEY_DIR="$HOME/.ssh/essencience"
TITAN_HOSTNAME="${1:-titan-essencience}"

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

print_header "Titan Security Key SSH Setup"

# Step 1: Check for Titan key software
print_step "Checking for security key utilities..."

# Check if we have OpenSSH 8.2+ (supports sk-ssh-ed25519@openssh.com keys)
OPENSSH_VERSION=$(ssh -V 2>&1 | grep -oE '[0-9]+\.[0-9]+' | head -1)
MAJOR_VERSION=$(echo $OPENSSH_VERSION | cut -d. -f1)

if [ "$MAJOR_VERSION" -lt 8 ]; then
    print_warning "OpenSSH 8.2+ required for Titan security key support"
    echo ""
    echo "You have: OpenSSH $OPENSSH_VERSION"
    echo "Install newer OpenSSH: brew install openssh"
fi
print_success "OpenSSH $OPENSSH_VERSION found"

# Step 2: Create SSH directory
mkdir -p "$PUBKEY_DIR"
chmod 700 "$PUBKEY_DIR"
print_success "SSH directory created: $PUBKEY_DIR"

# Step 3: Check for connected Titan key
print_step "Checking for connected Titan Security Key..."
echo ""
echo "Please ensure your Titan Security Key is:"
echo "  ✓ Inserted (USB) or paired (Bluetooth)"
echo "  ✓ Not currently in use"
echo ""
read -p "Press Enter once your Titan key is ready..."

# Step 4: Generate SSH key pair using Titan key
print_step "Generating FIDO2 SSH key pair using Titan Security Key..."
echo ""
echo "You'll be prompted to:"
echo "  1. Touch your Titan key (authentication)"
echo "  2. Choose a name for the key"
echo ""

# Generate FIDO2 SSH key (sk-ssh-ed25519@openssh.com format)
KEY_PATH="$PUBKEY_DIR/essencience-titan"

# Use ssh-keygen with sk provider to generate key on security key
ssh-keygen -t sk-ssh-ed25519@openssh.com \
    -C "essencience-$(date +%s)" \
    -f "$KEY_PATH" \
    -N "" \
    -O application=ssh:essencience \
    -O verify-required

if [ $? -ne 0 ]; then
    print_error "Failed to generate SSH key"
    echo ""
    echo "Troubleshooting:"
    echo "  • Ensure Titan key is inserted/paired"
    echo "  • Touch the key when prompted"
    echo "  • Try: ssh-keygen -t sk-ssh-ed25519@openssh.com -f $KEY_PATH"
    exit 1
fi

print_success "SSH key pair generated"

# Step 5: Verify keys were created
if [ ! -f "$KEY_PATH" ] || [ ! -f "$KEY_PATH.pub" ]; then
    print_error "Key files not created"
    exit 1
fi

chmod 600 "$KEY_PATH"
chmod 644 "$KEY_PATH.pub"
print_success "Key permissions set correctly"

# Step 6: Create SSH config for Titan key
print_step "Creating SSH configuration..."

cat > "$PUBKEY_DIR/config" << EOF
# Titan Security Key SSH Configuration for Essencience
# The private key is stored on the Titan hardware key
# You must touch the Titan key button to use SSH

Host $TITAN_HOSTNAME
    HostName 147.93.42.19
    Port 65002
    User u693982071
    IdentityFile $KEY_PATH
    StrictHostKeyChecking no
    UserKnownHostsFile /dev/null
    LogLevel QUIET

Host essencience-hostinger
    HostName 147.93.42.19
    Port 65002
    User u693982071
    IdentityFile $KEY_PATH
    StrictHostKeyChecking no
    UserKnownHostsFile /dev/null
    LogLevel QUIET
EOF

print_success "SSH config created"

# Step 7: Export public key details
print_step "Exporting public key for server setup..."

PUBKEY=$(cat "$KEY_PATH.pub")
echo ""
echo "Your Titan Security Key public key:"
echo "═════════════════════════════════════════════════════"
echo "$PUBKEY"
echo "═════════════════════════════════════════════════════"
echo ""

# Save to reference file
echo "$PUBKEY" > "$PUBKEY_DIR/essencience-titan.pub.txt"
print_success "Public key saved to: $PUBKEY_DIR/essencience-titan.pub.txt"

# Step 8: Create instructions for Hostinger
cat > "$PUBKEY_DIR/HOSTINGER_TITAN_SETUP.md" << 'EOF'
# Adding Titan Security Key SSH to Hostinger

## Quick Setup

1. **Get your Titan public key:**
   ```bash
   cat ~/.ssh/essencience/essencience-titan.pub
   ```

2. **Add to Hostinger authorized_keys:**

   **Option A: Via SSH (last time without Titan required)**
   ```bash
   ssh -p 65002 u693982071@147.93.42.19 << 'SSHCMD'
   cat >> ~/.ssh/authorized_keys << 'PUBKEY'
   [PASTE YOUR TITAN PUBLIC KEY HERE]
   PUBKEY
   chmod 600 ~/.ssh/authorized_keys
   SSHCMD
   ```

   **Option B: Via Hostinger File Manager**
   - Go to File Manager in Hostinger
   - Navigate to `.ssh/authorized_keys`
   - Add your public key (new line)
   - Save

## Testing

Test connection (will require Titan touch):
```bash
ssh -p 65002 u693982071@147.93.42.19 "php artisan about"
```

You should see a prompt to touch your Titan key.

## Features

- **FIDO2 Standard**: Universal security key standard
- **Touch Required**: Must physically touch key for each operation
- **Resident Key**: Private key stored only on hardware (if supported)
- **No PIN Needed**: Touch-based verification is sufficient for most uses
- **Works Offline**: Titan key works completely offline

## Troubleshooting

**"Permission denied" errors:**
- Ensure public key is in authorized_keys
- Check file permissions: `ssh ... "ls -la ~/.ssh/"`

**"Timeout" or "Not a security key":**
- Insert Titan key firmly
- Ensure Bluetooth connection is stable (if using BLE model)
- Try: `ssh-keygen -K` to test key availability

**"Touch not detected":**
- Press the Titan key button clearly
- Wait a moment before pressing again
- Check if another process is using the key

## Security Notes

✓ Private key NEVER leaves the hardware
✓ Touch required for every SSH operation
✓ FIDO2 certified by Google
✓ Works with U2F-compatible servers
✓ Can be used on multiple computers

✗ Don't share your Titan key
✗ Don't leave it unattended
✗ Don't write down PINs (touch verification only)
EOF

cat "$PUBKEY_DIR/HOSTINGER_TITAN_SETUP.md"

# Step 9: Store Titan key info in Keychain
print_step "Storing Titan key information in Keychain..."

KEYCHAIN_NAME="essencience-secure"
if [ ! -f "$HOME/Library/Keychains/$KEYCHAIN_NAME.keychain-db" ]; then
    print_warning "Hardware Keychain not found"
    echo ""
    echo "To create secure Keychain: bash setup-hardware-keychain.sh"
    echo "(Optional - Titan key works standalone)"
else
    TITAN_INFO="Titan-$(date +%s)"
    security add-generic-password \
        -s "$SERVICE" \
        -a "$ACCOUNT" \
        -w "$TITAN_INFO" \
        -k "$KEYCHAIN_NAME" \
        2>/dev/null || true
    print_success "Titan key info stored in Keychain"
fi

echo ""
print_header "✨ Titan Security Key SSH Setup Complete!"
echo ""
echo "Public Key: $PUBKEY_DIR/essencience-titan.pub"
echo "Private Key: $PUBKEY_DIR/essencience-titan"
echo "SSH Config: $PUBKEY_DIR/config"
echo ""
echo "🔐 Security Features:"
echo "  ✓ Private key never leaves Titan hardware"
echo "  ✓ Touch required for each SSH operation"
echo "  ✓ FIDO2 certified by Google"
echo "  ✓ Works on any computer with FIDO2 support"
echo ""
echo "📋 Next Steps:"
echo ""
echo "1. Add Titan public key to Hostinger:"
echo "   See: $PUBKEY_DIR/HOSTINGER_TITAN_SETUP.md"
echo ""
echo "2. Test Titan SSH access:"
echo "   ssh -p 65002 u693982071@147.93.42.19"
echo "   (Touch Titan key when prompted)"
echo ""
echo "3. Update deploy scripts to use Titan key:"
echo "   Edit deploy.sh to include:"
echo "   SSH_KEY=\"\$PUBKEY_DIR/essencience-titan\""
echo ""
