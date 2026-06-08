<?php

declare(strict_types=1);

namespace App\Modules\Chat\Http\Resources;

use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Chat\Models\Message
 */
class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'conversation_id' => $this->conversation_id,
            'type'            => $this->type,
            'body'            => $this->body,
            'attachment_url'  => $this->attachment_url,
            'sender'          => new UserResource($this->whenLoaded('sender')),
            'is_mine'         => $this->user_id === optional($request->user())->id,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
