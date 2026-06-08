<?php

declare(strict_types=1);

namespace App\Modules\Chat\Repositories\Eloquent;

use App\Modules\Chat\Models\Conversation;
use App\Modules\Chat\Repositories\Contracts\ConversationRepositoryInterface;
use App\Modules\Shared\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ConversationRepository extends BaseRepository implements ConversationRepositoryInterface
{
    protected function model(): string
    {
        return Conversation::class;
    }

    public function forUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->whereHas('participants', fn (Builder $q) => $q->where('users.id', $userId))
            ->with([
                'participants.profile',
                'latestMessage.sender.profile',
            ])
            // Unread = messages from others newer than this user's last_read_at.
            ->withCount(['messages as unread_count' => function (Builder $q) use ($userId) {
                $q->where('messages.user_id', '!=', $userId)
                    ->whereRaw(
                        'messages.created_at > COALESCE((select last_read_at from conversation_user '.
                        'where conversation_user.conversation_id = conversations.id '.
                        'and conversation_user.user_id = ?), \'1970-01-01 00:00:00\')',
                        [$userId]
                    );
            }])
            ->orderByDesc('last_message_at')
            ->paginate($perPage);
    }

    public function findDirectBetween(int $userA, int $userB): ?Conversation
    {
        /** @var Conversation|null $conversation */
        $conversation = $this->query()
            ->where('type', Conversation::TYPE_DIRECT)
            ->whereHas('participants', fn (Builder $q) => $q->where('users.id', $userA))
            ->whereHas('participants', fn (Builder $q) => $q->where('users.id', $userB))
            ->with('participants.profile')
            ->first();

        return $conversation;
    }

    public function findForUser(int $conversationId, int $userId): ?Conversation
    {
        /** @var Conversation|null $conversation */
        $conversation = $this->query()
            ->where('id', $conversationId)
            ->whereHas('participants', fn (Builder $q) => $q->where('users.id', $userId))
            ->with('participants.profile')
            ->first();

        return $conversation;
    }
}
