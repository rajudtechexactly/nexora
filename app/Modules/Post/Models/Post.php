<?php

declare(strict_types=1);

namespace App\Modules\Post\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A timeline post: text and/or attached media, authored by a user and shown
 * in friends' feeds according to its visibility.
 */
class Post extends Model
{
    public const VISIBILITY_PUBLIC  = 'public';
    public const VISIBILITY_FRIENDS = 'friends';
    public const VISIBILITY_PRIVATE = 'private';

    public const VISIBILITIES = [
        self::VISIBILITY_PUBLIC,
        self::VISIBILITY_FRIENDS,
        self::VISIBILITY_PRIVATE,
    ];

    protected $fillable = [
        'user_id',
        'content',
        'visibility',
    ];

    protected $attributes = [
        'visibility'      => self::VISIBILITY_FRIENDS,
        'comments_count'  => 0,
        'reactions_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'comments_count'  => 'integer',
            'reactions_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('position');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    /** Constrained in queries to the viewing user, to surface their own reaction. */
    public function viewerReaction(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }
}
