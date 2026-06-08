<?php

declare(strict_types=1);

namespace App\Modules\Notification\Repositories\Contracts;

use App\Modules\Shared\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface extends RepositoryInterface
{
    public function forUser(int $userId, int $perPage = 20): LengthAwarePaginator;

    public function unreadCount(int $userId): int;

    public function markAllRead(int $userId): void;
}
