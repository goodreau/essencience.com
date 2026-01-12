# Hostinger Git Integration Setup Guide

## Overview
This guide walks you through deploying essencience.com to Hostinger using Git Integration—the easiest method that doesn't require SSH access.

---

## ✅ Pre-Deployment Checklist

- [x] GitHub repository created: `goodreau/essencience.com`
- [x] All code committed and pushed to `main` branch
- [x] Laravel environment ready (.env.example in repo)
- [x] Deployment script (install.sh) in repository
- [x] Database: SQLite (portable, no setup needed)
- [x] No node_modules/vendor in repo (.gitignore configured)

---

## Step 1: Access Hostinger Git Integration

### Option A: Via Hostinger Dashboard (Recommended)
1. Log in to **Hostinger Control Panel**
2. Click **Hosting** → Select your plan
3. Look for one of these sections:
   - **Git Integration** (newer accounts)
   - **Repository** or **Deployment**
   - **Git Deploy** or **Git Tools**
   - **File Manager** → **Git** tab

### Option B: Via File Manager
1. Go to **File Manager** in Hostinger
2. Look for **Git** icon/button (top menu)
3. Click **Clone from GitHub**

---

## Step 2: Connect Your GitHub Repository

### Repository Details:
```
Repository URL: https://github.com/goodreau/essencience.com
Branch: main
Deploy To: /public_html
```

### In Hostinger, Enter:
- **Repository URL**: `https://github.com/goodreau/essencience.com`
- **Branch**: `main`
- **Deployment Path**: `/public_html` (or `/home/u693982071/public_html`)

**Authentication:**
- Since the repo is public, click **No Authentication** or **Skip**
- If prompted for credentials, leave blank (public repo needs no token)

### Click: **Deploy** or **Clone Repository**

⏳ **Wait 2-5 minutes** while Hostinger clones the repo.

---

## Step 3: Verify Files Are Deployed

Once deployment completes:

1. Go to **File Manager**
2. Navigate to `/public_html`
3. You should see:
   - `artisan` (PHP command)
   - `composer.json`
   - `app/` folder
   - `public/` folder
   - `resources/` folder
   - `routes/` folder
   - `install.sh` (deployment script)

---

## Step 4: Run Post-Deployment Setup

### Method A: Using Hostinger Terminal (Easiest)

1. In **File Manager**, look for **Terminal** button (top right)
2. If unavailable, try **Settings** → **Advanced** → **Terminal**
3. Run these commands in sequence:

```bash
cd /home/u693982071/public_html

# Copy environment file
cp .env.example .env

# Generate encryption key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Clear cache
php artisan cache:clear

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod 644 database/database.sqlite

# Check status
php artisan -V
```

### Method B: Using SSH (If Terminal unavailable)

If Hostinger Terminal isn't available, try SSH with password:

```bash
ssh -p 65002 u693982071@147.93.42.19
# Enter your password when prompted
```

Then run the same commands as Method A.

### Method C: Manual Via File Manager

If you can't access Terminal or SSH:

1. In **File Manager**, right-click on `.env.example`
2. **Rename** to `.env`
3. **Edit** the `.env` file and update:
   ```
   APP_KEY=base64:YOUR_BASE64_KEY_HERE
   APP_URL=https://essencience.com
   ```

4. Then try SSH or ask Hostinger support to run the setup script via their backend

---

## Step 5: Configure Domain SSL

1. Go to **Hosting** → **SSL Certificates**
2. Look for **essencience.com**
3. If not active, click **Install** (Hostinger usually auto-generates free Let's Encrypt)
4. Wait for activation (usually instant)

---

## Step 6: Verify Live Deployment

### Test Your Site:

1. **Open** `https://essencience.com` in browser
2. **Check these pages work:**
   - `/` (Home - hero + features)
   - `/about` (Mission + Values)
   - `/services` (Service cards)
   - `/contact` (Contact form)
   - `/counter` (Livewire demo - click buttons to test interactivity)

3. **All pages should load without errors**

### Troubleshooting:

| Problem | Solution |
|---------|----------|
| **Blank page** | Check if APP_KEY is set in .env |
| **Missing public files** | Run `php artisan storage:link` |
| **404 on pages** | Verify all routes are defined in `routes/web.php` |
| **Livewire not working** | Check browser console for JS errors, verify Livewire views exist |
| **Database error** | Run `php artisan migrate` to create tables |

---

## Step 7: Future Updates (Pull Latest Changes)

### To deploy future code changes:

1. Commit and push changes to GitHub:
   ```bash
   git add .
   git commit -m "Feature description"
   git push origin main
   ```

2. In **Hostinger File Manager** → **Git**:
   - Click **Pull** or **Sync** (auto-pulls latest `main` branch)
   - Or use SSH: `cd /home/u693982071/public_html && git pull origin main`

3. Run migrations if database changed:
   ```bash
   php artisan migrate
   ```

---

## Hostinger Control Panel Paths Reference

- **Hosting Dashboard**: Log in → Hosting → Select plan
- **File Manager**: Hosting → File Manager
- **SSH/Terminal**: File Manager → Terminal button (or settings)
- **SSL Certificates**: Hosting → SSL/Security → Certificates
- **Git Integration**: Hosting → Git (or File Manager → Git tab)
- **Domains**: Hosting → Domains

---

## Quick Troubleshooting Checklist

✅ Repository cloned successfully  
✅ `.env` file created from `.env.example`  
✅ `APP_KEY` generated  
✅ Database migrations ran  
✅ `storage/` and `bootstrap/cache` have write permissions  
✅ SSL certificate active  
✅ Domain points to `/public_html`  
✅ All Livewire components render  

---

## Support Resources

- **Hostinger Help**: https://support.hostinger.com
- **Laravel Docs**: https://laravel.com/docs
- **Livewire Docs**: https://livewire.laravel.com
- **This Project Repo**: https://github.com/goodreau/essencience.com

---

## Contact Info on Hostinger Server

```
Host: 147.93.42.19:65002
User: u693982071
Path: /home/u693982071/public_html
Domain: essencience.com
```

---

✨ **Your site should now be live at https://essencience.com!** ✨
