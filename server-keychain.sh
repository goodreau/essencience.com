#!/bin/bash

# Server-side Keychain for Hostinger (simple, file-based, AES encryption)
# Installs into ~/.essencience/keychain and manages encrypted secrets.
# Usage:
#   server-keychain.sh init
#   server-keychain.sh set <service> <key> <value>
#   server-keychain.sh get <service> <key>
#   server-keychain.sh list <service>
#   server-keychain.sh delete <service> <key>

set -euo pipefail

BASE_DIR="$HOME/.essencience/keychain"
KEY_FILE="$BASE_DIR/master.key"
SECRETS_DIR="$BASE_DIR/secrets"

mkdir -p "$BASE_DIR" "$SECRETS_DIR"

cmd_init() {
  if [ -f "$KEY_FILE" ]; then
    echo "Keychain already initialized: $KEY_FILE"
    return 0
  fi
  umask 077
  openssl rand -hex 32 > "$KEY_FILE"
  chmod 600 "$KEY_FILE"
  echo "Initialized server keychain at $BASE_DIR"
}

ensure_init() {
  if [ ! -f "$KEY_FILE" ]; then
    echo "Keychain not initialized. Run: $0 init" >&2
    exit 1
  fi
}

enc_path() {
  local service="$1" key="$2"
  echo "$SECRETS_DIR/$service/${key}.enc"
}

cmd_set() {
  ensure_init
  local service="$1" key="$2" value="$3"
  local dir="$SECRETS_DIR/$service"
  mkdir -p "$dir"
  echo -n "$value" | openssl enc -aes-256-cbc -pbkdf2 -salt -pass file:"$KEY_FILE" -out "$(enc_path "$service" "$key")"
  echo "Stored secret: $service/$key"
}

cmd_get() {
  ensure_init
  local service="$1" key="$2"
  local file="$(enc_path "$service" "$key")"
  if [ ! -f "$file" ]; then
    echo "Secret not found: $service/$key" >&2
    exit 1
  fi
  openssl enc -aes-256-cbc -pbkdf2 -d -salt -pass file:"$KEY_FILE" -in "$file"
}

cmd_list() {
  ensure_init
  local service="$1"
  local dir="$SECRETS_DIR/$service"
  if [ ! -d "$dir" ]; then
    echo "No secrets for service: $service" >&2
    exit 1
  fi
  ls -1 "$dir" | sed 's/\.enc$//' || true
}

cmd_delete() {
  ensure_init
  local service="$1" key="$2"
  local file="$(enc_path "$service" "$key")"
  if [ -f "$file" ]; then
    rm -f "$file"
    echo "Deleted: $service/$key"
  else
    echo "Secret not found: $service/$key" >&2
    exit 1
  fi
}

case "${1:-}" in
  init)
    cmd_init;;
  set)
    [ $# -ge 4 ] || { echo "Usage: $0 set <service> <key> <value>"; exit 1; }
    cmd_set "$2" "$3" "$4";;
  get)
    [ $# -ge 3 ] || { echo "Usage: $0 get <service> <key>"; exit 1; }
    cmd_get "$2" "$3";;
  list)
    [ $# -ge 2 ] || { echo "Usage: $0 list <service>"; exit 1; }
    cmd_list "$2";;
  delete)
    [ $# -ge 3 ] || { echo "Usage: $0 delete <service> <key>"; exit 1; }
    cmd_delete "$2" "$3";;
  *)
    echo "Server Keychain"
    echo "Usage: $0 {init|set|get|list|delete}"
    exit 1;;
esac
