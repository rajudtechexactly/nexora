<?php

declare(strict_types=1);

namespace App\Modules\Shared\Http;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Single source of truth for the JSON envelope every API response uses.
 *
 * Shape: { success, message, data, errors, meta }
 */
final class ApiResponse
{
    /**
     * A successful response carrying a payload.
     */
    public static function success(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        // Eloquent API Resources / paginators are unwrapped so the envelope stays flat.
        [$data, $meta] = self::normalize($data, $meta);

        return response()->json(array_filter([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta ?: null,
        ], static fn ($v) => $v !== null), $status);
    }

    /**
     * A failed response. $errors holds field-level validation details when present.
     */
    public static function error(
        string $message = 'Something went wrong.',
        int $status = 400,
        array $errors = [],
        ?string $code = null,
    ): JsonResponse {
        return response()->json(array_filter([
            'success' => false,
            'message' => $message,
            'code'    => $code,
            'errors'  => $errors ?: null,
        ], static fn ($v) => $v !== null), $status);
    }

    /**
     * Map any thrown exception to a consistent JSON error envelope.
     */
    public static function fromException(Throwable $e): JsonResponse
    {
        return match (true) {
            $e instanceof ValidationException => self::error(
                'The given data was invalid.',
                422,
                $e->errors(),
                'validation_error',
            ),
            $e instanceof AuthenticationException => self::error(
                'Unauthenticated.',
                401,
                code: 'unauthenticated',
            ),
            $e instanceof JWTException => self::error(
                $e->getMessage() ?: 'Token error.',
                401,
                code: 'token_invalid',
            ),
            $e instanceof AuthorizationException => self::error(
                $e->getMessage() ?: 'This action is unauthorized.',
                403,
                code: 'forbidden',
            ),
            $e instanceof ModelNotFoundException => self::error(
                'Resource not found.',
                404,
                code: 'not_found',
            ),
            $e instanceof HttpExceptionInterface => self::error(
                $e->getMessage() ?: 'HTTP error.',
                $e->getStatusCode(),
                code: 'http_error',
            ),
            default => self::error(
                config('app.debug') ? $e->getMessage() : 'Server error.',
                500,
                errors: config('app.debug') ? ['trace' => collect($e->getTrace())->take(5)->all()] : [],
                code: 'server_error',
            ),
        };
    }

    /**
     * Flatten API Resources and paginators into (data, meta) parts.
     *
     * @return array{0: mixed, 1: array}
     */
    private static function normalize(mixed $data, array $meta): array
    {
        if ($data instanceof ResourceCollection) {
            $resource = $data->resource;

            if ($resource instanceof AbstractPaginator) {
                return [
                    $data->resolve(),
                    array_merge($meta, self::paginationMeta($resource)),
                ];
            }

            return [$data->resolve(), $meta];
        }

        if ($data instanceof JsonResource) {
            return [$data->resolve(), $meta];
        }

        if ($data instanceof AbstractPaginator) {
            return [
                $data->items(),
                array_merge($meta, self::paginationMeta($data)),
            ];
        }

        return [$data, $meta];
    }

    private static function paginationMeta(AbstractPaginator $paginator): array
    {
        $meta = [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'has_more'     => $paginator->hasMorePages(),
        ];

        // LengthAwarePaginator exposes totals; cursor/simple paginators do not.
        if (method_exists($paginator, 'total')) {
            $meta['total']     = $paginator->total();
            $meta['last_page'] = $paginator->lastPage();
        }

        return ['pagination' => $meta];
    }
}
