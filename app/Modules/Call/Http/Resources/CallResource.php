<?php

declare(strict_types=1);

namespace App\Modules\Call\Http\Resources;

use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Call\Models\Call
 */
class CallResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewerId = optional($request->user())->id;

        return [
            'id'          => $this->id,
            'type'        => $this->type,
            'status'      => $this->status,
            'direction'   => $this->caller_id === $viewerId ? 'outgoing' : 'incoming',
            'caller'      => new UserResource($this->whenLoaded('caller')),
            'callee'      => new UserResource($this->whenLoaded('callee')),
            'answered_at' => $this->answered_at?->toIso8601String(),
            'ended_at'    => $this->ended_at?->toIso8601String(),
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
