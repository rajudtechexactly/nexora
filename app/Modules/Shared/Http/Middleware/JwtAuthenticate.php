<?php

declare(strict_types=1);

namespace App\Modules\Shared\Http\Middleware;

use App\Modules\Shared\Http\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the authenticated user from the bearer token and returns clean
 * JSON errors (expired / invalid / missing) instead of throwing raw exceptions.
 */
final class JwtAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return ApiResponse::error('User not found.', 401, code: 'user_not_found');
            }
        } catch (TokenExpiredException) {
            return ApiResponse::error('Token has expired.', 401, code: 'token_expired');
        } catch (TokenInvalidException) {
            return ApiResponse::error('Token is invalid.', 401, code: 'token_invalid');
        } catch (\Throwable) {
            return ApiResponse::error('Authorization token not found.', 401, code: 'token_absent');
        }

        return $next($request);
    }
}
