<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Modules\Auth\Http\Requests\ChangePasswordRequest;
use App\Modules\Auth\Http\Requests\ForgotPasswordRequest;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Requests\RegisterRequest;
use App\Modules\Auth\Http\Requests\ResendOtpRequest;
use App\Modules\Auth\Http\Requests\ResetPasswordRequest;
use App\Modules\Auth\Http\Requests\VerifyOtpRequest;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Shared\Http\Controllers\ApiController;
use App\Modules\User\Http\Resources\ProfileResource;
use Illuminate\Http\JsonResponse;

class AuthController extends ApiController
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->auth->register($request->validated());

        return $this->created([
            'email'                 => $user->email,
            'verification_required' => true,
        ], 'Registration successful. We emailed a verification code to complete your sign-in.');
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $payload = $this->auth->verifyRegistrationOtp(
            (string) $request->string('email'),
            (string) $request->string('otp'),
        );

        return $this->ok($this->authResponse($payload), 'Email verified. You are now signed in.');
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $this->auth->resendRegistrationOtp((string) $request->string('email'));

        return $this->ok(message: 'If your account needs verification, a new code has been sent.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login(
            (string) $request->string('login'),
            (string) $request->string('password'),
        );

        if (($result['status'] ?? null) === 'email_not_verified') {
            return $this->fail(
                'Your email is not verified yet. We have sent a new verification code to your email.',
                403,
                ['email' => [$result['email']]],
                'email_not_verified',
            );
        }

        return $this->ok($this->authResponse($result), 'Logged in successfully.');
    }

    public function logout(): JsonResponse
    {
        $this->auth->logout();

        return $this->ok(message: 'Logged out successfully.');
    }

    public function refresh(): JsonResponse
    {
        $payload = $this->auth->refresh();

        return $this->ok($this->authResponse($payload), 'Token refreshed.');
    }

    public function me(): JsonResponse
    {
        return $this->ok(new ProfileResource($this->auth->me()));
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->auth->forgotPassword((string) $request->string('email'));

        // Always success-shaped to avoid leaking which emails exist.
        return $this->ok(message: 'If that email exists, a password reset code has been sent.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->auth->resetPassword(
            (string) $request->string('email'),
            (string) $request->string('otp'),
            (string) $request->string('password'),
        );

        return $this->ok(message: 'Password has been reset. You can now log in.');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->auth->changePassword(
            $request->user(),
            (string) $request->string('current_password'),
            (string) $request->string('password'),
        );

        return $this->ok(message: 'Password changed successfully.');
    }

    /**
     * @param  array{user: \App\Modules\User\Models\User, token: string, expires_in: int}  $payload
     */
    private function authResponse(array $payload): array
    {
        return [
            'token_type'   => 'bearer',
            'access_token' => $payload['token'],
            'expires_in'   => $payload['expires_in'],
            'user'         => new ProfileResource($payload['user']),
        ];
    }
}
