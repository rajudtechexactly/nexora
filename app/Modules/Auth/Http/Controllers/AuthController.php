<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Modules\Auth\Http\Requests\ChangePasswordRequest;
use App\Modules\Auth\Http\Requests\ForgotPasswordRequest;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Requests\RegisterRequest;
use App\Modules\Auth\Http\Requests\ResetPasswordRequest;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Shared\Http\Controllers\ApiController;
use App\Modules\User\Http\Resources\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AuthController extends ApiController
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $this->auth->register($request->validated());

        return $this->created($this->authResponse($payload), 'Registration successful. Please verify your email.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $this->auth->login(
            (string) $request->string('login'),
            (string) $request->string('password'),
        );

        return $this->ok($this->authResponse($payload), 'Logged in successfully.');
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

    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        $user = $this->auth->verifyEmail($id, $hash);

        return $this->ok(['verified' => true, 'email' => $user->email], 'Email verified successfully.');
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $sent = $this->auth->resendVerification($request->user());

        return $sent
            ? $this->ok(message: 'Verification email sent.')
            : $this->ok(message: 'Your email is already verified.');
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->auth->sendPasswordResetLink((string) $request->string('email'));

        // Always return success-shaped to avoid leaking which emails exist.
        return $status === Password::RESET_LINK_SENT
            ? $this->ok(message: 'Password reset link sent to your email.')
            : $this->ok(message: 'If that email exists, a reset link has been sent.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->auth->resetPassword($request->only('email', 'password', 'password_confirmation', 'token'));

        return $status === Password::PASSWORD_RESET
            ? $this->ok(message: 'Password has been reset. You can now log in.')
            : $this->fail(__($status), 422, code: 'reset_failed');
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
