<?php

declare(strict_types=1);

namespace App\Modules\Notification\Events;

use App\Modules\Notification\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Pushes a new notification to the recipient's private "user.{id}" channel. */
class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Notification $notification)
    {
    }

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->notification->recipient_id)];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $n = $this->notification->loadMissing('actor.profile');

        return [
            'notification' => [
                'id'         => $n->id,
                'type'       => $n->type,
                'actor'      => $n->actor ? [
                    'id'         => $n->actor->id,
                    'name'       => $n->actor->name,
                    'username'   => $n->actor->username,
                    'avatar_url' => $n->actor->profile?->avatar_url,
                ] : null,
                'data'       => $n->data,
                'read'       => false,
                'created_at' => $n->created_at?->toIso8601String(),
            ],
        ];
    }
}
