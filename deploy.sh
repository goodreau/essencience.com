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
elif [ -f "$HOME/.ssh/essencience/essencience-deploy" ]; then
    print_success "SSH key detected: $HOME/.ssh/essencience/essencience-deploy"
    print_success "Using standard SSH key authentication"
    AUTH_METHOD="key"
    SSH_KEY="$HOME/.ssh/essencience/essencience-deploy"
else
    print_step "No hardware key detected, using password authentication"
    AUTH_METHOD="password"
fi

echo ""

# Optional override via environment (password|key|titan|yubikey)
if [ -n "${AUTH_METHOD_OVERRIDE:-}" ]; then
    echo "Auth override requested: $AUTH_METHOD_OVERRIDE"
    case "$AUTH_METHOD_OVERRIDE" in
        password)
            AUTH_METHOD="password" ; SSH_KEY="" ;;
        key)
            AUTH_METHOD="key" ; SSH_KEY="${SSH_KEY:-$HOME/.ssh/essencience/essencience-deploy}" ;;
        titan)
            AUTH_METHOD="titan" ; SSH_KEY="${SSH_KEY:-$YUBIKEY_PATH/essencience-titan}" ;;
        yubikey)
            AUTH_METHOD="yubikey" ; SSH_KEY="${SSH_KEY:-$YUBIKEY_PATH/essencience-yubikey}" ;;
    esac
fi

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
if [ "$AUTH_METHOD" = "titan" ] || [ "$AUTH_METHOD" = "yubikey" ] || [ "$AUTH_METHOD" = "key" ]; then
    SSH_CMD="ssh -p $SSH_PORT -i $SSH_KEY -o IdentitiesOnly=yes -o ConnectTimeout=10 -o 'StrictHostKeyChecking=no' $ACCOUNT@$SSH_HOST"
    if [ "$AUTH_METHOD" = "titan" ]; then
        echo "Authentication: Titan Security Key (FIDO2)"
    elif [ "$AUTH_METHOD" = "yubikey" ]; then
        echo "Authentication: YubiKey SSH Key"
    else
        echo "Authentication: SSH Key (ed25519)"
    fi
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
    if [ "$AUTH_METHOD" = "password" ]; then
        # For password, use sshpass
        echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << SSHCMD
$cmd
SSHCMD
    else
        # For key-based methods, use direct SSH
        eval "$SSH_CMD" << SSHCMD
$cmd
SSHCMD
    fi
}

# Step 1: Backup existing files (Laravel or WordPress)
print_step "Backing up existing installation (Laravel/WordPress detection)..."
if [ "$AUTH_METHOD" = "password" ]; then
    echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << 'SSHCMD' 2>/dev/null || true
if [ -d "$DEPLOY_PATH" ] && { [ -f "$DEPLOY_PATH/artisan" ] || [ -f "$DEPLOY_PATH/wp-config.php" ] || [ -d "$DEPLOY_PATH/wp-admin" ]; }; then
    BACKUP_PATH="${DEPLOY_PATH}_backup_\$(date +%s)"
    mv "$DEPLOY_PATH" "\$BACKUP_PATH"
    echo "Backup: \$BACKUP_PATH"
fi
SSHCMD
else
    # Hardware key (Titan or YubiKey)
    eval "$SSH_CMD" << 'SSHCMD' 2>/dev/null || true
if [ -d "$DEPLOY_PATH" ] && { [ -f "$DEPLOY_PATH/artisan" ] || [ -f "$DEPLOY_PATH/wp-config.php" ] || [ -d "$DEPLOY_PATH/wp-admin" ]; }; then
    BACKUP_PATH="${DEPLOY_PATH}_backup_\$(date +%s)"
    mv "$DEPLOY_PATH" "\$BACKUP_PATH"
    echo "Backup: \$BACKUP_PATH"
fi
SSHCMD
fi
print_success "Backup completed (Laravel/WordPress if present)"

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
fi
print_success "Repository cloned/updated"

# Step 3a: Install server-side keychain utilities (Hostinger)
print_step "Installing server keychain utilities..."
if [ "$AUTH_METHOD" = "password" ]; then
    echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << 'SSHCMD'
set -e
mkdir -p ~/.essencience/keychain
cat > ~/.essencience/keychain/server-keychain.sh << 'EOF'
#!/bin/bash
set -euo pipefail
BASE_DIR="$HOME/.essencience/keychain"; KEY_FILE="$BASE_DIR/master.key"; SECRETS_DIR="$BASE_DIR/secrets";
mkdir -p "$BASE_DIR" "$SECRETS_DIR"; umask 077
cmd_init(){ [ -f "$KEY_FILE" ] && { echo "Keychain already initialized: $KEY_FILE"; return; }; openssl rand -hex 32 > "$KEY_FILE"; chmod 600 "$KEY_FILE"; echo "Initialized server keychain at $BASE_DIR"; }
ensure_init(){ [ -f "$KEY_FILE" ] || { echo "Keychain not initialized. Run: $0 init" >&2; exit 1; }; }
enc_path(){ echo "$SECRETS_DIR/$1/${2}.enc"; }
cmd_set(){ ensure_init; service="$1"; key="$2"; value="$3"; dir="$SECRETS_DIR/$service"; mkdir -p "$dir"; echo -n "$value" | openssl enc -aes-256-cbc -pbkdf2 -salt -pass file:"$KEY_FILE" -out "$(enc_path "$service" "$key")"; echo "Stored secret: $service/$key"; }
cmd_get(){ ensure_init; service="$1"; key="$2"; file="$(enc_path "$service" "$key")"; [ -f "$file" ] || { echo "Secret not found: $service/$key" >&2; exit 1; }; openssl enc -aes-256-cbc -pbkdf2 -d -salt -pass file:"$KEY_FILE" -in "$file"; }
cmd_list(){ ensure_init; service="$1"; dir="$SECRETS_DIR/$service"; [ -d "$dir" ] || { echo "No secrets for service: $service" >&2; exit 1; }; ls -1 "$dir" | sed 's/\.enc$//' || true; }
cmd_delete(){ ensure_init; service="$1"; key="$2"; file="$(enc_path "$service" "$key")"; [ -f "$file" ] && { rm -f "$file"; echo "Deleted: $service/$key"; } || { echo "Secret not found: $service/$key" >&2; exit 1; }; }
case "${1:-}" in init) cmd_init;; set) [ $# -ge 4 ] || { echo "Usage: $0 set <service> <key> <value>"; exit 1; }; cmd_set "$2" "$3" "$4";; get) [ $# -ge 3 ] || { echo "Usage: $0 get <service> <key>"; exit 1; }; cmd_get "$2" "$3";; list) [ $# -ge 2 ] || { echo "Usage: $0 list <service>"; exit 1; }; cmd_list "$2";; delete) [ $# -ge 3 ] || { echo "Usage: $0 delete <service> <key>"; exit 1; }; cmd_delete "$2" "$3";; *) echo "Server Keychain"; echo "Usage: $0 {init|set|get|list|delete}"; exit 1;; esac
EOF
chmod +x ~/.essencience/keychain/server-keychain.sh
~/.essencience/keychain/server-keychain.sh init || true
SSHCMD
else
    eval "$SSH_CMD" << 'SSHCMD'
set -e
mkdir -p ~/.essencience/keychain
cat > ~/.essencience/keychain/server-keychain.sh << 'EOF'
#!/bin/bash
set -euo pipefail
BASE_DIR="$HOME/.essencience/keychain"; KEY_FILE="$BASE_DIR/master.key"; SECRETS_DIR="$BASE_DIR/secrets";
mkdir -p "$BASE_DIR" "$SECRETS_DIR"; umask 077
cmd_init(){ [ -f "$KEY_FILE" ] && { echo "Keychain already initialized: $KEY_FILE"; return; }; openssl rand -hex 32 > "$KEY_FILE"; chmod 600 "$KEY_FILE"; echo "Initialized server keychain at $BASE_DIR"; }
ensure_init(){ [ -f "$KEY_FILE" ] || { echo "Keychain not initialized. Run: $0 init" >&2; exit 1; }; }
enc_path(){ echo "$SECRETS_DIR/$1/${2}.enc"; }
cmd_set(){ ensure_init; service="$1"; key="$2"; value="$3"; dir="$SECRETS_DIR/$service"; mkdir -p "$dir"; echo -n "$value" | openssl enc -aes-256-cbc -pbkdf2 -salt -pass file:"$KEY_FILE" -out "$(enc_path "$service" "$key")"; echo "Stored secret: $service/$key"; }
cmd_get(){ ensure_init; service="$1"; key="$2"; file="$(enc_path "$service" "$key")"; [ -f "$file" ] || { echo "Secret not found: $service/$key" >&2; exit 1; }; openssl enc -aes-256-cbc -pbkdf2 -d -salt -pass file:"$KEY_FILE" -in "$file"; }
cmd_list(){ ensure_init; service="$1"; dir="$SECRETS_DIR/$service"; [ -d "$dir" ] || { echo "No secrets for service: $service" >&2; exit 1; }; ls -1 "$dir" | sed 's/\.enc$//' || true; }
cmd_delete(){ ensure_init; service="$1"; key="$2"; file="$(enc_path "$service" "$key")"; [ -f "$file" ] && { rm -f "$file"; echo "Deleted: $service/$key"; } || { echo "Secret not found: $service/$key" >&2; exit 1; }; }
case "${1:-}" in init) cmd_init;; set) [ $# -ge 4 ] || { echo "Usage: $0 set <service> <key> <value>"; exit 1; }; cmd_set "$2" "$3" "$4";; get) [ $# -ge 3 ] || { echo "Usage: $0 get <service> <key>"; exit 1; }; cmd_get "$2" "$3";; list) [ $# -ge 2 ] || { echo "Usage: $0 list <service>"; exit 1; }; cmd_list "$2";; delete) [ $# -ge 3 ] || { echo "Usage: $0 delete <service> <key>"; exit 1; }; cmd_delete "$2" "$3";; *) echo "Server Keychain"; echo "Usage: $0 {init|set|get|list|delete}"; exit 1;; esac
EOF
chmod +x ~/.essencience/keychain/server-keychain.sh
~/.essencience/keychain/server-keychain.sh init || true
SSHCMD
fi
print_success "Server keychain ready"

# Step 3: Install dependencies
print_step "Installing dependencies..."
if [ "$AUTH_METHOD" = "password" ]; then
    echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << SSHCMD
cd "$DEPLOY_PATH"
export COMPOSER_PROCESS_TIMEOUT=600
composer install --optimize-autoloader --no-dev --no-progress --no-interaction
SSHCMD
else
    eval "$SSH_CMD" << SSHCMD
cd "$DEPLOY_PATH"
export COMPOSER_PROCESS_TIMEOUT=600
composer install --optimize-autoloader --no-dev --no-progress --no-interaction
SSHCMD
fi
print_success "Composer dependencies installed"

# Step 4: Setup environment
print_step "Setting up environment..."
if [ "$AUTH_METHOD" = "password" ]; then
    echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << SSHCMD
cd "$DEPLOY_PATH"
[ -f .env ] || cp .env.example .env
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
SSHCMD
else
    eval "$SSH_CMD" << SSHCMD
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

# Step 4b: Apply secrets from server keychain to .env (optional)
print_step "Applying secrets from server keychain (if present)..."
if [ "$AUTH_METHOD" = "password" ]; then
        echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << 'SSHCMD'
set -e
cd "$DEPLOY_PATH"
KEYCHAIN="$HOME/.essencience/keychain/server-keychain.sh"
[ -x "$KEYCHAIN" ] || exit 0
update_env(){ key="$1"; val="$2"; grep -q "^${key}=" .env && sed -i "s/^${key}=.*/${key}=${val}/" .env || echo "${key}=${val}" >> .env; }
for k in DB_PASSWORD MAIL_PASSWORD; do
    if "$KEYCHAIN" get app "$k" >/dev/null 2>&1; then
        v=$("$KEYCHAIN" get app "$k")
        update_env "$k" "$v"
    fi
done
SSHCMD
else
        eval "$SSH_CMD" << 'SSHCMD'
set -e
cd "$DEPLOY_PATH"
KEYCHAIN="$HOME/.essencience/keychain/server-keychain.sh"
[ -x "$KEYCHAIN" ] || exit 0
update_env(){ key="$1"; val="$2"; grep -q "^${key}=" .env && sed -i "s/^${key}=.*/${key}=${val}/" .env || echo "${key}=${val}" >> .env; }
for k in DB_PASSWORD MAIL_PASSWORD; do
    if "$KEYCHAIN" get app "$k" >/dev/null 2>&1; then
        v=$("$KEYCHAIN" get app "$k")
        update_env "$k" "$v"
    fi
done
SSHCMD
fi
print_success "Secrets applied (if available)"

# Step 5: Set permissions
print_step "Setting file permissions..."
if [ "$AUTH_METHOD" = "password" ]; then
    echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << SSHCMD
cd "$DEPLOY_PATH"
chmod -R 755 storage bootstrap/cache
chmod 644 database/database.sqlite
chmod +x artisan
SSHCMD
else
    eval "$SSH_CMD" << SSHCMD
cd "$DEPLOY_PATH"
chmod -R 755 storage bootstrap/cache
chmod 644 database/database.sqlite
chmod +x artisan
SSHCMD
fi
print_success "Permissions set correctly"

# Step 6: Verify deployment
print_step "Verifying deployment..."
if [ "$AUTH_METHOD" = "password" ]; then
    LARAVEL_VERSION=$(echo "$SSH_PASSWORD" | sshpass -p "$SSH_PASSWORD" $SSH_CMD << SSHCMD 2>/dev/null
cd "$DEPLOY_PATH"
php artisan --version
SSHCMD
)
else
    LARAVEL_VERSION=$(eval "$SSH_CMD" << SSHCMD 2>/dev/null
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
