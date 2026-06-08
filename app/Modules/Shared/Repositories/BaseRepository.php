<?php

declare(strict_types=1);

namespace App\Modules\Shared\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent implementation of the repository contract. Concrete repositories
 * extend this and supply their model via model().
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->model = $this->makeModel();
    }

    /**
     * Fully-qualified Eloquent model class this repository manages.
     *
     * @return class-string<Model>
     */
    abstract protected function model(): string;

    protected function makeModel(): Model
    {
        $class = $this->model();

        return new $class();
    }

    /** A fresh query builder for the managed model. */
    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, $columns);
    }

    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        return $this->query()->find($id, $columns);
    }

    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        return $this->query()->findOrFail($id, $columns);
    }

    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->query()->where($field, $value)->first($columns);
    }

    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    public function update(int|string $id, array $attributes): Model
    {
        $model = $this->findOrFail($id);
        $model->fill($attributes)->save();

        return $model->refresh();
    }

    public function delete(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}
