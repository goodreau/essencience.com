#!/bin/bash

# SSH Key Setup for Hostinger
# This script adds your SSH public key to the authorized_keys file

set -e

echo "🔑 Setting up SSH key authentication..."

# Create .ssh directory if it doesn't exist
mkdir -p ~/.ssh

# Add the SSH public key
cat >> ~/.ssh/authorized_keys << 'EOF'
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIBfPv4qPSy/k7MaSw+Omlvn/Zu8KNfqgmA3NxrCV2A/T essencience.com
EOF

# Set proper permissions
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh

echo "✅ SSH key added successfully!"
echo ""
echo "You can now connect without a password:"
echo "ssh -i ~/.ssh/hostinger -p 65002 u693982071@147.93.42.19"
