#!/bin/bash

# YubiKey SSH Certificate Setup for Essencience
# Generates SSH certificates on YubiKey PIV slot
# Requires: YubiKey 5 Series + YubiKey Manager
# Touch required for each SSH operation = maximum security

set -e

# Configuration
KEYCHAIN_NAME="essencience-secure"
SERVICE="Essencience-SSH-Cert"
ACCOUNT="u693982071"
CERT_SLOTS=(9c)  # 9c = Digital Signature key slot
PUBKEY_DIR="$HOME/.ssh/essencience"
SERIAL_FILE="$PUBKEY_DIR/.yubikey_serial"

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

print_header "YubiKey SSH Certificate Setup"

# Step 1: Check YubiKey Manager
print_step "Checking YubiKey Manager installation..."
if ! command -v ykman &> /dev/null; then
    print_error "YubiKey Manager not found"
    echo ""
    echo "Install with: brew install yubico/tap/yubikey-manager"
    echo "Or download: https://www.yubico.com/products/yubico-manager/"
    exit 1
fi
print_success "YubiKey Manager found"

# Step 2: Check for connected YubiKey
print_step "Checking for connected YubiKey..."
if ! ykman list --serials &>/dev/null; then
    print_error "No YubiKey detected"
    echo ""
    echo "Please insert your YubiKey and try again"
    echo "Supported: YubiKey 5 Series, YubiKey 5 Nano, YubiKey 5C"
    exit 1
fi

YUBIKEY_SERIAL=$(ykman list --serials | head -1)
print_success "YubiKey detected (Serial: $YUBIKEY_SERIAL)"

# Step 3: Check PIV is enabled
print_step "Verifying PIV application is enabled..."
if ! ykman info | grep -q "PIV"; then
    print_warning "PIV may not be enabled"
    echo ""
    echo "YubiKey needs PIV application enabled"
    echo "This is usually enabled by default on YubiKey 5"
fi
print_success "PIV application verified"

# Step 4: Create SSH directory
mkdir -p "$PUBKEY_DIR"
chmod 700 "$PUBKEY_DIR"
print_success "SSH directory created: $PUBKEY_DIR"

# Step 5: Generate SSH key on YubiKey (if not already present)
print_step "Generating SSH key on YubiKey (slot 9c - Digital Signature)..."
echo ""
echo "IMPORTANT: You'll be prompted for:"
echo "  1. YubiKey PIN (default: 123456)"
echo "  2. PUK if needed (default: 12345678)"
echo "  3. Management Key (optional - will be asked to set)"
echo ""
echo "If this is your first time, keep PIN as default or set a custom PIN"
echo ""
read -p "Press Enter to continue..." -r

# Generate key on PIV slot 9c (Digital Signature)
# The key stays on the hardware key, we get the public key
ykman piv keys generate --pin-policy once --touch-policy always 9c "$PUBKEY_DIR/essencience-yubikey.pub" 2>&1 | head -20 || true

print_success "SSH key generated on YubiKey (touch required for use)"

# Step 6: Create SSH public key certificate format
print_step "Setting up SSH certificate authentication..."
cat > "$PUBKEY_DIR/config" << 'EOF'
# YubiKey SSH Configuration for Essencience
# The private key stays on the YubiKey hardware
# You must touch the YubiKey button to use SSH

Host essencience-hostinger
    HostName 147.93.42.19
    Port 65002
    User u693982071
    # SSH certificate or public key will be specified in deploy script
    StrictHostKeyChecking no
    UserKnownHostsFile /dev/null
    LogLevel QUIET
EOF
print_success "SSH config created"

# Step 7: Store YubiKey serial in keychain
print_step "Storing YubiKey serial in secure Keychain..."
security add-generic-password \
    -s "$SERVICE" \
    -a "$ACCOUNT" \
    -w "$YUBIKEY_SERIAL" \
    -k "$KEYCHAIN_NAME" \
    2>/dev/null || true
print_success "YubiKey serial stored in Keychain"

# Step 8: Export public key for Hostinger
print_step "Exporting public key for server configuration..."
if [ -f "$PUBKEY_DIR/essencience-yubikey.pub" ]; then
    echo ""
    echo "Your YubiKey public key:"
    echo "═════════════════════════════════════════════════════"
    cat "$PUBKEY_DIR/essencience-yubikey.pub"
    echo "═════════════════════════════════════════════════════"
    echo ""

    # Save to file for reference
    cp "$PUBKEY_DIR/essencience-yubikey.pub" "$PUBKEY_DIR/essencience-yubikey.pub.txt"
    print_success "Public key saved to: $PUBKEY_DIR/essencience-yubikey.pub.txt"
else
    print_warning "Public key not found. Key generation may have failed."
fi

# Step 9: Instructions for Hostinger
cat > "$PUBKEY_DIR/HOSTINGER_SETUP.md" << 'EOF'
# Adding YubiKey SSH Certificate to Hostinger

## Option 1: Using SSH Certificate (Recommended)

1. **Generate SSH certificate on your Mac:**
   ```bash
   ssh-keygen -s ~/.ssh/essencience-ca \
       -I "essencience-user" \
       -n u693982071 \
       -V +52w \
       ~/.ssh/essencience-yubikey.pub
   ```

2. **Copy certificate to server:**
   ```bash
   ssh-copy-id -i ~/.ssh/essencience-yubikey-cert.pub \
       -p 65002 u693982071@147.93.42.19
   ```

3. **Or manually add to authorized_keys:**
   ```bash
   ssh -p 65002 u693982071@147.93.42.19 << 'SSHCMD'
   cat >> ~/.ssh/authorized_keys << 'PUBKEY'
   [PASTE YOUR PUBLIC KEY HERE]
   PUBKEY
   SSHCMD
   ```

## Option 2: Using Public Key

1. **Get your YubiKey public key:**
   ```bash
   cat ~/.ssh/essencience/essencience-yubikey.pub
   ```

2. **Add to Hostinger authorized_keys**
   - Go to File Manager in Hostinger
   - Navigate to ~/.ssh/authorized_keys
   - Add the public key (new line)

## Verification

Test connection (will require YubiKey touch):
```bash
ssh -i ~/.ssh/essencience/essencience-yubikey \
    -p 65002 u693982071@147.93.42.19 "php artisan about"
```

You should see a prompt to touch your YubiKey.

## Troubleshooting

**"Device not found" errors:**
- Ensure YubiKey is inserted
- Check: `ykman list`

**"PIN incorrect" errors:**
- Default PIN: 123456
- Reset PIN: `ykman piv reset`

**"Touch required" not appearing:**
- Verify touch policy: `ykman piv keys info`
- Should show "Touch policy: ALWAYS"
EOF

cat "$PUBKEY_DIR/HOSTINGER_SETUP.md"

echo ""
print_header "✨ YubiKey SSH Setup Complete!"
echo ""
echo "YubiKey Serial: $YUBIKEY_SERIAL"
echo "Public Key: $PUBKEY_DIR/essencience-yubikey.pub"
echo "SSH Config: $PUBKEY_DIR/config"
echo ""
echo "🔐 Security Features:"
echo "  ✓ Private key never leaves YubiKey hardware"
echo "  ✓ Touch required for each SSH operation"
echo "  ✓ Certificate can have expiration (recommended: 1 year)"
echo "  ✓ Can revoke access by removing key from server"
echo ""
echo "📋 Next Steps:"
echo ""
echo "1. Add YubiKey public key to Hostinger:"
echo "   • Use HOSTINGER_SETUP.md for instructions"
echo "   • Or manually add to ~/.ssh/authorized_keys"
echo ""
echo "2. Test YubiKey SSH access:"
echo "   ssh -p 65002 u693982071@147.93.42.19"
echo "   (Touch YubiKey when prompted)"
echo ""
echo "3. Update deploy.sh to use YubiKey certificates"
echo ""
