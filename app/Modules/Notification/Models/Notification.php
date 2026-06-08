<?php

declare(strict_types=1);

namespace App\Modules\Notification\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * An in-app notification for a recipient, optionally caused by an actor and
 * pointing at a related entity (post, comment, conversation).
 */
class Notification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'recipient_id',
        'actor_id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data'    => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
