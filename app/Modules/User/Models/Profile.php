<?php

declare(strict_types=1);

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Public-facing extended profile for a User (1:1).
 */
class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'avatar_path',
        'cover_path',
        'location',
        'website',
        'work',
        'education',
        'relationship_status',
        'visibility',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Absolute URL to the avatar, or null when unset. */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->avatar_path
            ? Storage::disk(config('media.disk'))->url($this->avatar_path)
            : null);
    }

    /** Absolute URL to the cover photo, or null when unset. */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->cover_path
            ? Storage::disk(config('media.disk'))->url($this->cover_path)
            : null);
    }

    protected $appends = ['avatar_url', 'cover_url'];
}
