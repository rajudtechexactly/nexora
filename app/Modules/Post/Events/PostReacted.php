<?php

declare(strict_types=1);

namespace App\Modules\Post\Events;

use App\Modules\Post\Models\Reaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fired when a user reacts to a post or comment. Consumed by Notifications. */
class PostReacted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Reaction $reaction)
    {
    }
}
