#!/bin/bash
# SSH to essencience.com using password from keychain

PASSWORD=$(security find-generic-password -a u693982071 -s "essencience SSH" -w 2>/dev/null)

if [ -z "$PASSWORD" ]; then
    echo "Error: Password not found in keychain"
    exit 1
fi

# Use sshpass to provide password automatically
if ! command -v sshpass &> /dev/null; then
    echo "Installing sshpass..."
    brew install hudochenkov/sshpass/sshpass
fi

sshpass -p "$PASSWORD" ssh essencience "$@"
