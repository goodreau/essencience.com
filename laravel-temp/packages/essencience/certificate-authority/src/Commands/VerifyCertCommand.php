<?php

namespace Essencience\CertificateAuthority\Commands;

use Essencience\CertificateAuthority\Services\CertificateService;
use Illuminate\Console\Command;

class VerifyCertCommand extends Command
{
    protected $signature = 'ca:verify {cert : Path to certificate file}';
    protected $description = 'Verify a certificate against the CA';

    public function handle(CertificateService $certificateService): int
    {
        $certPath = $this->argument('cert');

        if (!file_exists($certPath)) {
            $this->error("Certificate not found: {$certPath}");
            return self::FAILURE;
        }

        $this->info("Verifying certificate: {$certPath}");

        try {
            $isValid = $certificateService->verifyCertificate($certPath);

            if ($isValid) {
                $this->info('✓ Certificate is valid!');

                $info = $certificateService->getCertificateInfo($certPath);
                $this->line('  Subject: ' . $info['subject']);
                $this->line('  Issuer: ' . $info['issuer']);
                $this->line('  Valid from: ' . $info['validity']['not_before']);
                $this->line('  Valid until: ' . $info['validity']['not_after']);

                return self::SUCCESS;
            } else {
                $this->error('✗ Certificate verification failed!');
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Error verifying certificate: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
