<?php

namespace Essencience\Passport\Services;

use Essencience\CertificateAuthority\Services\CertificateService;
use Essencience\Passport\Models\UserCertificate;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PassportService
{
    public function __construct(
        protected CertificateService $certificateService
    ) {}

    /**
     * Issue a certificate for a user
     */
    public function issueCertificate($user, array $options = []): UserCertificate
    {
        $email = $user->email;
        $name = $user->name ?? $email;
        $validity = $options['validity_days'] ?? config('passport.certificate_validity_days');

        $this->ensureDirectoryExists();

        // Generate paths
        $userDir = $this->getUserCertDir($user->id);
        $keyPath = "{$userDir}/key.pem";
        $csrPath = "{$userDir}/request.csr";
        $certPath = "{$userDir}/certificate.pem";
        $p12Path = "{$userDir}/certificate.p12";

        // Generate private key
        $this->executeCommand("openssl genrsa -out {$keyPath} 2048");

        // Create CSR with user details
        $subject = $this->buildUserSubject($name, $email);
        $this->executeCommand(
            "openssl req -new -key {$keyPath} -out {$csrPath} -subj \"{$subject}\""
        );

        // Sign with CA
        $caKey = config('certificate-authority.paths.ca_key');
        $caCert = config('certificate-authority.paths.ca_cert');

        $this->executeCommand(
            "openssl x509 -req -in {$csrPath} -CA {$caCert} -CAkey {$caKey} " .
            "-CAcreateserial -out {$certPath} -days {$validity} -sha256"
        );

        // Get certificate info
        $certInfo = $this->getCertificateInfo($certPath);
        $serialNumber = $certInfo['serial'];

        // Create PKCS12 bundle for easy distribution
        $password = $options['password'] ?? '';
        $passArg = $password ? "-passout pass:{$password}" : "-passout pass:";

        $this->executeCommand(
            "openssl pkcs12 -export -out {$p12Path} -inkey {$keyPath} " .
            "-in {$certPath} -certfile {$caCert} {$passArg}"
        );

        // Store certificate in database
        $userCertificate = UserCertificate::create([
            'user_id' => $user->id,
            'serial_number' => $serialNumber,
            'certificate' => file_get_contents($certPath),
            'public_key' => $this->extractPublicKey($certPath),
            'subject' => $certInfo['subject'],
            'issuer' => $certInfo['issuer'],
            'valid_from' => $certInfo['validity']['not_before'],
            'valid_until' => $certInfo['validity']['not_after'],
            'is_active' => true,
        ]);

        return $userCertificate;
    }

    /**
     * Verify a client certificate
     */
    public function verifyCertificate(string $certPem): array|false
    {
        if (!config('passport.verify_ca')) {
            return $this->parseCertificate($certPem);
        }

        // Write certificate to temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'cert_');
        file_put_contents($tempFile, $certPem);

        try {
            $caCert = config('passport.ca_cert_path');
            $result = Process::run("openssl verify -CAfile {$caCert} {$tempFile}");

            if (!$result->successful() || !str_contains($result->output(), 'OK')) {
                return false;
            }

            return $this->parseCertificate($certPem);
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Parse certificate details
     */
    protected function parseCertificate(string $certPem): array
    {
        $parsed = openssl_x509_parse($certPem);

        if (!$parsed) {
            throw new RuntimeException('Failed to parse certificate');
        }

        return [
            'serial' => $parsed['serialNumber'] ?? null,
            'subject' => $parsed['subject'] ?? [],
            'issuer' => $parsed['issuer'] ?? [],
            'valid_from' => isset($parsed['validFrom_time_t'])
                ? date('Y-m-d H:i:s', $parsed['validFrom_time_t'])
                : null,
            'valid_until' => isset($parsed['validTo_time_t'])
                ? date('Y-m-d H:i:s', $parsed['validTo_time_t'])
                : null,
            'email' => $parsed['subject']['emailAddress'] ?? null,
            'cn' => $parsed['subject']['CN'] ?? null,
        ];
    }

    /**
     * Find user by certificate
     */
    public function findUserByCertificate(string $certPem)
    {
        $certInfo = $this->parseCertificate($certPem);

        if (!$certInfo || !isset($certInfo['serial'])) {
            return null;
        }

        $userCert = UserCertificate::where('serial_number', $certInfo['serial'])
            ->where('is_active', true)
            ->where('is_revoked', false)
            ->first();

        if (!$userCert || !$userCert->isValid()) {
            return null;
        }

        return $userCert->user;
    }

    /**
     * Get certificate from request headers
     */
    public function getCertificateFromRequest($request): ?string
    {
        $certHeader = config('passport.client_cert_header');
        $cert = $request->header($certHeader) ?? $request->server($certHeader);

        if (!$cert) {
            return null;
        }

        // URL decode if needed
        $cert = urldecode($cert);

        // Ensure proper PEM format
        if (!str_starts_with($cert, '-----BEGIN CERTIFICATE-----')) {
            $cert = "-----BEGIN CERTIFICATE-----\n" .
                    chunk_split($cert, 64) .
                    "-----END CERTIFICATE-----";
        }

        return $cert;
    }

    /**
     * Revoke a user certificate
     */
    public function revokeCertificate(UserCertificate $certificate, string $reason = 'User requested'): void
    {
        $certificate->revoke($reason);
    }

    /**
     * Get certificate info
     */
    protected function getCertificateInfo(string $certPath): array
    {
        return $this->certificateService->getCertificateInfo($certPath);
    }

    /**
     * Extract public key from certificate
     */
    protected function extractPublicKey(string $certPath): string
    {
        $result = Process::run("openssl x509 -in {$certPath} -pubkey -noout");

        if (!$result->successful()) {
            throw new RuntimeException('Failed to extract public key');
        }

        return $result->output();
    }

    /**
     * Build subject for user certificate
     */
    protected function buildUserSubject(string $name, string $email): string
    {
        $subject = config('certificate-authority.subject');

        return sprintf(
            '/C=%s/ST=%s/L=%s/O=%s/CN=%s/emailAddress=%s',
            $subject['country'],
            $subject['state'],
            $subject['locality'],
            $subject['organization'],
            $name,
            $email
        );
    }

    /**
     * Get user certificate directory
     */
    protected function getUserCertDir(int $userId): string
    {
        return config('passport.user_certs_path') . "/{$userId}";
    }

    /**
     * Ensure directory exists
     */
    protected function ensureDirectoryExists(): void
    {
        $dir = config('passport.user_certs_path');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Execute shell command
     */
    protected function executeCommand(string $command): void
    {
        $result = Process::run($command);

        if (!$result->successful()) {
            throw new RuntimeException(
                "Command failed: {$command}\nError: {$result->errorOutput()}"
            );
        }
    }
}
