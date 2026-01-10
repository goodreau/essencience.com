<?php

namespace Essencience\CertificateAuthority\Commands;

use Essencience\CertificateAuthority\Services\CertificateService;
use Illuminate\Console\Command;

class GenerateServerCertCommand extends Command
{
    protected $signature = 'ca:server {domain? : Domain name for the certificate}';
    protected $description = 'Generate a server certificate signed by CA';

    public function handle(CertificateService $certificateService): int
    {
        $domain = $this->argument('domain') ?? config('certificate-authority.domain');

        $this->info("Generating server certificate for: {$domain}");

        try {
            $result = $certificateService->createServerCertificate($domain);

            $this->info('✓ Server certificate generated successfully!');
            $this->line('  Server Key: ' . $result['server_key']);
            $this->line('  Server Cert: ' . $result['server_cert']);
            $this->line('  Domain: ' . $result['domain']);

            if (config('certificate-authority.keychain.use_keychain')) {
                $this->info('✓ Certificate imported to macOS Keychain');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate server certificate: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
