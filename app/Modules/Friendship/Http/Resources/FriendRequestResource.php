<?php

declare(strict_types=1);

namespace App\Modules\Friendship\Http\Resources;

use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Represents a friend request/relationship row, surfacing the relevant
 * counterpart user (requester for incoming, addressee for outgoing).
 *
 * @mixin \App\Modules\Friendship\Models\Friendship
 */
class FriendRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewerId = optional($request->user())->id;

        // Show whichever party isn't the viewer.
        $other = $this->requester_id === $viewerId
            ? $this->whenLoaded('addressee')
            : $this->whenLoaded('requester');

        return [
            'id'           => $this->id,
            'status'       => $this->status,
            'direction'    => $this->requester_id === $viewerId ? 'outgoing' : 'incoming',
            'user'         => $other ? new UserResource($other) : null,
            'created_at'   => $this->created_at?->toIso8601String(),
            'responded_at' => $this->responded_at?->toIso8601String(),
        ];
    }
}
