<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories\Eloquent;

use App\Modules\Auth\Models\EmailOtp;
use App\Modules\Auth\Repositories\Contracts\OtpRepositoryInterface;
use App\Modules\Shared\Repositories\BaseRepository;

class OtpRepository extends BaseRepository implements OtpRepositoryInterface
{
    protected function model(): string
    {
        return EmailOtp::class;
    }

    public function latestActive(int $userId, string $purpose): ?EmailOtp
    {
        /** @var EmailOtp|null $otp */
        $otp = $this->query()
            ->where('user_id', $userId)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        return $otp;
    }

    public function invalidateAll(int $userId, string $purpose): void
    {
        $this->query()
            ->where('user_id', $userId)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);
    }
}
