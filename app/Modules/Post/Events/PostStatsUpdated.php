<?php

declare(strict_types=1);

namespace App\Modules\Post\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcasts a post's updated reaction/comment counts so every client with
 * that post in their feed updates live. Public channel — only non-sensitive
 * counts are sent (clients only act on posts they already hold).
 */
class PostStatsUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public int $postId,
        public int $reactionsCount,
        public int $commentsCount,
    ) {
    }

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [new Channel('posts')];
    }

    public function broadcastAs(): string
    {
        return 'post.stats';
    }

    /** @return array<string, int> */
    public function broadcastWith(): array
    {
        return [
            'post_id'         => $this->postId,
            'reactions_count' => $this->reactionsCount,
            'comments_count'  => $this->commentsCount,
        ];
    }
}
