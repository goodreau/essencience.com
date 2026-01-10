<?php

namespace Essencience\Passport\Commands;

use Essencience\Passport\Models\UserCertificate;
use Illuminate\Console\Command;

class ListUserCertificatesCommand extends Command
{
    protected $signature = 'passport:list {user? : User ID or email}';
    protected $description = 'List user certificates';

    public function handle(): int
    {
        $userIdentifier = $this->argument('user');
        $userModel = config('passport.user_model');

        $query = UserCertificate::with('user');

        if ($userIdentifier) {
            $user = is_numeric($userIdentifier)
                ? $userModel::find($userIdentifier)
                : $userModel::where('email', $userIdentifier)->first();

            if (!$user) {
                $this->error("User not found: {$userIdentifier}");
                return self::FAILURE;
            }

            $query->where('user_id', $user->id);
        }

        $certificates = $query->get();

        if ($certificates->isEmpty()) {
            $this->info('No certificates found.');
            return self::SUCCESS;
        }

        $headers = ['Serial', 'User', 'Email', 'Valid Until', 'Status'];
        $rows = $certificates->map(function ($cert) {
            $status = $cert->is_revoked ? 'Revoked' :
                     ($cert->isValid() ? 'Active' : 'Expired');

            return [
                $cert->serial_number,
                $cert->user->name,
                $cert->user->email,
                $cert->valid_until->format('Y-m-d'),
                $status,
            ];
        });

        $this->table($headers, $rows);

        return self::SUCCESS;
    }
}
