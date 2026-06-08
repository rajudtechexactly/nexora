<?php

declare(strict_types=1);

namespace App\Modules\Friendship\Repositories\Eloquent;

use App\Modules\Friendship\Models\Friendship;
use App\Modules\Friendship\Repositories\Contracts\FriendshipRepositoryInterface;
use App\Modules\Shared\Repositories\BaseRepository;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FriendshipRepository extends BaseRepository implements FriendshipRepositoryInterface
{
    protected function model(): string
    {
        return Friendship::class;
    }

    public function findBetween(int $userA, int $userB): ?Friendship
    {
        return $this->query()
            ->where(function ($q) use ($userA, $userB) {
                $q->where('requester_id', $userA)->where('addressee_id', $userB);
            })
            ->orWhere(function ($q) use ($userA, $userB) {
                $q->where('requester_id', $userB)->where('addressee_id', $userA);
            })
            ->first();
    }

    public function statusBetween(int $viewerId, int $otherId): string
    {
        if ($viewerId === $otherId) {
            return 'self';
        }

        $row = $this->findBetween($viewerId, $otherId);

        if (! $row) {
            return 'none';
        }

        return match ($row->status) {
            Friendship::STATUS_ACCEPTED => 'friends',
            Friendship::STATUS_BLOCKED  => $row->blocked_by === $viewerId ? 'blocked' : 'blocked_by_them',
            Friendship::STATUS_DECLINED => 'declined',
            Friendship::STATUS_PENDING  => $row->requester_id === $viewerId ? 'pending_outgoing' : 'pending_incoming',
            default                     => 'none',
        };
    }

    public function areFriends(int $userA, int $userB): bool
    {
        $row = $this->findBetween($userA, $userB);

        return $row !== null && $row->status === Friendship::STATUS_ACCEPTED;
    }

    public function friends(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        // The friend is whichever side of an accepted row isn't $userId.
        return User::query()
            ->with('profile')
            ->whereIn('id', function ($q) use ($userId) {
                $q->select(DB::raw('case when requester_id = '.((int) $userId).' then addressee_id else requester_id end'))
                    ->from('friendships')
                    ->where('status', Friendship::STATUS_ACCEPTED)
                    ->where(fn ($w) => $w->where('requester_id', $userId)->orWhere('addressee_id', $userId));
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function friendIds(int $userId): Collection
    {
        return $this->query()
            ->where('status', Friendship::STATUS_ACCEPTED)
            ->where(fn ($w) => $w->where('requester_id', $userId)->orWhere('addressee_id', $userId))
            ->get(['requester_id', 'addressee_id'])
            ->map(fn (Friendship $f) => $f->counterpartId($userId))
            ->values();
    }

    public function incomingRequests(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->with('requester.profile')
            ->where('addressee_id', $userId)
            ->where('status', Friendship::STATUS_PENDING)
            ->latest()
            ->paginate($perPage);
    }

    public function outgoingRequests(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->with('addressee.profile')
            ->where('requester_id', $userId)
            ->where('status', Friendship::STATUS_PENDING)
            ->latest()
            ->paginate($perPage);
    }

    public function blocked(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->with(['requester.profile', 'addressee.profile'])
            ->where('status', Friendship::STATUS_BLOCKED)
            ->where('blocked_by', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function suggestions(int $userId, int $limit = 20): Collection
    {
        $friendIds = $this->friendIds($userId)->all();

        // Anyone already in a relationship (any status) with the user is excluded.
        $relatedIds = $this->query()
            ->where(fn ($w) => $w->where('requester_id', $userId)->orWhere('addressee_id', $userId))
            ->get(['requester_id', 'addressee_id'])
            ->flatMap(fn (Friendship $f) => [$f->requester_id, $f->addressee_id])
            ->push($userId)
            ->unique()
            ->all();

        $query = User::query()->with('profile')->where('is_active', true)->whereNotIn('id', $relatedIds);

        // Prefer friends-of-friends (mutual connections) when available.
        if ($friendIds !== []) {
            $mutualCounts = $this->query()
                ->where('status', Friendship::STATUS_ACCEPTED)
                ->where(function ($w) use ($friendIds) {
                    $w->whereIn('requester_id', $friendIds)->orWhereIn('addressee_id', $friendIds);
                })
                ->get(['requester_id', 'addressee_id'])
                ->flatMap(fn (Friendship $f) => [$f->requester_id, $f->addressee_id])
                ->filter(fn ($id) => ! in_array($id, $relatedIds, true))
                ->countBy()
                ->sortDesc();

            if ($mutualCounts->isNotEmpty()) {
                $ordered = $mutualCounts->keys()->take($limit)->all();
                $users = $query->whereIn('id', $ordered)->get();

                if ($users->count() >= $limit) {
                    return $users->sortBy(fn (User $u) => array_search($u->id, $ordered, true))->values();
                }

                // Top up with other users if not enough mutuals.
                $remaining = $query->whereNotIn('id', $ordered)->inRandomOrder()->limit($limit - $users->count())->get();

                return $users->concat($remaining)->values();
            }
        }

        return $query->inRandomOrder()->limit($limit)->get();
    }
}
