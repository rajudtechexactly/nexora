<?php

declare(strict_types=1);

namespace App\Modules\Post\Repositories\Contracts;

use App\Modules\Post\Models\Reaction;
use App\Modules\Shared\Repositories\RepositoryInterface;

interface ReactionRepositoryInterface extends RepositoryInterface
{
    /** The reaction a user has on a given target, if any. */
    public function findFor(int $userId, string $reactableType, int $reactableId): ?Reaction;
}
