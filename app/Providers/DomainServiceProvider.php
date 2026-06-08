<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Auth\Repositories\Contracts\OtpRepositoryInterface;
use App\Modules\Auth\Repositories\Eloquent\OtpRepository;
use App\Modules\Friendship\Repositories\Contracts\FriendshipRepositoryInterface;
use App\Modules\Friendship\Repositories\Eloquent\FriendshipRepository;
use App\Modules\Post\Repositories\Contracts\CommentRepositoryInterface;
use App\Modules\Post\Repositories\Contracts\PostRepositoryInterface;
use App\Modules\Post\Repositories\Contracts\ReactionRepositoryInterface;
use App\Modules\Post\Repositories\Eloquent\CommentRepository;
use App\Modules\Post\Repositories\Eloquent\PostRepository;
use App\Modules\Post\Repositories\Eloquent\ReactionRepository;
use App\Modules\Reel\Repositories\Contracts\ReelRepositoryInterface;
use App\Modules\Reel\Repositories\Eloquent\ReelRepository;
use App\Modules\Chat\Repositories\Contracts\ConversationRepositoryInterface;
use App\Modules\Chat\Repositories\Contracts\MessageRepositoryInterface;
use App\Modules\Chat\Repositories\Eloquent\ConversationRepository;
use App\Modules\Chat\Repositories\Eloquent\MessageRepository;
use App\Modules\Call\Repositories\Contracts\CallRepositoryInterface;
use App\Modules\Call\Repositories\Eloquent\CallRepository;
use App\Modules\Notification\Repositories\Contracts\NotificationRepositoryInterface;
use App\Modules\Notification\Repositories\Eloquent\NotificationRepository;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use App\Modules\User\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Wires every repository contract to its Eloquent implementation. Services
 * type-hint the interfaces, so persistence stays an implementation detail.
 */
class DomainServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    private array $repositories = [
        UserRepositoryInterface::class         => UserRepository::class,
        OtpRepositoryInterface::class          => OtpRepository::class,
        FriendshipRepositoryInterface::class   => FriendshipRepository::class,
        PostRepositoryInterface::class         => PostRepository::class,
        CommentRepositoryInterface::class      => CommentRepository::class,
        ReactionRepositoryInterface::class     => ReactionRepository::class,
        ReelRepositoryInterface::class         => ReelRepository::class,
        ConversationRepositoryInterface::class => ConversationRepository::class,
        MessageRepositoryInterface::class      => MessageRepository::class,
        CallRepositoryInterface::class         => CallRepository::class,
        NotificationRepositoryInterface::class => NotificationRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->repositories as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }
}
