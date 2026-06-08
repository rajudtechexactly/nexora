<?php

declare(strict_types=1);

namespace App\Modules\Post\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A single image or video attached to a post. Stored on the configured media
 * disk; URLs are resolved on read.
 */
class PostMedia extends Model
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    protected $table = 'post_media';

    protected $fillable = [
        'post_id',
        'type',
        'path',
        'thumb_path',
        'width',
        'height',
        'size',
        'mime',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'width'    => 'integer',
            'height'   => 'integer',
            'size'     => 'integer',
            'position' => 'integer',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk(config('media.disk', 'public'))->url($this->path) : null;
    }

    public function getThumbUrlAttribute(): ?string
    {
        return $this->thumb_path ? Storage::disk(config('media.disk', 'public'))->url($this->thumb_path) : null;
    }
}
