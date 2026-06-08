<?php

declare(strict_types=1);

namespace App\Modules\Shared\Http\Controllers;

use App\Modules\Shared\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;

/**
 * Base controller for every API endpoint. Provides the shared response
 * helpers and the authorize/validate traits Laravel 11+ no longer auto-wires.
 */
abstract class ApiController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    protected function ok(mixed $data = null, string $message = 'OK', array $meta = []): JsonResponse
    {
        return ApiResponse::success($data, $message, 200, $meta);
    }

    protected function created(mixed $data = null, string $message = 'Created successfully.'): JsonResponse
    {
        return ApiResponse::success($data, $message, 201);
    }

    protected function noContent(string $message = 'Done.'): JsonResponse
    {
        return ApiResponse::success(null, $message, 200);
    }

    protected function fail(string $message, int $status = 400, array $errors = [], ?string $code = null): JsonResponse
    {
        return ApiResponse::error($message, $status, $errors, $code);
    }
}
