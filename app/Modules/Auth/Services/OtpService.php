<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\EmailOtp;
use App\Modules\Auth\Notifications\OtpNotification;
use App\Modules\Auth\Repositories\Contracts\OtpRepositoryInterface;
use App\Modules\Shared\Services\BaseService;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Issues and validates one-time email codes. The plaintext code lives only
 * long enough to be emailed (via a queued notification); the database keeps
 * just its hash, an expiry, and an attempt counter.
 */
class OtpService extends BaseService
{
    public function __construct(private readonly OtpRepositoryInterface $otps)
    {
    }

    /**
     * Generate a fresh code for the given purpose, invalidate any previous
     * outstanding codes, persist the hash, and queue the email.
     */
    public function send(User $user, string $purpose): void
    {
        $code = $this->generateCode();

        $this->transaction(function () use ($user, $purpose, $code): void {
            $this->otps->invalidateAll($user->id, $purpose);

            $this->otps->create([
                'user_id'    => $user->id,
                'purpose'    => $purpose,
                'code_hash'  => Hash::make($code),
                'expires_at' => now()->addMinutes($this->ttl()),
            ]);
        });

        // Queued (ShouldQueue) — the SMTP send happens on the worker.
        $user->notify(new OtpNotification($code, $purpose, $this->ttl()));
    }

    /**
     * Validate a submitted code. Consumes it on success; counts the attempt
     * (and burns the code past the limit) on failure.
     *
     * @throws ValidationException
     */
    public function verify(User $user, string $purpose, string $code): void
    {
        $otp = $this->otps->latestActive($user->id, $purpose);

        if (! $otp) {
            $this->reject('The code is invalid or has expired. Please request a new one.');
        }

        if ($otp->attempts >= $this->maxAttempts()) {
            $otp->forceFill(['consumed_at' => now()])->save();
            $this->reject('Too many incorrect attempts. Please request a new code.');
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');
            $this->reject('The code you entered is incorrect.');
        }

        $otp->forceFill(['consumed_at' => now()])->save();
    }

    /**
     * @throws ValidationException
     */
    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['otp' => [$message]]);
    }

    private function generateCode(): string
    {
        $length = $this->length();

        return str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    private function length(): int
    {
        return (int) config('otp.length', 6);
    }

    private function ttl(): int
    {
        return (int) config('otp.ttl', 10);
    }

    private function maxAttempts(): int
    {
        return (int) config('otp.max_attempts', 5);
    }
}
