<?php

namespace Essencience\Passport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCertificate extends Model
{
    protected $fillable = [
        'user_id',
        'serial_number',
        'certificate',
        'public_key',
        'subject',
        'issuer',
        'valid_from',
        'valid_until',
        'is_active',
        'is_revoked',
        'revoked_at',
        'revocation_reason',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'revoked_at' => 'datetime',
        'is_active' => 'boolean',
        'is_revoked' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('passport.user_model'));
    }

    public function isValid(): bool
    {
        return $this->is_active
            && !$this->is_revoked
            && now()->between($this->valid_from, $this->valid_until);
    }

    public function revoke(string $reason = 'User requested'): void
    {
        $this->update([
            'is_revoked' => true,
            'revoked_at' => now(),
            'revocation_reason' => $reason,
            'is_active' => false,
        ]);
    }
}
