<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories\Contracts;

use App\Modules\Auth\Models\EmailOtp;
use App\Modules\Shared\Repositories\RepositoryInterface;

interface OtpRepositoryInterface extends RepositoryInterface
{
    /** The newest still-valid (unconsumed, unexpired) code for a user + purpose. */
    public function latestActive(int $userId, string $purpose): ?EmailOtp;

    /** Burn every outstanding code for a user + purpose (called before issuing a new one). */
    public function invalidateAll(int $userId, string $purpose): void;
}
