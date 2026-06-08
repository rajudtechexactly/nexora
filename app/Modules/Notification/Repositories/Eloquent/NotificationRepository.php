<?php

declare(strict_types=1);

namespace App\Modules\Notification\Repositories\Eloquent;

use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Repositories\Contracts\NotificationRepositoryInterface;
use App\Modules\Shared\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    protected function model(): string
    {
        return Notification::class;
    }

    public function forUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->where('recipient_id', $userId)
            ->with('actor.profile')
            ->latest()
            ->paginate($perPage);
    }

    public function unreadCount(int $userId): int
    {
        return $this->query()
            ->where('recipient_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function markAllRead(int $userId): void
    {
        $this->query()
            ->where('recipient_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
