# SSH Security Guide for Essencience

## 🔒 Secure SSH Setup

This guide explains how to securely manage SSH credentials for your Hostinger deployment.

## Option 1: SSH Key Authentication (Recommended ⭐)

SSH keys are **much more secure** than passwords. Here's how to set it up:

### Step 1: Generate SSH Key Pair (if you don't have one)

On your local machine:
```bash
ssh-keygen -t ed25519 -C "your_email@example.com" -f ~/.ssh/hostinger
```

Press Enter to skip passphrase (or set one for extra security).

### Step 2: Add Public Key to Hostinger

```bash
# Copy public key
cat ~/.ssh/hostinger.pub

# Connect to Hostinger and add the key
ssh -p 65002 u693982071@147.93.42.19

# On Hostinger server:
mkdir -p ~/.ssh
echo "your_public_key_content_here" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
exit
```

### Step 3: Configure .env.deployment

Create `.env.deployment` (NOT committed to Git):
```bash
cp .env.deployment.example .env.deployment
```

Edit `.env.deployment`:
```
HOSTINGER_SSH_HOST=147.93.42.19
HOSTINGER_SSH_PORT=65002
HOSTINGER_SSH_USER=u693982071
HOSTINGER_SSH_KEY=/path/to/your/private/key
HOSTINGER_DEPLOY_PATH=/home/u693982071/public_html
HOSTINGER_DOMAIN=essencience.com
```

### Step 4: Test Connection

```bash
ssh -i ~/.ssh/hostinger -p 65002 u693982071@147.93.42.19
```

## Option 2: Password Authentication (Less Secure)

If you must use a password:

### Step 1: Create .env.deployment

```bash
cp .env.deployment.example .env.deployment
```

Edit `.env.deployment`:
```
HOSTINGER_SSH_HOST=147.93.42.19
HOSTINGER_SSH_PORT=65002
HOSTINGER_SSH_USER=u693982071
HOSTINGER_SSH_PASSWORD=your_actual_password
HOSTINGER_DEPLOY_PATH=/home/u693982071/public_html
HOSTINGER_DOMAIN=essencience.com
```

### Step 2: Keep It Secret!

- ✅ `.env.deployment` is in `.gitignore` (never committed)
- ✅ NEVER commit this file
- ✅ NEVER share this file
- ✅ NEVER put credentials in code

## Using the Deploy Helper

Once configured, use the interactive deploy helper:

```bash
./deploy-helper.sh
```

Menu options:
1. Deploy from scratch
2. Pull latest changes
3. Run migrations
4. Clear cache
5. View server logs
6. SSH to server

## Changing Your Password on Hostinger

To change your SSH password on Hostinger:

1. Connect via SSH:
   ```bash
   ssh -p 65002 u693982071@147.93.42.19
   ```

2. Use `passwd` command:
   ```bash
   passwd
   ```

3. Enter your current password
4. Enter new password twice

## Best Practices

✅ **DO:**
- Use SSH keys instead of passwords
- Keep `.env.deployment` local only
- Use a strong password/passphrase
- Rotate credentials regularly
- Keep your SSH key backed up safely

❌ **DON'T:**
- Commit `.env.deployment` to Git
- Share your SSH credentials
- Use the same password everywhere
- Store passwords in plain text files that are tracked

## Git Safety Check

Verify `.env.deployment` is not tracked:

```bash
# This should show .env.deployment is ignored
git status --ignored
```

If you accidentally committed credentials:

```bash
# Remove from git history
git rm --cached .env.deployment
git commit -m "Remove .env.deployment"
git push origin main

# Change your Hostinger password immediately!
```

## Secure Deployment Commands

Deploy safely without exposing credentials:

```bash
# Using SSH key
ssh -i ~/.ssh/hostinger -p 65002 u693982071@147.93.42.19 \
  "bash <(curl -s https://raw.githubusercontent.com/goodreau/essencience.com/main/deploy.sh)"

# Or use the helper
./deploy-helper.sh
```

## Emergency: Reset Password

If credentials are compromised:

1. Change password on Hostinger immediately
2. Update `.env.deployment` locally
3. Check deployment logs for unauthorized access
4. Review file changes in Git history

```bash
cd ~/public_html
tail -f storage/logs/laravel.log
```
