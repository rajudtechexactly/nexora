<?php

declare(strict_types=1);

namespace App\Modules\Call\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A 1:1 audio/video call. The row tracks lifecycle/history; the actual media
 * flows peer-to-peer over WebRTC, with this server only relaying signaling.
 */
class Call extends Model
{
    public const TYPE_AUDIO = 'audio';
    public const TYPE_VIDEO = 'video';

    public const STATUS_RINGING  = 'ringing';
    public const STATUS_ONGOING  = 'ongoing';
    public const STATUS_ENDED    = 'ended';
    public const STATUS_MISSED   = 'missed';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'caller_id',
        'callee_id',
        'type',
        'status',
        'answered_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
            'ended_at'    => 'datetime',
        ];
    }

    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    public function callee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'callee_id');
    }

    public function involves(int $userId): bool
    {
        return $this->caller_id === $userId || $this->callee_id === $userId;
    }

    /** The id of the other party relative to $userId. */
    public function otherParty(int $userId): int
    {
        return $this->caller_id === $userId ? $this->callee_id : $this->caller_id;
    }
}
