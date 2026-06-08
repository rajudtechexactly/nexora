<?php

declare(strict_types=1);

namespace App\Modules\Auth\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A short-lived, single-use email verification code. Issued for two purposes:
 * verifying a new account before its first login, and authorizing a password
 * reset. The plaintext code is never persisted — only its hash.
 */
class EmailOtp extends Model
{
    public const PURPOSE_REGISTRATION = 'registration';

    public const PURPOSE_PASSWORD_RESET = 'password_reset';

    protected $fillable = [
        'user_id',
        'purpose',
        'code_hash',
        'attempts',
        'expires_at',
        'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts'    => 'integer',
            'expires_at'  => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
