<?php

declare(strict_types=1);

namespace App\Modules\Post\Http\Resources;

use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Post\Models\Post
 */
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewerId = optional($request->user())->id;

        return [
            'id'              => $this->id,
            'content'         => $this->content,
            'visibility'      => $this->visibility,
            'author'          => new UserResource($this->whenLoaded('user')),
            'media'           => $this->whenLoaded('media', fn () => $this->media->map(fn ($m) => [
                'id'        => $m->id,
                'type'      => $m->type,
                'url'       => $m->url,
                'thumb_url' => $m->thumb_url,
                'width'     => $m->width,
                'height'    => $m->height,
            ])->values()),
            'reactions_count' => (int) $this->reactions_count,
            'comments_count'  => (int) $this->comments_count,
            'my_reaction'     => $this->whenLoaded('viewerReaction', fn () => optional($this->viewerReaction->first())->type),
            'can_edit'        => $this->user_id === $viewerId,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
