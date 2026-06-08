<?php

declare(strict_types=1);

namespace App\Modules\Shared\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Contract every repository fulfils. Services depend on this abstraction,
 * never on Eloquent directly — keeping persistence swappable and testable.
 */
interface RepositoryInterface
{
    public function all(array $columns = ['*']): Collection;

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    public function find(int|string $id, array $columns = ['*']): ?Model;

    public function findOrFail(int|string $id, array $columns = ['*']): Model;

    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model;

    public function create(array $attributes): Model;

    public function update(int|string $id, array $attributes): Model;

    public function delete(int|string $id): bool;
}
