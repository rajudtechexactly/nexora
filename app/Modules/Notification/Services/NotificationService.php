<?php

declare(strict_types=1);

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Events\NotificationCreated;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Repositories\Contracts\NotificationRepositoryInterface;
use App\Modules\Shared\Services\BaseService;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NotificationService extends BaseService
{
    public function __construct(private readonly NotificationRepositoryInterface $notifications)
    {
    }

    /**
     * Create + broadcast a notification. No-ops when the actor is the recipient
     * (you don't get notified about your own actions).
     */
    public function push(int $recipientId, ?int $actorId, string $type, ?Model $notifiable = null, array $data = []): ?Notification
    {
        if ($actorId !== null && $recipientId === $actorId) {
            return null;
        }

        /** @var Notification $notification */
        $notification = $this->notifications->create([
            'recipient_id'    => $recipientId,
            'actor_id'        => $actorId,
            'type'            => $type,
            'notifiable_type' => $notifiable?->getMorphClass(),
            'notifiable_id'   => $notifiable?->getKey(),
            'data'            => $data !== [] ? $data : null,
        ]);

        event(new NotificationCreated($notification));

        return $notification;
    }

    public function list(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->notifications->forUser($userId, $perPage);
    }

    public function unreadCount(int $userId): int
    {
        return $this->notifications->unreadCount($userId);
    }

    public function markRead(User $user, int $notificationId): void
    {
        /** @var Notification|null $notification */
        $notification = Notification::query()
            ->where('id', $notificationId)
            ->where('recipient_id', $user->id)
            ->first();

        if (! $notification) {
            throw (new ModelNotFoundException())->setModel(Notification::class);
        }

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }
    }

    public function markAllRead(User $user): void
    {
        $this->notifications->markAllRead($user->id);
    }
}
