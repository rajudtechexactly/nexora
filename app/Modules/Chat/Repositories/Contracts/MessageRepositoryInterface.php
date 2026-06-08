<?php

declare(strict_types=1);

namespace App\Modules\Chat\Repositories\Contracts;

use App\Modules\Shared\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MessageRepositoryInterface extends RepositoryInterface
{
    /** Messages in a conversation, newest first, with sender loaded. */
    public function forConversation(int $conversationId, int $perPage = 30): LengthAwarePaginator;
}
