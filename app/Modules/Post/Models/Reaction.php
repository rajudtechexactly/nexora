<?php

declare(strict_types=1);

namespace App\Modules\Post\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A user's reaction to a post or comment (one per user per target).
 */
class Reaction extends Model
{
    public const TYPES = ['like', 'love', 'haha', 'wow', 'sad', 'angry', 'care'];

    protected $fillable = [
        'user_id',
        'reactable_id',
        'reactable_type',
        'type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }
}
