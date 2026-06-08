<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Shared\Services\BaseService;
use App\Modules\User\Models\Profile;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Orchestrates all authentication use-cases. Controllers stay thin; every
 * business rule (token issuance, verification, reset) lives here.
 */
class AuthService extends BaseService
{
    public function __construct(private readonly UserRepositoryInterface $users)
    {
    }

    /**
     * Register a new user, create their empty profile, dispatch the
     * verification email, and return an authenticated token pair.
     *
     * @return array{user: User, token: string, expires_in: int}
     */
    public function register(array $data): array
    {
        $user = $this->transaction(function () use ($data): User {
            /** @var User $user */
            $user = $this->users->create([
                'name'          => $data['name'],
                'username'      => $data['username'],
                'email'         => $data['email'],
                'phone'         => $data['phone'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender'        => $data['gender'] ?? null,
                'password'      => $data['password'],
                'last_active_at' => now(),
            ]);

            Profile::create(['user_id' => $user->id, 'visibility' => 'public']);

            return $user;
        });

        // Fires the Registered listener which sends the verification email.
        event(new Registered($user));

        return $this->tokenPayload(JWTAuth::fromUser($user), $user->load('profile'));
    }

    /**
     * Authenticate with email-or-username + password.
     *
     * @return array{user: User, token: string, expires_in: int}
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

        $token = Auth::guard('api')->login($user);
        $user->forceFill(['last_active_at' => now()])->save();

        return $this->tokenPayload($token, $user->load('profile'));
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
     * Mark a user's email verified after validating the signed-link hash.
     *
     * @throws ValidationException
     */
    public function verifyEmail(int $userId, string $hash): User
    {
        /** @var User $user */
        $user = $this->users->findOrFail($userId);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw ValidationException::withMessages([
                'hash' => ['Invalid verification link.'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $user;
    }

    public function resendVerification(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->sendEmailVerificationNotification();

        return true;
    }

    /**
     * Trigger a password-reset email. Returns the broker status string.
     */
    public function sendPasswordResetLink(string $email): string
    {
        return Password::broker()->sendResetLink(['email' => $email]);
    }

    /**
     * Complete a password reset using the emailed token.
     */
    public function resetPassword(array $credentials): string
    {
        return Password::broker()->reset($credentials, function (User $user, string $password): void {
            $user->forceFill(['password' => Hash::make($password)])->save();
        });
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
