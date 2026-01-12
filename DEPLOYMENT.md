# Hostinger Deployment Setup

## ✅ Completed Steps

1. **Git Repository Initialized** ✓
2. **GitHub Repository Created** ✓
   - URL: https://github.com/goodreau/essencience.com
3. **Code Pushed to GitHub** ✓

## 🔧 Hostinger Configuration Required

### Step 1: Set Up SSH Access on Hostinger

1. Log into Hostinger hPanel
2. Go to **Advanced** → **SSH Access**
3. Enable SSH access if not already enabled
4. Note your SSH details:
   - Host: `srv###.hostinger.com`
   - Port: Usually `65002`
   - Username: Your hosting username (e.g., `u123456789`)

### Step 2: Generate SSH Key (if needed)

```bash
ssh-keygen -t rsa -b 4096 -C "your_email@example.com"
```

Add the public key to Hostinger:
1. Copy your public key: `cat ~/.ssh/id_rsa.pub`
2. In Hostinger hPanel → **SSH Access** → Add the public key

### Step 3: Set Up Git on Hostinger

SSH into your Hostinger server:
```bash
ssh -p 65002 u123456789@srv###.hostinger.com
```

Then initialize Git in your domain directory:
```bash
cd ~/domains/essencience.com/public_html
git init
git remote add origin https://github.com/goodreau/essencience.com.git
git fetch
git checkout main
```

### Step 4: Configure GitHub Secrets

Add these secrets in GitHub repository settings:
- Go to: https://github.com/goodreau/essencience.com/settings/secrets/actions

**Required Secrets:**
- `HOSTINGER_HOST`: Your server (e.g., `srv123.hostinger.com`)
- `HOSTINGER_USERNAME`: Your SSH username (e.g., `u123456789`)
- `HOSTINGER_SSH_KEY`: Your private SSH key (contents of `~/.ssh/id_rsa`)
- `HOSTINGER_PORT`: SSH port (usually `65002`)
- `HOSTINGER_PATH`: Path to your app (e.g., `/home/u123456789/domains/essencience.com/public_html`)

### Step 5: Configure Laravel on Hostinger

1. **Copy and configure .env file:**
```bash
cd laravel-temp
cp .env.example .env
php artisan key:generate
```

2. **Set up database in .env:**
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

3. **Update public directory:**
   - Point your domain's document root to: `public_html/laravel-temp/public`
   - In Hostinger hPanel → **Domains** → **Manage** → Set document root

4. **Set permissions:**
```bash
chmod -R 755 laravel-temp/storage
chmod -R 755 laravel-temp/bootstrap/cache
```

### Step 6: Test Deployment

Push a commit to GitHub:
```bash
git add .
git commit -m "Test deployment"
git push origin main
```

The GitHub Action will automatically deploy to Hostinger!

## Manual Deployment Alternative

If you prefer manual deployment via SSH:

```bash
ssh -p 65002 u123456789@srv###.hostinger.com
cd ~/domains/essencience.com/public_html
git pull origin main
cd laravel-temp
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

- **Permission errors**: Check storage and cache folder permissions
- **Database errors**: Verify .env database credentials
- **Composer errors**: Ensure PHP version matches (8.1+)
- **502 errors**: Check document root points to `laravel-temp/public`
