<?php

namespace Essencience\Passport\Commands;

use Essencience\Passport\Services\PassportService;
use Illuminate\Console\Command;

class IssueUserCertificateCommand extends Command
{
    protected $signature = 'passport:issue
                            {user : User ID or email}
                            {--validity=365 : Certificate validity in days}
                            {--password= : Password for PKCS12 file}
                            {--download : Display download path}';

    protected $description = 'Issue a certificate for a user';

    public function handle(PassportService $passportService): int
    {
        $userIdentifier = $this->argument('user');
        $userModel = config('passport.user_model');

        // Find user
        $user = is_numeric($userIdentifier)
            ? $userModel::find($userIdentifier)
            : $userModel::where('email', $userIdentifier)->first();

        if (!$user) {
            $this->error("User not found: {$userIdentifier}");
            return self::FAILURE;
        }

        $this->info("Issuing certificate for: {$user->email}");

        try {
            $certificate = $passportService->issueCertificate($user, [
                'validity_days' => $this->option('validity'),
                'password' => $this->option('password'),
            ]);

            $this->info('✓ Certificate issued successfully!');
            $this->line('  User: ' . $user->name);
            $this->line('  Email: ' . $user->email);
            $this->line('  Serial: ' . $certificate->serial_number);
            $this->line('  Valid from: ' . $certificate->valid_from);
            $this->line('  Valid until: ' . $certificate->valid_until);

            if ($this->option('download')) {
                $certPath = config('passport.user_certs_path') . "/{$user->id}";
                $this->newLine();
                $this->info('Certificate files:');
                $this->line('  PKCS12 (for browsers): ' . $certPath . '/certificate.p12');
                $this->line('  PEM certificate: ' . $certPath . '/certificate.pem');
                $this->line('  Private key: ' . $certPath . '/key.pem');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to issue certificate: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
