<?php

declare(strict_types=1);

namespace App\Modules\Friendship\Events;

use App\Modules\Friendship\Models\Friendship;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Raised when a user sends a new friend request (addressee should be notified). */
class FriendRequestSent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Friendship $friendship)
    {
    }
}
