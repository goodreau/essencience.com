# Certificate Authority Package for Laravel

A Laravel package for managing SSL/TLS certificates using macOS Keychain as a Certificate Authority.

## Features

- ✅ Create and manage Certificate Authority (CA)
- ✅ Generate server certificates signed by CA
- ✅ Automatic integration with macOS Keychain
- ✅ Certificate verification
- ✅ Artisan commands for easy management
- ✅ Configurable validity periods and paths

## Installation

### 1. Add to composer.json

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/essencience/certificate-authority"
        }
    ],
    "require": {
        "essencience/certificate-authority": "@dev"
    }
}
```

### 2. Install the package

```bash
composer require essencience/certificate-authority
```

### 3. Publish configuration

```bash
php artisan vendor:publish --tag=certificate-authority-config
```

## Configuration

Edit `config/certificate-authority.php`:

```php
return [
    'domain' => env('CA_DOMAIN', 'essencience.com'),
    'ca_name' => env('CA_NAME', 'Essencience Root CA'),
    
    'paths' => [
        'ca_key' => storage_path('ca/ca-key.pem'),
        'ca_cert' => storage_path('ca/ca-cert.pem'),
        'server_key' => storage_path('ca/server-key.pem'),
        'server_cert' => storage_path('ca/server-cert.pem'),
    ],
    
    'keychain' => [
        'use_keychain' => true, // Auto-import to macOS Keychain
    ],
];
```

## Usage

### Generate Certificate Authority

```bash
php artisan ca:generate
```

This creates a root CA and imports it to your macOS Keychain as a trusted certificate.

### Generate Server Certificate

```bash
# For domain in config
php artisan ca:server

# For specific domain
php artisan ca:server example.com
```

### Verify Certificate

```bash
php artisan ca:verify storage/ca/server-cert.pem
```

### Programmatic Usage

```php
use Essencience\CertificateAuthority\Services\CertificateService;

class ExampleController extends Controller
{
    public function __construct(
        protected CertificateService $certificateService
    ) {}

    public function createCertificate()
    {
        // Create CA
        $ca = $this->certificateService->createCA();
        
        // Create server certificate
        $server = $this->certificateService->createServerCertificate('myapp.com');
        
        // Verify certificate
        $isValid = $this->certificateService->verifyCertificate(
            storage_path('ca/server-cert.pem')
        );
        
        // Get certificate info
        $info = $this->certificateService->getCertificateInfo(
            storage_path('ca/server-cert.pem')
        );
        
        return response()->json([
            'valid' => $isValid,
            'info' => $info,
        ]);
    }
}
```

## Environment Variables

Add to your `.env`:

```env
CA_DOMAIN=essencience.com
CA_NAME="Essencience Root CA"
CA_VALIDITY_DAYS=3650
SERVER_VALIDITY_DAYS=365
USE_KEYCHAIN=true
```

## Artisan Commands

| Command | Description |
|---------|-------------|
| `ca:generate` | Create a new Certificate Authority |
| `ca:server {domain?}` | Generate server certificate |
| `ca:verify {cert}` | Verify certificate against CA |

## Certificate Locations

After generation:

- CA Key: `storage/ca/ca-key.pem`
- CA Certificate: `storage/ca/ca-cert.pem`
- Server Key: `storage/ca/server-key.pem`
- Server Certificate: `storage/ca/server-cert.pem`

Certificates are automatically imported to:
- **CA**: System Keychain (trusted root)
- **Server**: Login Keychain

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- macOS with OpenSSL
- Keychain Access

## License

MIT
