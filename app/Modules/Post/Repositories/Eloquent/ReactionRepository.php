<?php

declare(strict_types=1);

namespace App\Modules\Post\Repositories\Eloquent;

use App\Modules\Post\Models\Reaction;
use App\Modules\Post\Repositories\Contracts\ReactionRepositoryInterface;
use App\Modules\Shared\Repositories\BaseRepository;

class ReactionRepository extends BaseRepository implements ReactionRepositoryInterface
{
    protected function model(): string
    {
        return Reaction::class;
    }

    public function findFor(int $userId, string $reactableType, int $reactableId): ?Reaction
    {
        /** @var Reaction|null $reaction */
        $reaction = $this->query()
            ->where('user_id', $userId)
            ->where('reactable_type', $reactableType)
            ->where('reactable_id', $reactableId)
            ->first();

        return $reaction;
    }
}
