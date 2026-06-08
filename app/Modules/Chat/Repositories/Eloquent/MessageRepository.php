<?php

declare(strict_types=1);

namespace App\Modules\Chat\Repositories\Eloquent;

use App\Modules\Chat\Models\Message;
use App\Modules\Chat\Repositories\Contracts\MessageRepositoryInterface;
use App\Modules\Shared\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MessageRepository extends BaseRepository implements MessageRepositoryInterface
{
    protected function model(): string
    {
        return Message::class;
    }

    public function forConversation(int $conversationId, int $perPage = 30): LengthAwarePaginator
    {
        return $this->query()
            ->where('conversation_id', $conversationId)
            ->with('sender.profile')
            ->latest()
            ->paginate($perPage);
    }
}
