<?php

declare(strict_types=1);

namespace App\Modules\Post\Events;

use App\Modules\Post\Models\Comment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fired when a user comments on a post. Consumed by the Notification module. */
class PostCommented
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Comment $comment)
    {
    }
}
