<?php

namespace Essencience\CertificateAuthority\Commands;

use Essencience\CertificateAuthority\Services\CertificateService;
use Illuminate\Console\Command;

class GenerateCACommand extends Command
{
    protected $signature = 'ca:generate {--force : Force regeneration of existing CA}';
    protected $description = 'Generate a new Certificate Authority';

    public function handle(CertificateService $certificateService): int
    {
        $caPath = config('certificate-authority.paths.ca_cert');

        if (file_exists($caPath) && !$this->option('force')) {
            $this->error('CA already exists. Use --force to regenerate.');
            return self::FAILURE;
        }

        $this->info('Generating Certificate Authority...');

        try {
            $result = $certificateService->createCA();

            $this->info('✓ CA generated successfully!');
            $this->line('  CA Key: ' . $result['ca_key']);
            $this->line('  CA Cert: ' . $result['ca_cert']);

            if (config('certificate-authority.keychain.use_keychain')) {
                $this->info('✓ CA imported to macOS Keychain');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate CA: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
