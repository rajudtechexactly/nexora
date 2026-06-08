<?php

declare(strict_types=1);

namespace App\Modules\Friendship\Repositories\Contracts;

use App\Modules\Friendship\Models\Friendship;
use App\Modules\Shared\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface FriendshipRepositoryInterface extends RepositoryInterface
{
    /** The relationship row between two users, in either direction. */
    public function findBetween(int $userA, int $userB): ?Friendship;

    /**
     * Viewer-relative status: self | friends | pending_outgoing |
     * pending_incoming | blocked | blocked_by_them | declined | none.
     */
    public function statusBetween(int $viewerId, int $otherId): string;

    public function areFriends(int $userA, int $userB): bool;

    /** Accepted friends of a user (paginated User models). */
    public function friends(int $userId, int $perPage = 20): LengthAwarePaginator;

    /** Ids of a user's accepted friends. */
    public function friendIds(int $userId): Collection;

    /** Pending requests addressed TO the user. */
    public function incomingRequests(int $userId, int $perPage = 20): LengthAwarePaginator;

    /** Pending requests the user SENT. */
    public function outgoingRequests(int $userId, int $perPage = 20): LengthAwarePaginator;

    /** Users this user has blocked. */
    public function blocked(int $userId, int $perPage = 20): LengthAwarePaginator;

    /** Friend suggestions (friends-of-friends, then fill with others). */
    public function suggestions(int $userId, int $limit = 20): Collection;
}
