<?php

declare(strict_types=1);

namespace App\Modules\Friendship\Services;

use App\Modules\Friendship\Events\FriendRequestAccepted;
use App\Modules\Friendship\Events\FriendRequestSent;
use App\Modules\Friendship\Models\Friendship;
use App\Modules\Friendship\Repositories\Contracts\FriendshipRepositoryInterface;
use App\Modules\Shared\Services\BaseService;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Validation\ValidationException;

class FriendshipService extends BaseService
{
    public function __construct(
        private readonly FriendshipRepositoryInterface $friendships,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * Send a friend request. Handles the edge cases the way users expect:
     * mutual pending requests auto-become friends; declined relationships can
     * be retried; blocked relationships are refused.
     */
    public function sendRequest(User $actor, int $targetId): Friendship
    {
        if ($actor->id === $targetId) {
            $this->reject('You cannot send a friend request to yourself.');
        }

        // Ensures the target exists.
        $this->users->findOrFail($targetId);

        $existing = $this->friendships->findBetween($actor->id, $targetId);

        if ($existing) {
            return $this->handleExisting($actor, $targetId, $existing);
        }

        $friendship = $this->friendships->create([
            'requester_id' => $actor->id,
            'addressee_id' => $targetId,
            'status'       => Friendship::STATUS_PENDING,
        ]);

        event(new FriendRequestSent($friendship));

        return $friendship;
    }

    public function accept(User $actor, int $requesterId): Friendship
    {
        $row = $this->friendships->findBetween($actor->id, $requesterId);

        if (! $row || $row->status !== Friendship::STATUS_PENDING || $row->addressee_id !== $actor->id) {
            $this->reject('No pending friend request from this user.');
        }

        $row->forceFill([
            'status'       => Friendship::STATUS_ACCEPTED,
            'responded_at' => now(),
        ])->save();

        event(new FriendRequestAccepted($row));

        return $row;
    }

    public function decline(User $actor, int $requesterId): void
    {
        $row = $this->friendships->findBetween($actor->id, $requesterId);

        if (! $row || $row->status !== Friendship::STATUS_PENDING || $row->addressee_id !== $actor->id) {
            $this->reject('No pending friend request from this user.');
        }

        $row->forceFill([
            'status'       => Friendship::STATUS_DECLINED,
            'responded_at' => now(),
        ])->save();
    }

    public function cancel(User $actor, int $targetId): void
    {
        $row = $this->friendships->findBetween($actor->id, $targetId);

        if (! $row || $row->status !== Friendship::STATUS_PENDING || $row->requester_id !== $actor->id) {
            $this->reject('No outgoing request to cancel.');
        }

        $row->delete();
    }

    public function unfriend(User $actor, int $targetId): void
    {
        $row = $this->friendships->findBetween($actor->id, $targetId);

        if (! $row || $row->status !== Friendship::STATUS_ACCEPTED) {
            $this->reject('You are not friends with this user.');
        }

        $row->delete();
    }

    public function block(User $actor, int $targetId): Friendship
    {
        if ($actor->id === $targetId) {
            $this->reject('You cannot block yourself.');
        }

        $this->users->findOrFail($targetId);

        return $this->transaction(function () use ($actor, $targetId): Friendship {
            $row = $this->friendships->findBetween($actor->id, $targetId);

            if ($row) {
                $row->forceFill([
                    'status'       => Friendship::STATUS_BLOCKED,
                    'blocked_by'   => $actor->id,
                    'responded_at' => now(),
                ])->save();

                return $row;
            }

            return $this->friendships->create([
                'requester_id' => $actor->id,
                'addressee_id' => $targetId,
                'status'       => Friendship::STATUS_BLOCKED,
                'blocked_by'   => $actor->id,
                'responded_at' => now(),
            ]);
        });
    }

    public function unblock(User $actor, int $targetId): void
    {
        $row = $this->friendships->findBetween($actor->id, $targetId);

        if (! $row || $row->status !== Friendship::STATUS_BLOCKED || $row->blocked_by !== $actor->id) {
            $this->reject('You have not blocked this user.');
        }

        $row->delete();
    }

    /**
     * Resolve how an existing relationship row should react to a new request.
     */
    private function handleExisting(User $actor, int $targetId, Friendship $existing): Friendship
    {
        switch ($existing->status) {
            case Friendship::STATUS_ACCEPTED:
                $this->reject('You are already friends.');
                // no break

            case Friendship::STATUS_BLOCKED:
                $this->reject('Unable to send a friend request to this user.');
                // no break

            case Friendship::STATUS_PENDING:
                // They already invited us → accept; otherwise it's a duplicate.
                if ($existing->addressee_id === $actor->id) {
                    return $this->accept($actor, $existing->requester_id);
                }
                $this->reject('Friend request already sent.');
                // no break

            default: // declined → retry as a fresh request from the actor.
                $existing->forceFill([
                    'requester_id' => $actor->id,
                    'addressee_id' => $targetId,
                    'status'       => Friendship::STATUS_PENDING,
                    'responded_at' => null,
                ])->save();

                event(new FriendRequestSent($existing));

                return $existing;
        }
    }

    /** @throws ValidationException */
    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['friendship' => [$message]]);
    }
}
