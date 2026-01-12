# Essencience Passport

Certificate-based authentication for Laravel applications. No usernames or passwords needed - users authenticate using X.509 certificates.

## Features

- ✅ Certificate-based authentication (mutual TLS)
- ✅ No passwords required
- ✅ Automatic user authentication from certificates
- ✅ Certificate issuance and management
- ✅ Certificate revocation
- ✅ Integration with macOS Keychain
- ✅ Laravel middleware for protected routes
- ✅ Artisan commands for certificate management

## Installation

### 1. Add to composer.json

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/essencience/passport"
        }
    ],
    "require": {
        "essencience/passport": "@dev"
    }
}
```

### 2. Install the package

```bash
composer require essencience/passport
```

### 3. Publish configuration and migrations

```bash
php artisan vendor:publish --tag=passport-config
php artisan vendor:publish --tag=passport-migrations
php artisan migrate
```

### 4. Generate Certificate Authority

```bash
php artisan ca:generate
```

## Configuration

Edit `config/passport.php`:

```php
return [
    'enabled' => true,
    'verify_ca' => true,
    'auto_create_users' => false, // Auto-create users from certificates
    'certificate_validity_days' => 365,
];
```

## Usage

### 1. Issue Certificate for a User

```bash
# By user ID
php artisan passport:issue 1

# By email
php artisan passport:issue user@example.com

# With custom validity
php artisan passport:issue user@example.com --validity=730

# With password protection
php artisan passport:issue user@example.com --password=secret
```

This generates:
- `storage/passport/users/{id}/certificate.p12` - For browsers/clients
- `storage/passport/users/{id}/certificate.pem` - PEM certificate
- `storage/passport/users/{id}/key.pem` - Private key

### 2. Protect Routes with Certificate Authentication

```php
// routes/web.php or routes/api.php

use Illuminate\Support\Facades\Route;

// Require certificate authentication
Route::middleware('passport')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/api/secure-data', [ApiController::class, 'secureData']);
});

// Or on individual routes
Route::get('/profile', [ProfileController::class, 'show'])
    ->middleware('passport');
```

### 3. List User Certificates

```bash
# List all certificates
php artisan passport:list

# List certificates for specific user
php artisan passport:list user@example.com
php artisan passport:list 1
```

### 4. Revoke a Certificate

```bash
php artisan passport:revoke ABC123SERIAL --reason="Security breach"
```

### 5. Using Certificates in Applications

**Browser Setup:**

1. Issue certificate for user
2. Install `.p12` file in browser/keychain
3. Access protected routes - browser automatically presents certificate

**API Client (cURL):**

```bash
curl https://essencience.com/api/secure-data \
  --cert storage/passport/users/1/certificate.pem \
  --key storage/passport/users/1/key.pem
```

**PHP Client (Guzzle):**

```php
use GuzzleHttp\Client;

$client = new Client([
    'cert' => storage_path('passport/users/1/certificate.pem'),
    'ssl_key' => storage_path('passport/users/1/key.pem'),
]);

$response = $client->get('https://essencience.com/api/secure-data');
```

### 6. Programmatic Usage

```php
use Essencience\Passport\Services\PassportService;
use Essencience\Passport\Models\UserCertificate;

class UserController extends Controller
{
    public function __construct(
        protected PassportService $passportService
    ) {}

    public function issueCertificate(User $user)
    {
        $certificate = $this->passportService->issueCertificate($user, [
            'validity_days' => 365,
            'password' => 'optional-password',
        ]);

        return response()->download(
            config('passport.user_certs_path') . "/{$user->id}/certificate.p12"
        );
    }

    public function revokeCertificate(UserCertificate $certificate)
    {
        $this->passportService->revokeCertificate($certificate, 'User requested');

        return response()->json(['message' => 'Certificate revoked']);
    }

    public function listCertificates(User $user)
    {
        return $user->certificates()
            ->where('is_active', true)
            ->get();
    }
}
```

### 7. Check Authentication in Controllers

```php
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); // Authenticated via certificate
        
        if ($request->authenticated_via === 'certificate') {
            // User authenticated with certificate
        }

        return view('dashboard', compact('user'));
    }
}
```

## Server Configuration

### Nginx Configuration

For production with Nginx + PHP-FPM:

```nginx
server {
    listen 443 ssl;
    server_name essencience.com;

    ssl_certificate /path/to/server-cert.pem;
    ssl_certificate_key /path/to/server-key.pem;
    
    # Require client certificates
    ssl_client_certificate /path/to/ca-cert.pem;
    ssl_verify_client on;
    ssl_verify_depth 2;

    # Pass client certificate to PHP
    fastcgi_param SSL_CLIENT_CERT $ssl_client_cert;
    fastcgi_param SSL_CLIENT_SERIAL $ssl_client_serial;
    fastcgi_param SSL_CLIENT_S_DN $ssl_client_s_dn;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### Development with Laravel Serve

For local development:

```bash
# Install certificates in macOS Keychain
php artisan passport:issue your@email.com --download

# Import the .p12 file to Keychain Access
open storage/passport/users/1/certificate.p12
```

## Database Schema

The package creates a `user_certificates` table:

```sql
- id
- user_id (foreign key)
- serial_number (unique)
- certificate (text)
- public_key
- subject
- issuer
- valid_from
- valid_until
- is_active
- is_revoked
- revoked_at
- revocation_reason
- timestamps
```

## User Model Relationship

Add to your User model:

```php
use Essencience\Passport\Models\UserCertificate;

class User extends Authenticatable
{
    public function certificates()
    {
        return $this->hasMany(UserCertificate::class);
    }

    public function activeCertificate()
    {
        return $this->hasOne(UserCertificate::class)
            ->where('is_active', true)
            ->where('is_revoked', false)
            ->where('valid_until', '>', now());
    }
}
```

## Security Considerations

1. **CA Private Key**: Protect `storage/ca/ca-key.pem` - never expose it
2. **User Private Keys**: Stored in `storage/passport/users/{id}/` - secure this directory
3. **Certificate Revocation**: Always revoke compromised certificates
4. **Validity Period**: Use appropriate validity periods (1 year recommended)
5. **Backup**: Regularly backup CA certificate and key

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- OpenSSL
- `essencience/certificate-authority` package

## License

MIT
