<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\EmailOtp;
use App\Modules\Shared\Services\BaseService;
use App\Modules\User\Models\Profile;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Orchestrates all authentication use-cases. Controllers stay thin; every
 * business rule (registration, OTP verification, token issuance, reset) lives
 * here.
 *
 * Auth flow: a new account is created unverified and is NOT logged in. A
 * verification OTP is emailed (queued); the user submits it to verify their
 * email and receive their first token. Password resets are likewise gated by
 * an emailed OTP rather than a reset link.
 */
class AuthService extends BaseService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly OtpService $otp,
    ) {
    }

    /**
     * Register a new user (unverified) with an empty profile and email them a
     * verification OTP. No token is issued — the account cannot log in until
     * the OTP is verified.
     */
    public function register(array $data): User
    {
        $user = $this->transaction(function () use ($data): User {
            /** @var User $user */
            $user = $this->users->create([
                'name'           => $data['name'],
                'username'       => $data['username'],
                'email'          => $data['email'],
                'phone'          => $data['phone'] ?? null,
                'date_of_birth'  => $data['date_of_birth'] ?? null,
                'gender'         => $data['gender'] ?? null,
                'password'       => $data['password'],
                'last_active_at' => now(),
            ]);

            Profile::create(['user_id' => $user->id, 'visibility' => 'public']);

            return $user;
        });

        $this->otp->send($user, EmailOtp::PURPOSE_REGISTRATION);

        return $user;
    }

    /**
     * Verify the registration OTP, mark the email verified, and issue the
     * first authenticated token.
     *
     * @return array{user: User, token: string, expires_in: int}
     *
     * @throws ValidationException
     */
    public function verifyRegistrationOtp(string $email, string $code): array
    {
        $user = $this->users->findByEmail($email);

        // Same error as an invalid code so we never reveal which emails exist.
        if (! $user) {
            $this->rejectOtp();
        }

        $this->otp->verify($user, EmailOtp::PURPOSE_REGISTRATION, $code);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $token = Auth::guard('api')->login($user);
        $user->forceFill(['last_active_at' => now()])->save();

        return $this->tokenPayload($token, $user->load('profile'));
    }

    /**
     * Re-send a registration OTP. Always succeeds outwardly (no enumeration);
     * only actually sends when an unverified account matches.
     */
    public function resendRegistrationOtp(string $email): void
    {
        $user = $this->users->findByEmail($email);

        if ($user && ! $user->hasVerifiedEmail()) {
            $this->otp->send($user, EmailOtp::PURPOSE_REGISTRATION);
        }
    }

    /**
     * Authenticate with email-or-username + password.
     *
     * Returns a discriminated result:
     *  - ['status' => 'authenticated', user, token, expires_in]
     *  - ['status' => 'email_not_verified', email]  (a fresh OTP is emailed)
     *
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function login(string $login, string $password): array
    {
        $user = $this->users->findByLogin($login);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['These credentials do not match our records.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'login' => ['This account has been deactivated.'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            // Issue a fresh code so the client can route to the OTP screen.
            $this->otp->send($user, EmailOtp::PURPOSE_REGISTRATION);

            return ['status' => 'email_not_verified', 'email' => $user->email];
        }

        $token = Auth::guard('api')->login($user);
        $user->forceFill(['last_active_at' => now()])->save();

        return ['status' => 'authenticated'] + $this->tokenPayload($token, $user->load('profile'));
    }

    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    /**
     * Issue a fresh token from the current (possibly expired-but-refreshable) one.
     *
     * @return array{user: User, token: string, expires_in: int}
     */
    public function refresh(): array
    {
        $token = Auth::guard('api')->refresh();
        /** @var User $user */
        $user = Auth::guard('api')->setToken($token)->user();

        return $this->tokenPayload($token, $user->load('profile'));
    }

    public function me(): User
    {
        /** @var User $user */
        $user = Auth::guard('api')->user();

        return $user->load('profile');
    }

    /**
     * Email a password-reset OTP. Always success-shaped to avoid leaking which
     * emails are registered.
     */
    public function forgotPassword(string $email): void
    {
        $user = $this->users->findByEmail($email);

        if ($user) {
            $this->otp->send($user, EmailOtp::PURPOSE_PASSWORD_RESET);
        }
    }

    /**
     * Reset a password after validating the emailed OTP.
     *
     * @throws ValidationException
     */
    public function resetPassword(string $email, string $code, string $newPassword): void
    {
        $user = $this->users->findByEmail($email);

        if (! $user) {
            $this->rejectOtp();
        }

        $this->otp->verify($user, EmailOtp::PURPOSE_PASSWORD_RESET, $code);

        $user->forceFill(['password' => Hash::make($newPassword)])->save();
    }

    /**
     * Change password for an authenticated user (requires current password).
     *
     * @throws ValidationException
     */
    public function changePassword(User $user, string $current, string $new): void
    {
        if (! Hash::check($current, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Your current password is incorrect.'],
            ]);
        }

        $user->forceFill(['password' => Hash::make($new)])->save();
    }

    /**
     * @throws ValidationException
     */
    private function rejectOtp(): never
    {
        throw ValidationException::withMessages([
            'otp' => ['The code is invalid or has expired. Please request a new one.'],
        ]);
    }

    /**
     * @return array{user: User, token: string, expires_in: int}
     */
    private function tokenPayload(string $token, User $user): array
    {
        return [
            'user'       => $user,
            'token'      => $token,
            'expires_in' => (int) config('jwt.ttl') * 60,
        ];
    }
}
