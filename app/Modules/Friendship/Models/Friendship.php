<?php

declare(strict_types=1);

namespace App\Modules\Friendship\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A directed relationship row between two users. Direction (requester →
 * addressee) matters for pending requests; once accepted it represents a
 * mutual friendship.
 */
class Friendship extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_BLOCKED  = 'blocked';
    public const STATUS_DECLINED = 'declined';

    protected $fillable = [
        'requester_id',
        'addressee_id',
        'status',
        'blocked_by',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressee_id');
    }

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /** The id of the user on the other side of this row from $userId. */
    public function counterpartId(int $userId): int
    {
        return $this->requester_id === $userId ? $this->addressee_id : $this->requester_id;
    }
}
