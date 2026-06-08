<?php

declare(strict_types=1);

namespace App\Modules\Shared\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Base application service. Holds cross-cutting helpers (transactions) that
 * domain services reuse. Business rules live in the concrete services.
 */
abstract class BaseService
{
    /**
     * Run a unit of work inside a database transaction.
     *
     * @template T
     * @param  callable():T  $callback
     * @return T
     *
     * @throws Throwable
     */
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
