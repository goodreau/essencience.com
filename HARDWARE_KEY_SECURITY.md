# Hardware Key Security Setup for Essencience

## Overview

This guide sets up **enterprise-grade security** for essencience.com using:

- **Hardware Security Keys** (YubiKey 5 Series or Titan Security Keys)
- **Separate secure Keychain** that requires password + hardware key
- **SSH certificate/key authentication** with mandatory hardware key touch
- **Zero plaintext credentials** stored on Mac (all encrypted or on hardware)

---

## Why Hardware Keys?

| Traditional | Hardware Key |
|-------------|--------------|
| ❌ Passwords stored in Keychain (software) | ✅ Touch required, key never leaves hardware |
| ❌ Hacked if Mac is compromised | ✅ Safe even if Mac is compromised |
| ❌ No audit trail of access | ✅ Every operation logged on hardware |
| ❌ Can be stolen/copied | ✅ Unique to physical device, non-cloneable |
| ❌ Vulnerable to malware | ✅ Malware cannot access hardware keys |

---

## Hardware Requirements

**Option 1: Titan Security Keys (Recommended)**
- Google Titan Security Key (USB-A or BLE/USB-C)
- Works with FIDO2/U2F standard
- **No additional software needed** (uses standard OpenSSH)
- **Price:** $30-50 USD

**Option 2: YubiKey 5 Series**
- YubiKey 5 (USB-A)
- YubiKey 5 Nano (compact USB-A)
- YubiKey 5C (USB-C)
- YubiKey 5Ci (Lightning + USB-C)
- Requires YubiKey Manager software
- **Price:** $45-80 USD

**Also Supported:**
- YubiKey 4 Series (legacy features)
- OnlyKey
- Somu
- Any FIDO2-certified security key

---

## Setup Steps

### Step 1: Choose Your Security Key

**For Titan Security Key (simplest):**
- No software installation needed
- Works with OpenSSH 8.2+ (pre-installed on macOS)
- FIDO2 certified by Google
- Lower cost, simpler setup

**For YubiKey 5 Series (more features):**
- Advanced PIV certificate support
- YubiKey Manager required
- More customization options
- Better for complex deployments

### Step 2: Install Prerequisites

**For YubiKey users:**
```bash
brew install yubico/tap/yubikey-manager
ykman version
```

**For Titan users:** No additional installation needed! (OpenSSH 8.2+ built-in)

### Step 3: Create Hardware-Protected Keychain

```bash
bash setup-hardware-keychain.sh
```

**What happens:**
1. Creates a new Keychain named `essencience-secure`
2. Requires strong master password
3. Automatically locks after 5 minutes
4. Stores SSH credentials encrypted

**Prompts:**
- Master password for Keychain (use something strong!)
- Hostinger SSH password

### Step 4: Configure Your Hardware Security Key

**For Titan Security Key (recommended):**
```bash
bash setup-titan-ssh-certs.sh
```

**What happens:**
1. Generates FIDO2 SSH key using your Titan key
2. Private key stored **only on the hardware**
3. Exports public key for server
4. Creates SSH configuration automatically
5. Stores Titan info in Keychain

**Prompts:**
- Touch your Titan key when prompted
- Choose key naming options

**For YubiKey 5:**
```bash
bash setup-hardware-ssh-certs.sh
```

**What happens:**
1. Generates SSH key **on the YubiKey hardware** (not on your Mac)
2. Private key **never leaves the hardware**
3. Exports public key for server
4. Stores YubiKey serial in Keychain

**Prompts:**
- YubiKey PIN (default: 123456)
- Optional: Custom management key
- Touch the YubiKey when requested

### Step 5: Add Public Key to Hostinger

**Get your public key:**

For Titan:
```bash
cat ~/.ssh/essencience/essencience-titan.pub
```

For YubiKey:
```bash
cat ~/.ssh/essencience/essencience-yubikey.pub
```

**Add to Hostinger:**

Option A - Via SSH (last time without hardware key):
```bash
ssh -p 65002 u693982071@147.93.42.19 << 'SSHCMD'
cat >> ~/.ssh/authorized_keys << 'PUBKEY'
[PASTE PUBLIC KEY HERE]
PUBKEY
SSHCMD
```

Option B - Via Hostinger File Manager:
1. Go to File Manager
2. Navigate to `.ssh/authorized_keys`
3. Edit and add your public key (new line)
4. Save



### Step 5: Test YubiKey SSH Access

```bash
ssh -p 65002 u693982071@147.93.42.19
# Touch YubiKey when prompted
```

You should see:
```
Please touch the YKPIV application on your YubiKey
[wait for touch detection]
```

---

## Daily Usage

### Deploy to Production

```bash
bash deploy.sh
```

**You'll be prompted for:**
1. Keychain password (master password you set)
2. Touch YubiKey (when SSH operations occur)

### Interactive Deployment Menu

```bash
bash deploy-helper.sh
```

Options:
- Deploy from scratch
- Pull latest code
- Run migrations
- Clear cache
- View logs
- SSH access

---

## Security Architecture

```
┌─────────────────────────────────────────────┐
│         Essencience Deployment              │
└────────────────┬────────────────────────────┘
                 │
        ┌────────┴──────────┐
        │                   │
   ┌────▼─────┐      ┌─────▼────────┐
   │  Keychain│      │  SSH via     │
   │ Protected│      │  YubiKey     │
   │  Creds   │      │  (cert auth) │
   └────┬─────┘      └─────┬────────┘
        │                   │
   ┌────▼───────────┐  ┌────▼──────────────┐
   │ Needs:         │  │ Needs:             │
   │ • Keychain PW  │  │ • YubiKey Insert   │
   │ • Keychain     │  │ • YubiKey Touch    │
   │   Unlock       │  │ • YubiKey PIN      │
   └────────────────┘  └────────────────────┘
        ↓                      ↓
   ┌────────────────────────────────┐
   │   Hostinger Server SSH Access  │
   │   (fully authenticated)        │
   └────────────────────────────────┘
```

---

## File Structure

```
~/.ssh/essencience/
├── essencience-yubikey.pub          # Public key (exported from YubiKey)
├── essencience-yubikey.pub.txt      # Copy of public key
├── config                            # SSH host configuration
└── HOSTINGER_SETUP.md               # YubiKey-specific instructions

~/Library/Keychains/
└── essencience-secure.keychain-db   # Hardware-protected credentials

~/essencience-repo/
├── setup-hardware-keychain.sh       # Initialize secure Keychain
├── setup-hardware-ssh-certs.sh      # Configure YubiKey SSH
├── deploy.sh                        # Deployment (uses YubiKey + Keychain)
├── deploy-helper.sh                 # Interactive menu
└── HARDWARE_KEY_SECURITY.md         # This file
```

---

## YubiKey PIN & Management Key

### Default Credentials

```
YubiKey PIN:           123456
YubiKey PUK:           12345678
Management Key:        [blank by default]
```

### Change PIN (Recommended)

```bash
ykman piv change-pin
```

### Reset PIN (if forgotten)

```bash
# Using PUK (Personal Unblock Key)
ykman piv change-pin --unblock

# Or full reset (erases all PIV data)
ykman piv reset
```

---

## Advanced Features

### Set PIN Policy

Require PIN for each operation:
```bash
ykman piv keys generate --pin-policy always 9c essencience-yubikey.pub
```

### Set Touch Policy

Require physical touch for each operation:
```bash
ykman piv keys generate --touch-policy always 9c essencience-yubikey.pub
```

### Multiple Keys on One YubiKey

YubiKey has multiple slots (9a, 9c, 9d, 9e):
- 9a: Authentication
- 9c: **Digital Signature** (our SSH key)
- 9d: Key Management
- 9e: Card Authentication

### Backup YubiKey

Recommended: Use 2 YubiKeys, add both public keys to servers
```bash
# Insert YubiKey #1
bash setup-hardware-ssh-certs.sh
cat ~/.ssh/essencience/essencience-yubikey.pub >> backup_keys.txt

# Insert YubiKey #2
bash setup-hardware-ssh-certs.sh
cat ~/.ssh/essencience/essencience-yubikey.pub >> backup_keys.txt

# Add both to Hostinger
```

---

## Troubleshooting

### YubiKey Not Detected

```bash
# Check if YubiKey is recognized
ykman list

# If no output, YubiKey may not be inserted
# Try: System Preferences → Security & Privacy → USB Accessories
```

### PIN Incorrect

```bash
# PIN count: default 3 tries before lock
# If locked, use PUK to reset

ykman piv change-pin --unblock

# Default PUK: 12345678
```

### SSH Still Asks for Password

Ensure authorized_keys has your YubiKey public key:
```bash
# Check Hostinger authorized_keys
ssh -p 65002 u693982071@147.93.42.19 "cat ~/.ssh/authorized_keys | grep ssh-rsa"

# Should show your key starting with: ssh-rsa AAAAB3...
```

### "Touch required" Not Prompting

Check YubiKey PIV slot configuration:
```bash
ykman piv keys info
# Output should show: Touch policy: ALWAYS
```

### Deploy Script Won't Complete

Common issues:
```bash
# 1. Keychain not unlocked
security unlock-keychain ~/Library/Keychains/essencience-secure.keychain-db

# 2. YubiKey not inserted
ykman list

# 3. Wrong PIN
# Re-run setup: bash setup-hardware-keychain.sh
```

---

## Security Best Practices

✅ **Do:**
- Carry YubiKey with you (it's your key to production)
- Use strong Keychain master password
- Enable PIN on YubiKey (set to custom, not default)
- Store YubiKey in secure location
- Use 2 YubiKeys (for redundancy)
- Review access logs regularly
- Update YubiKey firmware annually

❌ **Don't:**
- Share YubiKey with anyone
- Use default PIN (123456)
- Leave YubiKey unattended
- Store plain passwords anywhere
- Commit private keys to git
- Disable touch requirement

---

## Certificate-Based SSH (Advanced)

### Generate SSH Certificate

```bash
# Create CA private key (store securely)
ssh-keygen -t ed25519 -f ~/.ssh/essencience-ca -N ""

# Sign YubiKey public key with CA
ssh-keygen -s ~/.ssh/essencience-ca \
    -I "essencience-${USER}-$(date +%s)" \
    -n u693982071 \
    -V +52w \
    ~/.ssh/essencience/essencience-yubikey.pub

# Result: essencience-yubikey-cert.pub
```

### Add Certificate to Hostinger

```bash
# Option 1: Via SSH
ssh -p 65002 u693982071@147.93.42.19 << 'SSHCMD'
cat >> ~/.ssh/authorized_keys << 'PUBKEY'
$(cat ~/.ssh/essencience/essencience-yubikey-cert.pub)
PUBKEY
SSHCMD

# Option 2: Via File Manager
# Copy essencience-yubikey-cert.pub contents
# Paste into Hostinger ~/.ssh/authorized_keys
```

### Benefits of Certificates

- Single certificate valid for multiple servers
- Can add expiration (e.g., 1 year)
- Can revoke without removing key from all servers
- Better audit logging
- Identity embedded in certificate

---

## Monitoring & Logging

### Check Access Logs (Hostinger)

```bash
ssh -p 65002 u693982071@147.93.42.19 "tail -100 ~/.ssh/authorized_keys.audit"
```

### View YubiKey Operations

YubiKey Manager logs PIV operations in system logs:
```bash
log show --predicate 'eventMessage contains "piv"' --info
```

### Monitor Deployment

```bash
# Check recent deployments
ls -lat /home/u693982071/public_html/.git/

# View deployment logs
ssh -p 65002 u693982071@147.93.42.19 "tail -50 ~/deployment.log"
```

---

## Support & Resources

**YubiKey Documentation:**
- https://docs.yubico.com/
- https://developers.yubico.com/

**SSH Certificate Authority:**
- https://wiki.archlinux.org/title/SSH_keys#ECDSA_key

**Essencience Repository:**
- https://github.com/goodreau/essencience.com

**macOS Keychain:**
- https://support.apple.com/guide/keychain-access/

---

## Quick Reference

```bash
# Initialize hardware security
bash setup-hardware-keychain.sh      # Create secure Keychain
bash setup-hardware-ssh-certs.sh     # Setup YubiKey SSH

# Daily operations
bash deploy.sh                       # Deploy (uses YubiKey + Keychain)
bash deploy-helper.sh                # Interactive menu

# Manage credentials
open -a "Keychain Access"            # View Keychain
ykman list                           # Check YubiKey status
ykman piv keys info                  # Check SSH key details

# Test access
ssh -p 65002 u693982071@147.93.42.19 # Test YubiKey SSH (requires touch)

# Troubleshooting
security unlock-keychain ~/Library/Keychains/essencience-secure.keychain-db
ykman piv change-pin                 # Change YubiKey PIN
ykman piv reset                      # Factory reset YubiKey
```

---

**Your deployment is now protected by hardware security! 🔐🛡️**

For Hostinger setup instructions specific to your YubiKey, see:
`~/.ssh/essencience/HOSTINGER_SETUP.md`
