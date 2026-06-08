<?php

declare(strict_types=1);

namespace App\Modules\Chat\Events;

use App\Modules\Chat\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast to the conversation's private channel so participants receive new
 * messages in real time over Reverb. Also consumed by the Notification module.
 */
class MessageSent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Message $message)
    {
    }

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.'.$this->message->conversation_id)];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $m = $this->message->loadMissing('sender.profile');

        return [
            'message' => [
                'id'              => $m->id,
                'conversation_id' => $m->conversation_id,
                'type'            => $m->type,
                'body'            => $m->body,
                'attachment_url'  => $m->attachment_url,
                'sender'          => [
                    'id'         => $m->sender?->id,
                    'name'       => $m->sender?->name,
                    'username'   => $m->sender?->username,
                    'avatar_url' => $m->sender?->profile?->avatar_url,
                ],
                'created_at'      => $m->created_at?->toIso8601String(),
            ],
        ];
    }
}
