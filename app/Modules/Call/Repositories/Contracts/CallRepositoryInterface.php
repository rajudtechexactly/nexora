<?php

declare(strict_types=1);

namespace App\Modules\Call\Repositories\Contracts;

use App\Modules\Shared\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CallRepositoryInterface extends RepositoryInterface
{
    /** Call history (incoming + outgoing) for a user, newest first. */
    public function history(int $userId, int $perPage = 20): LengthAwarePaginator;
}
