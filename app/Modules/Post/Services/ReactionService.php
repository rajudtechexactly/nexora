<?php

declare(strict_types=1);

namespace App\Modules\Post\Services;

use App\Modules\Post\Events\PostReacted;
use App\Modules\Post\Events\PostStatsUpdated;
use App\Modules\Post\Models\Post;
use App\Modules\Post\Models\Reaction;
use App\Modules\Post\Repositories\Contracts\ReactionRepositoryInterface;
use App\Modules\Shared\Services\BaseService;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Adds/changes/removes a user's reaction on a post or comment, keeping the
 * target's denormalised reactions_count in sync.
 */
class ReactionService extends BaseService
{
    public function __construct(private readonly ReactionRepositoryInterface $reactions)
    {
    }

    public function react(User $actor, Model $target, string $type): Reaction
    {
        if (! in_array($type, Reaction::TYPES, true)) {
            throw ValidationException::withMessages(['type' => ['Unsupported reaction.']]);
        }

        [$reaction, $isNew] = $this->transaction(function () use ($actor, $target, $type): array {
            $existing = $this->reactions->findFor($actor->id, $target->getMorphClass(), $target->getKey());

            if ($existing) {
                if ($existing->type !== $type) {
                    $existing->update(['type' => $type]);
                }

                return [$existing, false];
            }

            /** @var Reaction $reaction */
            $reaction = $target->reactions()->create(['user_id' => $actor->id, 'type' => $type]);
            $target->increment('reactions_count');

            return [$reaction, true];
        });

        if ($isNew) {
            event(new PostReacted($reaction));
        }

        $this->broadcastStats($target);

        return $reaction;
    }

    public function unreact(User $actor, Model $target): void
    {
        $this->transaction(function () use ($actor, $target): void {
            $existing = $this->reactions->findFor($actor->id, $target->getMorphClass(), $target->getKey());

            if ($existing) {
                $existing->delete();
                $target->decrement('reactions_count');
            }
        });

        $this->broadcastStats($target);
    }

    /** Broadcast a post's fresh counts to the public feed channel. */
    private function broadcastStats(Model $target): void
    {
        if ($target instanceof Post) {
            $target->refresh();
            event(new PostStatsUpdated($target->id, (int) $target->reactions_count, (int) $target->comments_count));
        }
    }
}
