# macOS Keychain Setup for Essencience Deployment

## Overview

Instead of storing passwords in `.env.deployment` files or committing them to git, we now use **macOS Keychain** to securely encrypt and store your Hostinger SSH credentials.

**Benefits:**
- ✅ Passwords are encrypted by macOS Keychain
- ✅ Never exposed in environment files or scripts
- ✅ Never committed to git (even accidentally)
- ✅ Automatically available to deployment scripts
- ✅ Can be managed via Keychain Access app
- ✅ Works with all shell sessions

---

## Quick Start (2 steps)

### 1. Store Your Credentials in Keychain

```bash
bash setup-keychain.sh
```

This script will:
- Prompt you for your Hostinger SSH password
- Store it securely in macOS Keychain under "Essencience-Hostinger"
- Optionally store SSH host and port

### 2. Deploy

```bash
bash deploy.sh
```

The deployment script will automatically retrieve your password from Keychain and deploy to Hostinger.

---

## What Gets Stored in Keychain

| Item | Service | Account | Notes |
|------|---------|---------|-------|
| SSH Password | `Essencience-Hostinger` | `u693982071` | Primary credential (required) |
| SSH Host | `Essencience-Hostinger-HOST` | `u693982071` | Optional, defaults to 147.93.42.19 |
| SSH Port | `Essencience-Hostinger-PORT` | `u693982071` | Optional, defaults to 65002 |

---

## Managing Credentials

### View Stored Credentials

1. Open **Keychain Access** app:
   ```bash
   open /Applications/Utilities/Keychain\ Access.app
   ```

2. Search for "Essencience-Hostinger" in the login keychain

3. Double-click any entry to view/edit it

### Update Password

Run the setup script again:
```bash
bash setup-keychain.sh
```

It will prompt you for a new password and update the existing entry.

### Delete Credentials

In Keychain Access:
1. Find "Essencience-Hostinger" entries
2. Right-click → Delete
3. Confirm

Or from terminal:
```bash
security delete-generic-password -s "Essencience-Hostinger" -a "u693982071"
security delete-generic-password -s "Essencience-Hostinger-HOST" -a "u693982071"
security delete-generic-password -s "Essencience-Hostinger-PORT" -a "u693982071"
```

---

## How It Works

### setup-keychain.sh

1. Checks if running on macOS
2. Prompts you for your Hostinger SSH password (twice to confirm)
3. Stores the password in Keychain using `security add-generic-password`
4. Optionally stores SSH host and port

### deploy.sh

1. Checks if Keychain credentials exist
2. Retrieves your password using `security find-generic-password`
3. Uses password in SSH commands via `sshpass`
4. Deploys to Hostinger with automatic authentication

### deploy-helper.sh

1. Retrieves credentials from Keychain
2. Provides an interactive menu for:
   - Full deployment
   - Pulling code updates
   - Running migrations
   - Clearing cache
   - Viewing logs
   - SSH access

---

## Troubleshooting

### "Credentials not found in Keychain"

**Solution:** Run `bash setup-keychain.sh` first

```bash
bash setup-keychain.sh
```

### "This script requires macOS with Keychain"

**Solution:** This method only works on macOS. On Linux/Windows, use SSH keys instead:

```bash
ssh-keygen -t ed25519 -f ~/.ssh/hostinger
# Add public key to Hostinger authorized_keys
```

### "Permission denied (publickey,password)"

**Solution:** Verify password is correct:

```bash
# Check what's stored
security find-generic-password -s "Essencience-Hostinger" -a "u693982071"

# Re-enter correct password
bash setup-keychain.sh
```

### Can't open Keychain Access app

Try:
```bash
open -a "Keychain Access"
```

Or navigate: Finder → Applications → Utilities → Keychain Access

---

## Security Best Practices

✅ **Do:**
- Store complex passwords in Keychain (e.g., `ApolloX8p#E`)
- Use Keychain instead of environment files
- Update your password regularly in Keychain
- Lock your Mac when away from desk

❌ **Don't:**
- Share your Keychain password (it's your Mac's security)
- Write passwords in `.env` files
- Commit `.env.deployment` to git
- Use weak/simple passwords

---

## Automation & CI/CD

For automated deployments (GitHub Actions, etc.), use SSH keys instead:

```bash
# Generate SSH key
ssh-keygen -t ed25519 -f ~/.ssh/hostinger -N ""

# Add to Hostinger's authorized_keys
cat ~/.ssh/hostinger.pub | ssh -p 65002 u693982071@147.93.42.19 "cat >> ~/.ssh/authorized_keys"

# Use in CI/CD
ssh -i ~/.ssh/hostinger -p 65002 u693982071@147.93.42.19 "cd /public_html && git pull"
```

---

## Related Files

- `setup-keychain.sh` - Initialize Keychain credentials
- `deploy.sh` - Main deployment script (retrieves from Keychain)
- `deploy-helper.sh` - Interactive deployment menu (retrieves from Keychain)
- `.env.deployment` - **REMOVED** (no longer needed)
- `.env.deployment.example` - **REMOVED** (no longer needed)

---

## Migration from .env.deployment

If you were previously using `.env.deployment`:

1. **Remove old files** (now in .gitignore):
   ```bash
   rm .env.deployment .env.deployment.example
   ```

2. **Add credentials to Keychain:**
   ```bash
   bash setup-keychain.sh
   ```

3. **Verify deployment works:**
   ```bash
   bash deploy.sh
   ```

---

## Useful Commands

```bash
# Initialize credentials
bash setup-keychain.sh

# Deploy to production
bash deploy.sh

# Interactive deployment menu
bash deploy-helper.sh

# View all stored credentials
security find-generic-password -l | grep Essencience

# View specific credential
security find-generic-password -s "Essencience-Hostinger" -a "u693982071" -w

# Delete all Essencience credentials
security delete-generic-password -s "Essencience-Hostinger" -a "u693982071"
```

---

## Support

For issues:
1. Verify Keychain has your credentials: `security find-generic-password -s "Essencience-Hostinger" -w`
2. Check Hostinger password is correct by manual SSH: `ssh -p 65002 u693982071@147.93.42.19`
3. Run setup again: `bash setup-keychain.sh`
4. Check repo for latest scripts: `git pull origin main`

---

**Your deployment is now secure! 🔐**
