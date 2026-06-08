<?php

declare(strict_types=1);

namespace App\Modules\Chat\Http\Resources;

use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Chat\Models\Conversation
 */
class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewerId = optional($request->user())->id;

        return [
            'id'              => $this->id,
            'type'            => $this->type,
            'title'           => $this->title,
            'participants'    => UserResource::collection($this->whenLoaded('participants')),
            // The counterpart in a direct chat, for list display.
            'other'           => $this->when(
                $this->relationLoaded('participants') && $this->type === 'direct',
                fn () => ($other = $this->participants->firstWhere('id', '!=', $viewerId))
                    ? new UserResource($other)
                    : null,
            ),
            'last_message'    => $this->whenLoaded(
                'latestMessage',
                fn () => $this->latestMessage ? new MessageResource($this->latestMessage) : null,
            ),
            'unread_count'    => (int) ($this->unread_count ?? 0),
            'last_message_at' => $this->last_message_at?->toIso8601String(),
        ];
    }
}
