<?php

declare(strict_types=1);

namespace App\Modules\Notification\Http\Resources;

use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Notification\Models\Notification
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'type'       => $this->type,
            'actor'      => new UserResource($this->whenLoaded('actor')),
            'data'       => $this->data,
            'entity'     => [
                'type' => $this->notifiable_type ? class_basename($this->notifiable_type) : null,
                'id'   => $this->notifiable_id,
            ],
            'read'       => $this->read_at !== null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
