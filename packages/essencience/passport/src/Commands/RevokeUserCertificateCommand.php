<?php

namespace Essencience\Passport\Commands;

use Essencience\Passport\Models\UserCertificate;
use Illuminate\Console\Command;

class RevokeUserCertificateCommand extends Command
{
    protected $signature = 'passport:revoke
                            {serial : Certificate serial number}
                            {--reason= : Revocation reason}';

    protected $description = 'Revoke a user certificate';

    public function handle(): int
    {
        $serial = $this->argument('serial');
        $reason = $this->option('reason') ?? 'Revoked by administrator';

        $certificate = UserCertificate::where('serial_number', $serial)->first();

        if (!$certificate) {
            $this->error("Certificate not found: {$serial}");
            return self::FAILURE;
        }

        if ($certificate->is_revoked) {
            $this->warn('Certificate is already revoked.');
            return self::SUCCESS;
        }

        $certificate->revoke($reason);

        $this->info('✓ Certificate revoked successfully!');
        $this->line('  Serial: ' . $certificate->serial_number);
        $this->line('  User: ' . $certificate->user->name);
        $this->line('  Reason: ' . $reason);
        $this->line('  Revoked at: ' . $certificate->revoked_at);

        return self::SUCCESS;
    }
}
