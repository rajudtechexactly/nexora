<?php

declare(strict_types=1);

namespace App\Modules\User\Repositories\Eloquent;

use App\Modules\Shared\Repositories\BaseRepository;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected function model(): string
    {
        return User::class;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    public function findByUsername(string $username): ?User
    {
        return $this->query()->where('username', $username)->first();
    }

    public function findByLogin(string $login): ?User
    {
        return $this->query()
            ->where('email', $login)
            ->orWhere('username', $login)
            ->first();
    }

    public function search(string $term, int $excludeUserId, int $perPage = 20): LengthAwarePaginator
    {
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $this->query()
            ->with('profile')
            ->where('id', '!=', $excludeUserId)
            ->where('is_active', true)
            ->where(function ($q) use ($like) {
                $q->where('name', 'ilike', $like)
                    ->orWhere('username', 'ilike', $like)
                    ->orWhere('email', 'ilike', $like);
            })
            ->orderBy('name')
            ->paginate($perPage);
    }
}
