<?php

declare(strict_types=1);

namespace App\Modules\User\Repositories\Contracts;

use App\Modules\Shared\Repositories\RepositoryInterface;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findByUsername(string $username): ?User;

    /** Resolve a user by email OR username (login identifier). */
    public function findByLogin(string $login): ?User;

    /** Full-text-ish search across name, username and email. */
    public function search(string $term, int $excludeUserId, int $perPage = 20): LengthAwarePaginator;
}
