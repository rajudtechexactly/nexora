<?php

declare(strict_types=1);

namespace App\Modules\Chat\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A single message in a conversation. May carry text and/or one attachment.
 */
class Message extends Model
{
    public const TYPE_TEXT  = 'text';
    public const TYPE_IMAGE = 'image';
    public const TYPE_FILE  = 'file';

    protected $fillable = [
        'conversation_id',
        'user_id',
        'type',
        'body',
        'attachment_path',
        'attachment_mime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment_path
            ? Storage::disk(config('media.disk', 'public'))->url($this->attachment_path)
            : null;
    }
}
