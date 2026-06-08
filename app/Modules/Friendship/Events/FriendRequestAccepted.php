<?php

declare(strict_types=1);

namespace App\Modules\Friendship\Events;

use App\Modules\Friendship\Models\Friendship;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Raised when a friend request is accepted (original requester should be notified). */
class FriendRequestAccepted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Friendship $friendship)
    {
    }
}
