<?php

declare(strict_types=1);

namespace App\Modules\Notification\Providers;

use App\Modules\Chat\Events\MessageSent;
use App\Modules\Friendship\Events\FriendRequestAccepted;
use App\Modules\Friendship\Events\FriendRequestSent;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Post\Events\PostCommented;
use App\Modules\Post\Events\PostReacted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Translates domain events from the other modules into in-app notifications.
 * Keeping the wiring here means the originating modules stay unaware of the
 * Notification module.
 */
class NotificationEventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(FriendRequestSent::class, function (FriendRequestSent $e): void {
            $this->notifier()->push($e->friendship->addressee_id, $e->friendship->requester_id, 'friend_request', $e->friendship);
        });

        Event::listen(FriendRequestAccepted::class, function (FriendRequestAccepted $e): void {
            $this->notifier()->push($e->friendship->requester_id, $e->friendship->addressee_id, 'friend_accepted', $e->friendship);
        });

        Event::listen(PostCommented::class, function (PostCommented $e): void {
            $comment = $e->comment->loadMissing('post');
            if ($comment->post) {
                $this->notifier()->push(
                    $comment->post->user_id,
                    $comment->user_id,
                    'post_comment',
                    $comment->post,
                    ['preview' => Str::limit((string) $comment->content, 80)],
                );
            }
        });

        Event::listen(PostReacted::class, function (PostReacted $e): void {
            $reaction = $e->reaction->loadMissing('reactable');
            $ownerId = $reaction->reactable?->user_id;
            if ($ownerId) {
                $this->notifier()->push(
                    (int) $ownerId,
                    $reaction->user_id,
                    'reaction',
                    $reaction->reactable,
                    ['reaction' => $reaction->type],
                );
            }
        });

        Event::listen(MessageSent::class, function (MessageSent $e): void {
            $message = $e->message->loadMissing('conversation.participants');
            foreach ($message->conversation?->participants ?? [] as $participant) {
                if ($participant->id !== $message->user_id) {
                    $this->notifier()->push(
                        $participant->id,
                        $message->user_id,
                        'message',
                        $message->conversation,
                        ['preview' => Str::limit((string) ($message->body ?? 'Sent an attachment'), 80)],
                    );
                }
            }
        });
    }

    private function notifier(): NotificationService
    {
        return $this->app->make(NotificationService::class);
    }
}
