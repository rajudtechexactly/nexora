<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public representation of a user. Safe to embed anywhere (posts, comments,
 * chat, friend lists). Sensitive fields are never exposed.
 *
 * @mixin \App\Modules\User\Models\User
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'username'   => $this->username,
            'avatar_url' => $this->whenLoaded('profile', fn () => $this->profile?->avatar_url),
            'is_active'  => (bool) $this->is_active,
            'last_active_at' => $this->last_active_at?->toIso8601String(),
        ];
    }
}
