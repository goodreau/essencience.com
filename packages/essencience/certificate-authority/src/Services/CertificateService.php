<?php

namespace Essencience\CertificateAuthority\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CertificateService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('certificate-authority');
    }

    /**
     * Create a new Certificate Authority
     */
    public function createCA(): array
    {
        $this->ensureDirectoryExists();

        $caKey = $this->config['paths']['ca_key'];
        $caCert = $this->config['paths']['ca_cert'];
        $validity = $this->config['validity']['ca'];

        // Generate CA private key
        $this->executeCommand("openssl genrsa -out {$caKey} 4096");

        // Generate CA certificate
        $subject = $this->buildSubject($this->config['ca_name']);
        $this->executeCommand(
            "openssl req -new -x509 -days {$validity} -key {$caKey} -out {$caCert} -subj \"{$subject}\""
        );

        // Import to Keychain if enabled
        if ($this->config['keychain']['use_keychain']) {
            $this->importToKeychain($caCert, true);
        }

        return [
            'ca_key' => $caKey,
            'ca_cert' => $caCert,
        ];
    }

    /**
     * Create a server certificate signed by CA
     */
    public function createServerCertificate(string $domain = null): array
    {
        $domain = $domain ?? $this->config['domain'];

        $serverKey = $this->config['paths']['server_key'];
        $serverCert = $this->config['paths']['server_cert'];
        $serverCSR = storage_path('ca/server.csr');
        $caKey = $this->config['paths']['ca_key'];
        $caCert = $this->config['paths']['ca_cert'];
        $validity = $this->config['validity']['server'];

        // Generate server private key
        $this->executeCommand("openssl genrsa -out {$serverKey} 2048");

        // Create CSR
        $subject = $this->buildSubject($domain);
        $this->executeCommand(
            "openssl req -new -key {$serverKey} -out {$serverCSR} -subj \"{$subject}\""
        );

        // Sign with CA
        $this->executeCommand(
            "openssl x509 -req -in {$serverCSR} -CA {$caCert} -CAkey {$caKey} " .
            "-CAcreateserial -out {$serverCert} -days {$validity} -sha256"
        );

        // Import to Keychain
        if ($this->config['keychain']['use_keychain']) {
            $p12File = storage_path('ca/server.p12');
            $this->executeCommand(
                "openssl pkcs12 -export -out {$p12File} -inkey {$serverKey} " .
                "-in {$serverCert} -certfile {$caCert} -passout pass:essencience"
            );
            $this->importToKeychain($p12File, false, 'essencience');
        }

        return [
            'server_key' => $serverKey,
            'server_cert' => $serverCert,
            'domain' => $domain,
        ];
    }

    /**
     * Verify a certificate
     */
    public function verifyCertificate(string $certPath): bool
    {
        $caCert = $this->config['paths']['ca_cert'];

        $result = Process::run("openssl verify -CAfile {$caCert} {$certPath}");

        return $result->successful() && str_contains($result->output(), 'OK');
    }

    /**
     * Get certificate information
     */
    public function getCertificateInfo(string $certPath): array
    {
        $result = Process::run("openssl x509 -in {$certPath} -text -noout");

        if (!$result->successful()) {
            throw new RuntimeException("Failed to read certificate: {$certPath}");
        }

        return [
            'raw' => $result->output(),
            'subject' => $this->extractField($result->output(), 'Subject:'),
            'issuer' => $this->extractField($result->output(), 'Issuer:'),
            'validity' => $this->extractValidity($result->output()),
        ];
    }

    /**
     * Import certificate to macOS Keychain
     */
    protected function importToKeychain(string $certPath, bool $trustRoot = false, ?string $password = null): void
    {
        if ($trustRoot) {
            $keychain = $this->config['keychain']['system_keychain'];
            $this->executeCommand(
                "sudo security add-trusted-cert -d -r trustRoot -k {$keychain} {$certPath}"
            );
        } else {
            $keychain = $this->config['keychain']['keychain_path'];
            $passArg = $password ? "-P {$password}" : '';
            $this->executeCommand(
                "security import {$certPath} -k {$keychain} {$passArg}"
            );
        }
    }

    /**
     * Build certificate subject string
     */
    protected function buildSubject(string $commonName): string
    {
        $subject = $this->config['subject'];

        return sprintf(
            '/C=%s/ST=%s/L=%s/O=%s/CN=%s',
            $subject['country'],
            $subject['state'],
            $subject['locality'],
            $subject['organization'],
            $commonName
        );
    }

    /**
     * Ensure storage directory exists
     */
    protected function ensureDirectoryExists(): void
    {
        $dir = storage_path('ca');
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

    /**
     * Extract field from certificate text
     */
    protected function extractField(string $text, string $field): ?string
    {
        if (preg_match('/' . preg_quote($field, '/') . '\s*(.+)/', $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    /**
     * Extract validity dates from certificate
     */
    protected function extractValidity(string $text): array
    {
        $notBefore = $this->extractField($text, 'Not Before:');
        $notAfter = $this->extractField($text, 'Not After:');

        return [
            'not_before' => $notBefore,
            'not_after' => $notAfter,
        ];
    }
}
