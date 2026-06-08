<?php

declare(strict_types=1);

namespace App\Modules\Chat\Repositories\Contracts;

use App\Modules\Chat\Models\Conversation;
use App\Modules\Shared\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ConversationRepositoryInterface extends RepositoryInterface
{
    /** Conversations the user is in, newest-active first, with unread counts. */
    public function forUser(int $userId, int $perPage = 20): LengthAwarePaginator;

    /** The existing 1:1 conversation between two users, if any. */
    public function findDirectBetween(int $userA, int $userB): ?Conversation;

    /** A conversation the user participates in, with participants loaded. */
    public function findForUser(int $conversationId, int $userId): ?Conversation;
}
